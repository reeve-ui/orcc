import Alpine from 'alpinejs';

window.toggleTheme = function () {
    const root = document.documentElement;
    const isDark = root.classList.toggle('dark');

    try {
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    } catch (error) {
        // localStorage bisa diblokir pada mode privat; tema tetap berfungsi sesi ini.
    }
};

Alpine.data('addressPicker', (el) => ({
    province: '',
    city: '',
    district: '',
    village: '',
    postal_code: '',
    provinces: [],
    cities: [],
    districts: [],
    villages: [],
    postalCodes: [],
    loading: {
        provinces: false,
        cities: false,
        districts: false,
        villages: false,
        postalCodes: false,
    },
    error: '',
    urls: {
        provinces: el.dataset.provincesUrl,
        cities: el.dataset.citiesUrl,
        districts: el.dataset.districtsUrl,
        villages: el.dataset.villagesUrl,
        postalCodes: el.dataset.postalCodesUrl,
    },

    async init() {
        let initial = {};

        try {
            initial = JSON.parse(el.dataset.initial || '{}');
        } catch (error) {
            initial = {};
        }

        this.province = initial.province || '';
        this.city = initial.city || '';
        this.district = initial.district || '';
        this.village = initial.village || '';
        this.postal_code = initial.postal_code || '';

        await this.loadProvinces();
        this.ensureValue('province', this.provinces);

        if (!this.province) {
            return;
        }

        await this.loadCities();
        this.ensureValue('city', this.cities, true);

        if (!this.city) {
            return;
        }

        await this.loadDistricts();
        this.ensureValue('district', this.districts);

        if (!this.district) {
            return;
        }

        await this.loadVillages();
        this.ensureValue('village', this.villages);

        if (!this.village) {
            return;
        }

        await this.loadPostalCodes();
        this.ensureValue('postal_code', this.postalCodes);
    },

    async loadProvinces() {
        this.loading.provinces = true;
        this.error = '';

        try {
            this.provinces = await this.fetchJson(this.urls.provinces);
        } catch (error) {
            this.error = 'Gagal memuat data provinsi. Muat ulang halaman.';
        } finally {
            this.loading.provinces = false;
        }
    },

    async loadCities() {
        this.cities = [];

        if (!this.province) {
            return;
        }

        this.loading.cities = true;
        this.error = '';

        try {
            this.cities = await this.fetchJson(this.urls.cities, { province: this.province });
        } catch (error) {
            this.error = 'Gagal memuat data kabupaten/kota.';
        } finally {
            this.loading.cities = false;
        }
    },

    async loadDistricts() {
        this.districts = [];

        if (!this.city) {
            return;
        }

        this.loading.districts = true;
        this.error = '';

        try {
            this.districts = await this.fetchJson(this.urls.districts, {
                province: this.province,
                city: this.city,
            });
        } catch (error) {
            this.error = 'Gagal memuat data kecamatan.';
        } finally {
            this.loading.districts = false;
        }
    },

    async loadVillages() {
        this.villages = [];

        if (!this.district) {
            return;
        }

        this.loading.villages = true;
        this.error = '';

        try {
            this.villages = await this.fetchJson(this.urls.villages, {
                province: this.province,
                city: this.city,
                district: this.district,
            });
        } catch (error) {
            this.error = 'Gagal memuat data kelurahan/desa.';
        } finally {
            this.loading.villages = false;
        }
    },

    async loadPostalCodes() {
        this.postalCodes = [];

        if (!this.village) {
            return;
        }

        this.loading.postalCodes = true;
        this.error = '';

        try {
            this.postalCodes = await this.fetchJson(this.urls.postalCodes, {
                province: this.province,
                city: this.city,
                district: this.district,
                village: this.village,
            });

            if (this.postalCodes.length === 1) {
                this.postal_code = this.postalCodes[0];
            }
        } catch (error) {
            this.error = 'Gagal memuat data kode pos.';
        } finally {
            this.loading.postalCodes = false;
        }
    },

    async changeProvince() {
        this.city = '';
        this.district = '';
        this.village = '';
        this.postal_code = '';
        this.cities = [];
        this.districts = [];
        this.villages = [];
        this.postalCodes = [];

        await this.loadCities();
    },

    async changeCity() {
        this.district = '';
        this.village = '';
        this.postal_code = '';
        this.districts = [];
        this.villages = [];
        this.postalCodes = [];

        await this.loadDistricts();
    },

    async changeDistrict() {
        this.village = '';
        this.postal_code = '';
        this.villages = [];
        this.postalCodes = [];

        await this.loadVillages();
    },

    async changeVillage() {
        this.postal_code = '';
        this.postalCodes = [];

        await this.loadPostalCodes();
    },

    ensureValue(field, options, normalizeLegacy = false) {
        const current = this[field];

        if (!current) {
            return;
        }

        const candidates = [current];

        if (normalizeLegacy) {
            candidates.push(
                current.replace(/^Kota\s+/i, ''),
                current.replace(/^Kabupaten\s+/i, ''),
                current.replace(/^Kab\.\s+/i, '')
            );
        }

        const match = candidates.find((candidate) => candidate && options.includes(candidate));
        this[field] = match || '';
    },

    async fetchJson(url, params = {}) {
        const query = new URLSearchParams(params).toString();
        const response = await fetch(url + (query ? `?${query}` : ''), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        return Array.isArray(data.items) ? data.items : [];
    },
}));

Alpine.data('memberForm', (config = {}) => ({
    familyId: config.familyId || '',
    familyStatus: config.familyStatus || 'Anggota Keluarga',
    childOrder: config.childOrder || '',
    gender: config.gender || 'Pria',
    maritalStatus: config.maritalStatus || 'Belum Menikah',
    ktjId: config.existingKtj || '',
    existingKtj: config.existingKtj || '',
    original: config.original || {},

    init() {
        this.syncKtj();

        this.$watch('familyId', () => this.syncKtj());
        this.$watch('familyStatus', () => this.syncKtj());
        this.$watch('childOrder', () => this.syncKtj());
        this.$watch('gender', () => this.syncKtj());
    },

    get isAnggota() {
        return this.familyStatus === 'Anggota Keluarga';
    },

    get isMarried() {
        return this.maritalStatus === 'Telah Menikah';
    },

    hasRelevantChanges() {
        return String(this.original.familyId || '') !== String(this.familyId || '')
            || String(this.original.familyStatus || '') !== String(this.familyStatus || '')
            || String(this.original.childOrder || '') !== String(this.childOrder || '')
            || String(this.original.gender || '') !== String(this.gender || '');
    },

    syncKtj() {
        if (this.existingKtj && !this.hasRelevantChanges()) {
            this.ktjId = this.existingKtj;
            return;
        }

        this.ktjId = this.buildKtj();
    },

    buildKtj() {
        const familySelect = this.$el.querySelector('#family_id');
        const kkjId = (familySelect?.selectedOptions?.[0]?.dataset?.kkjId || '').toUpperCase();

        if (kkjId.length !== 13) {
            return '';
        }

        let memberDigit = '0';

        if (this.isAnggota) {
            const order = Number.parseInt(this.childOrder, 10);

            if (!Number.isInteger(order) || order < 1 || order > 9) {
                return '';
            }

            memberDigit = String(order);
        }

        const genderDigit = this.gender === 'Wanita' ? '1' : this.gender === 'Pria' ? '0' : '';

        if (genderDigit === '') {
            return '';
        }

        return kkjId + memberDigit + genderDigit;
    },
}));

window.Alpine = Alpine;

Alpine.start();
