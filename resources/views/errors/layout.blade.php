<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --brand: #0f766e;
            --brand-soft: #ccfbf1;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #020617;
                --card: #0f172a;
                --text: #f8fafc;
                --muted: #94a3b8;
                --line: #1e293b;
                --brand: #2dd4bf;
                --brand-soft: rgba(45, 212, 191, 0.12);
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .card {
            width: 100%;
            max-width: 520px;
            padding: 40px 32px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: var(--card);
            box-shadow: 0 24px 60px -32px rgba(15, 23, 42, 0.35);
            text-align: center;
        }

        .mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            margin-bottom: 20px;
            border-radius: 16px;
            background: var(--brand);
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
        }

        .code {
            margin: 0;
            font-size: 64px;
            line-height: 1;
            letter-spacing: -0.04em;
            color: var(--brand);
        }

        h1 {
            margin: 12px 0 8px;
            font-size: 22px;
            letter-spacing: -0.02em;
        }

        p {
            margin: 0 auto;
            max-width: 400px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
        }

        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 18px;
            border-radius: 12px;
            border: 1px solid transparent;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        a.primary {
            background: var(--brand);
            color: #ffffff;
        }

        a.secondary {
            border-color: var(--line);
            color: var(--text);
        }

        .footer {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 11px;
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="mark" aria-hidden="true">O4C</span>
        <p class="code">@yield('code')</p>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>

        <div class="actions">
            <a class="primary" href="{{ url('/') }}">Kembali ke dashboard</a>
            @auth
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="secondary" style="min-height:42px;padding:0 18px;border-radius:12px;border:1px solid var(--line);background:transparent;color:var(--text);font:inherit;font-size:13px;font-weight:700;cursor:pointer;">Keluar</button>
                </form>
            @else
                <a class="secondary" href="{{ route('login') }}">Masuk</a>
            @endauth
        </div>

        <div class="footer">{{ config('app.name') }} v{{ config('app.version') }} · {{ config('church.name') }}</div>
    </main>
</body>
</html>
