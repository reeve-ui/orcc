<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Throwable;

class UpdateService
{
    private const SUCCESS = 0;

    private const FAILURE = 1;

    private const CHECK_CACHE_KEY = 'app-update-check';

    private const LOCK_KEY = 'app-update-run';

    private const LOCK_TTL = 3600;

    private const STATUS_FILE = 'app/private/update-status.json';

    private const LOG_FILE = 'logs/update.log';

    public function enabled(): bool
    {
        return (bool) config('update.enabled');
    }

    public function statusPath(): string
    {
        return storage_path(self::STATUS_FILE);
    }

    public function logPath(): string
    {
        return storage_path(self::LOG_FILE);
    }

    /**
     * Status proses update terakhir.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $path = $this->statusPath();

        if (! File::exists($path)) {
            return $this->idleStatus();
        }

        try {
            $data = json_decode((string) File::get($path), true);
        } catch (Throwable) {
            return $this->idleStatus();
        }

        if (! is_array($data)) {
            return $this->idleStatus();
        }

        $status = array_merge($this->idleStatus(), $data);

        if ($status['state'] === 'running' && ! empty($status['started_at'])) {
            try {
                $started = Carbon::parse($status['started_at']);

                if ($started->diffInSeconds(now()) > max(self::LOCK_TTL, (int) config('update.timeout') + 180)) {
                    $status['state'] = 'failed';
                    $status['step'] = 'timeout';
                    $status['message'] = 'Proses update tampak berhenti. Periksa log pembaruan.';
                    $status['finished_at'] = now()->toIso8601String();
                }
            } catch (Throwable) {
                $status['state'] = 'failed';
                $status['step'] = 'invalid';
                $status['message'] = 'Status pembaruan tidak valid. Periksa log pembaruan.';
                $status['finished_at'] = now()->toIso8601String();
            }
        }

        return $status;
    }

    /**
     * Hasil pengecekan terakhir yang tersimpan di cache.
     *
     * @return array<string, mixed>|null
     */
    public function lastCheck(): ?array
    {
        $data = Cache::get(self::CHECK_CACHE_KEY);

        return is_array($data) ? $data : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function check(bool $force = false): array
    {
        if (! $this->enabled()) {
            return array_merge($this->baseCheck(), [
                'state' => 'disabled',
                'error' => 'Pembaruan aplikasi dinonaktifkan di konfigurasi.',
            ]);
        }

        if ($force) {
            Cache::forget(self::CHECK_CACHE_KEY);
        }

        return Cache::remember(
            self::CHECK_CACHE_KEY,
            now()->addMinutes(5),
            fn (): array => $this->performCheck()
        );
    }

    /**
     * Mulai proses update di latar belakang.
     *
     * @return array{started: bool, message: string}
     */
    public function start(int $userId): array
    {
        $lock = Cache::lock(self::LOCK_KEY.':start', 180);

        if (! $lock->get()) {
            return ['started' => false, 'message' => 'Proses pembaruan lain sedang dimulai.'];
        }

        try {
            return $this->startUpdate($userId);
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array{started: bool, message: string}
     */
    private function startUpdate(int $userId): array
    {
        if (! $this->enabled()) {
            return ['started' => false, 'message' => 'Pembaruan aplikasi dinonaktifkan di konfigurasi.'];
        }

        if (! $this->isGitRepository()) {
            return ['started' => false, 'message' => 'Folder aplikasi bukan repositori Git. Perbarui secara manual.'];
        }

        if ($this->status()['state'] === 'running') {
            return ['started' => false, 'message' => 'Proses pembaruan lain sedang berjalan.'];
        }

        if (! function_exists('exec')) {
            return ['started' => false, 'message' => 'Fungsi exec dinonaktifkan. Jalankan php artisan orcc4church:update lewat SSH.'];
        }

        if (! function_exists('proc_open')) {
            return ['started' => false, 'message' => 'Fungsi proc_open dinonaktifkan. Aktifkan proc_open atau jalankan langkah pembaruan secara manual.'];
        }

        $check = $this->check(true);

        if (! empty($check['error'])) {
            return ['started' => false, 'message' => (string) $check['error']];
        }

        if (! empty($check['dirty'])) {
            return ['started' => false, 'message' => 'Ada perubahan file lokal. Commit atau simpan perubahan tersebut sebelum memperbarui.'];
        }

        if (empty($check['update_available'])) {
            return ['started' => false, 'message' => $this->unavailableMessage($check)];
        }

        $php = (string) config('update.php_binary');
        $phpProbe = $this->runCommand([$php, '-v'], 15);

        if (! $phpProbe['successful']) {
            return ['started' => false, 'message' => 'PHP CLI tidak dapat dijalankan. Set APP_UPDATE_PHP_BINARY ke path PHP CLI yang benar.'];
        }

        $this->writeStatus([
            'state' => 'running',
            'step' => 'starting',
            'message' => 'Memulai proses pembaruan...',
            'output' => null,
            'warnings' => [],
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'user_id' => $userId,
            'from_commit' => $check['current_commit'] ?? null,
            'to_commit' => $check['remote_commit'] ?? null,
            'version' => config('app.version'),
        ]);

        $command = [
            $php,
            base_path('artisan'),
            'orcc4church:update',
            '--user='.$userId,
        ];

        $shell = 'nohup '.implode(' ', array_map('escapeshellarg', $command))
            .' >> '.escapeshellarg($this->logPath()).' 2>&1 &';

        exec($shell, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->writeStatus([
                'state' => 'failed',
                'step' => 'starting',
                'message' => 'Gagal memulai proses pembaruan di latar belakang.',
                'finished_at' => now()->toIso8601String(),
            ]);

            return ['started' => false, 'message' => 'Gagal memulai proses pembaruan. Periksa log server.'];
        }

        return ['started' => true, 'message' => 'Proses pembaruan dimulai. Pantau status di halaman ini; proses tetap berjalan meskipun halaman ditutup.'];
    }

    /**
     * Jalankan seluruh langkah pembaruan. Dipanggil oleh perintah Artisan.
     */
    public function run(int $userId = 0): int
    {
        $lock = $this->acquireRunLock();

        if ($lock === null) {
            $this->writeStatus([
                'state' => 'failed',
                'step' => 'locked',
                'message' => 'Proses pembaruan lain sedang berjalan.',
                'finished_at' => now()->toIso8601String(),
            ]);

            return self::FAILURE;
        }

        try {
            Log::info('Pembaruan aplikasi dimulai.', ['user_id' => $userId]);

            $this->writeStatus([
                'state' => 'running',
                'step' => 'checking',
                'message' => 'Memeriksa pembaruan...',
                'output' => null,
                'warnings' => [],
                'started_at' => now()->toIso8601String(),
                'finished_at' => null,
                'user_id' => $userId,
                'from_commit' => null,
                'to_commit' => null,
                'version' => config('app.version'),
            ]);

            $check = $this->performCheck();

            if (! empty($check['error'])) {
                return $this->fail((string) $check['error']);
            }

            if (! empty($check['dirty'])) {
                return $this->fail('Ada perubahan file lokal. Update dibatalkan agar perubahan tidak hilang.');
            }

            if (empty($check['update_available'])) {
                if (($check['state'] ?? null) === 'up_to_date') {
                    return $this->succeed($this->unavailableMessage($check), $check);
                }

                return $this->fail($this->unavailableMessage($check));
            }

            $this->writeStatus([
                'from_commit' => $check['current_commit'] ?? null,
                'to_commit' => $check['remote_commit'] ?? null,
            ]);

            $branch = $this->branch();

            $this->step('pull', 'Mengambil pembaruan dari GitHub...');
            $pull = $this->runCommand($this->gitCommand(['pull', '--ff-only', 'origin', $branch]), 180);

            if (! $pull['successful']) {
                return $this->fail('Gagal mengambil pembaruan: '.$this->commandError($pull), $pull);
            }

            $this->step('composer', 'Memasang dependensi PHP...');
            $composer = $this->composerCommand();
            $composerResult = $this->runCommand(
                array_merge($composer, ['install', '--no-dev', '--no-interaction', '--prefer-dist', '--optimize-autoloader']),
                (int) config('update.timeout')
            );

            if (! $composerResult['successful']) {
                return $this->fail('Composer gagal: '.$this->commandError($composerResult), $composerResult);
            }

            $this->step('migrate', 'Menjalankan migrasi database...');
            $migrate = $this->runCommand([PHP_BINARY, base_path('artisan'), 'migrate', '--force'], 300);

            if (! $migrate['successful']) {
                return $this->fail('Migrasi database gagal: '.$this->commandError($migrate), $migrate);
            }

            $warnings = [];

            if (config('update.build_assets')) {
                $this->step('assets', 'Membangun aset frontend...');
                $npm = (string) config('update.npm_binary');
                $install = $this->runCommand([$npm, 'ci', '--no-audit', '--no-fund'], 600);

                if (! $install['successful']) {
                    $warnings[] = 'npm ci gagal: '.$this->commandError($install);
                } else {
                    $build = $this->runCommand([$npm, 'run', 'build'], 600);

                    if (! $build['successful']) {
                        $warnings[] = 'npm run build gagal: '.$this->commandError($build);
                    }
                }
            }

            $this->step('cache', 'Membersihkan cache aplikasi...');
            $cache = $this->runCommand([PHP_BINARY, base_path('artisan'), 'optimize:clear'], 180);

            if (! $cache['successful']) {
                $warnings[] = 'optimize:clear gagal: '.$this->commandError($cache);
            }

            $version = $this->readVersionFile() ?: config('app.version');

            return $this->succeed(
                'Aplikasi berhasil diperbarui'.($version ? ' ke versi '.$version : '').'.',
                $check,
                $warnings
            );
        } catch (Throwable $exception) {
            Log::error('Pembaruan aplikasi gagal.', ['exception' => $exception]);

            return $this->fail('Pembaruan gagal: '.$exception->getMessage());
        } finally {
            $this->releaseRunLock($lock);
        }
    }

    /**
     * @return resource|null
     */
    private function acquireRunLock()
    {
        File::ensureDirectoryExists(dirname($this->statusPath()));

        $handle = fopen($this->runLockPath(), 'c');

        if ($handle === false) {
            return null;
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return null;
        }

        return $handle;
    }

    /**
     * @param  resource  $handle
     */
    private function releaseRunLock($handle): void
    {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private function runLockPath(): string
    {
        return storage_path('app/private/update.lock');
    }

    /**
     * @return array<string, mixed>
     */
    private function performCheck(): array
    {
        $result = $this->baseCheck();
        $result['checked_at'] = now()->toIso8601String();

        if (! $this->isGitRepository()) {
            $result['state'] = 'not_git';
            $result['error'] = 'Folder aplikasi bukan repositori Git. Pembaruan otomatis tidak tersedia.';

            return $result;
        }

        $git = $this->gitBinary();
        $gitVersion = $this->runCommand([$git, '--version'], 15);

        if (! $gitVersion['successful']) {
            $result['state'] = 'error';
            $result['error'] = 'Git tidak tersedia: '.$this->commandError($gitVersion);

            return $result;
        }

        $result['git'] = true;

        $head = $this->runCommand($this->gitCommand(['rev-parse', 'HEAD']), 15);

        if (! $head['successful']) {
            $result['state'] = 'error';
            $result['error'] = 'Tidak dapat membaca commit terpasang: '.$this->commandError($head);

            return $result;
        }

        $localCommit = trim($head['output']);
        $result['current_commit'] = $localCommit;
        $result['current_short'] = substr($localCommit, 0, 7);

        $workingTree = $this->runCommand($this->gitCommand(['status', '--porcelain', '--untracked-files=no']), 30);

        if (! $workingTree['successful']) {
            $result['state'] = 'error';
            $result['error'] = 'Tidak dapat memeriksa perubahan lokal: '.$this->commandError($workingTree);

            return $result;
        }

        $dirtyFiles = array_values(array_filter(
            preg_split('/\R/', trim($workingTree['output'])) ?: [],
            fn (string $line): bool => trim($line) !== ''
        ));

        $result['dirty'] = $dirtyFiles !== [];
        $result['dirty_files'] = array_slice($dirtyFiles, 0, 10);

        $remote = $this->runCommand(
            $this->gitCommand(['ls-remote', 'origin', 'refs/heads/'.$this->branch()]),
            60
        );

        if (! $remote['successful']) {
            $result['state'] = 'error';
            $result['error'] = 'Tidak dapat menghubungi GitHub: '.$this->commandError($remote);

            return $result;
        }

        $remoteCommit = $this->parseRemoteCommit($remote['output']);

        if ($remoteCommit === null) {
            $result['state'] = 'error';
            $result['error'] = 'Branch '.$this->branch().' tidak ditemukan di origin.';

            return $result;
        }

        $result['remote_commit'] = $remoteCommit;
        $result['remote_short'] = substr($remoteCommit, 0, 7);
        $result['remote_url'] = 'https://github.com/'.$this->repository().'/commit/'.$remoteCommit;

        if ($localCommit === $remoteCommit) {
            $result['state'] = 'up_to_date';
            $result['update_available'] = false;

            return $result;
        }

        $remoteExists = $this->runCommand(
            $this->gitCommand(['cat-file', '-e', $remoteCommit.'^{commit}']),
            15
        );

        if (! $remoteExists['successful']) {
            $result['state'] = 'behind';
            $result['update_available'] = true;

            return $result;
        }

        $remoteIsAncestor = $this->runCommand(
            $this->gitCommand(['merge-base', '--is-ancestor', $remoteCommit, 'HEAD']),
            15
        );

        $headIsAncestor = $this->runCommand(
            $this->gitCommand(['merge-base', '--is-ancestor', 'HEAD', $remoteCommit]),
            15
        );

        if ($remoteIsAncestor['successful']) {
            $result['state'] = 'ahead';
        } elseif ($headIsAncestor['successful']) {
            $result['state'] = 'behind';
            $result['update_available'] = true;
        } else {
            $result['state'] = 'diverged';
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function baseCheck(): array
    {
        return [
            'enabled' => $this->enabled(),
            'git' => false,
            'dirty' => false,
            'dirty_files' => [],
            'state' => 'unknown',
            'update_available' => false,
            'current_commit' => null,
            'current_short' => null,
            'remote_commit' => null,
            'remote_short' => null,
            'remote_url' => null,
            'branch' => $this->branch(),
            'repository' => $this->repository(),
            'version' => config('app.version'),
            'checked_at' => null,
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function idleStatus(): array
    {
        return [
            'state' => 'idle',
            'step' => null,
            'message' => null,
            'output' => null,
            'warnings' => [],
            'started_at' => null,
            'finished_at' => null,
            'user_id' => null,
            'from_commit' => null,
            'to_commit' => null,
            'version' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function writeStatus(array $changes): void
    {
        $path = $this->statusPath();
        File::ensureDirectoryExists(dirname($path));

        $status = array_merge($this->status(), $changes);

        $temporary = $path.'.tmp';

        File::put($temporary, json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        File::move($temporary, $path);
    }

    private function step(string $step, string $message): void
    {
        $this->writeStatus([
            'state' => 'running',
            'step' => $step,
            'message' => $message,
            'output' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $command
     */
    private function fail(string $message, ?array $command = null): int
    {
        $this->writeStatus([
            'state' => 'failed',
            'step' => 'failed',
            'message' => $message,
            'output' => $command ? $this->formatCommandOutput($command) : null,
            'finished_at' => now()->toIso8601String(),
        ]);

        Log::error('Pembaruan aplikasi gagal.', [
            'message' => $message,
            'command' => $command,
        ]);

        return self::FAILURE;
    }

    /**
     * @param  array<string, mixed>  $check
     * @param  list<string>  $warnings
     */
    private function succeed(string $message, array $check, array $warnings = []): int
    {
        $version = $this->readVersionFile() ?: config('app.version');

        $this->writeStatus([
            'state' => 'success',
            'step' => 'done',
            'message' => $message,
            'output' => null,
            'warnings' => $warnings,
            'finished_at' => now()->toIso8601String(),
            'from_commit' => $check['current_commit'] ?? null,
            'to_commit' => $check['remote_commit'] ?? null,
            'version' => $version,
        ]);

        Log::info('Pembaruan aplikasi selesai.', [
            'message' => $message,
            'warnings' => $warnings,
        ]);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $check
     */
    private function unavailableMessage(array $check): string
    {
        return match ($check['state'] ?? null) {
            'up_to_date' => 'Aplikasi sudah menggunakan versi terbaru dari GitHub.',
            'ahead' => 'Instalasi memiliki commit lokal yang lebih baru dari GitHub. Update otomatis dinonaktifkan.',
            'diverged' => 'Riwayat commit lokal berbeda dari GitHub. Selesaikan konflik secara manual.',
            'not_git' => 'Folder aplikasi bukan repositori Git. Perbarui secara manual.',
            default => 'Tidak ada pembaruan yang dapat dipasang.',
        };
    }

    /**
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    private function gitCommand(array $arguments): array
    {
        return array_merge(
            [$this->gitBinary(), '-c', 'safe.directory='.base_path()],
            $arguments
        );
    }

    /**
     * @return array<int, string>
     */
    private function composerCommand(): array
    {
        $binary = (string) config('update.composer_binary');

        if (is_file(base_path('composer.phar')) && ($binary === '' || $binary === 'composer')) {
            return [PHP_BINARY, base_path('composer.phar')];
        }

        return [$binary !== '' ? $binary : 'composer'];
    }

    /**
     * @param  array<int, string>  $command
     * @return array{successful: bool, output: string, error: string, exit_code: int|null}
     */
    private function runCommand(array $command, int $timeout = 120): array
    {
        try {
            $result = Process::path(base_path())
                ->env(['GIT_TERMINAL_PROMPT' => '0'])
                ->timeout($timeout)
                ->run($command);

            return [
                'successful' => $result->successful(),
                'output' => trim($result->output()),
                'error' => trim($result->errorOutput()),
                'exit_code' => $result->exitCode(),
            ];
        } catch (Throwable $exception) {
            return [
                'successful' => false,
                'output' => '',
                'error' => $exception->getMessage(),
                'exit_code' => null,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function commandError(array $result): string
    {
        $message = trim((string) ($result['error'] ?? ''));

        if ($message === '') {
            $message = trim((string) ($result['output'] ?? ''));
        }

        if ($message === '') {
            $message = 'Kode keluar '.($result['exit_code'] ?? 'tidak diketahui');
        }

        return Str::limit($message, 1200);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function formatCommandOutput(array $result): string
    {
        $output = trim(($result['output'] ?? '')."\n".($result['error'] ?? ''));

        return Str::limit($output, 8000);
    }

    private function parseRemoteCommit(string $output): ?string
    {
        $line = trim($output);

        if ($line === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $line);

        return isset($parts[0]) && preg_match('/^[a-f0-9]{40}$/i', $parts[0]) === 1
            ? $parts[0]
            : null;
    }

    private function readVersionFile(): ?string
    {
        $path = base_path('VERSION');

        if (! is_file($path)) {
            return null;
        }

        $version = trim((string) file_get_contents($path));

        return $version !== '' ? $version : null;
    }

    private function isGitRepository(): bool
    {
        return File::exists(base_path('.git'));
    }

    private function branch(): string
    {
        return (string) (config('update.branch') ?: 'main');
    }

    private function repository(): string
    {
        return (string) (config('update.repository') ?: 'reeve-ui/orcc');
    }

    private function gitBinary(): string
    {
        return (string) (config('update.git_binary') ?: 'git');
    }
}
