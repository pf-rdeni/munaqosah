/**
 * Quran Viewer Module
 * Handles Quran verse display with per-verse and per-page viewing modes
 */

const QuranViewer = {
    currentMode: 'verse',
    selectedSurah: null,
    highlightFrom: null,
    highlightTo: null,

    /**
     * Initialize the Quran viewer
     */
    init() {
        this.loadSurahList();
        this.bindEvents();
    },

    /**
     * Bind UI events
     */
    bindEvents() {
        // Mode toggle
        $('input[name="view_mode"]').on('change', (e) => {
            this.currentMode = e.target.value;
            this.toggleMode();
        });

        // Surah selection
        $('#surah-select').on('change', (e) => {
            this.selectedSurah = parseInt(e.target.value);
            this.updateAyahLimits();
        });

        // Load verses button
        $('#btn-load-verses').on('click', () => {
            this.loadVerses();
        });

        // Load page button
        $('#btn-load-page').on('click', () => {
            this.loadPage();
        });

        // Enter key support
        $('#from-ayah, #to-ayah').on('keypress', (e) => {
            if (e.which === 13) {
                this.loadVerses();
            }
        });

        $('#page-number').on('keypress', (e) => {
            if (e.which === 13) {
                this.loadPage();
            }
        });
    },

    /**
     * Toggle between per-verse and per-page modes
     */
    toggleMode() {
        if (this.currentMode === 'verse') {
            $('#controls-per-verse').show();
            $('#controls-per-page').hide();
        } else {
            $('#controls-per-verse').hide();
            $('#controls-per-page').show();
        }

        // Clear display
        this.showPlaceholder();
    },

    /**
     * Load surah list from server
     */
    loadSurahList() {
        $.ajax({
            url: baseUrl + 'backend/munaqosah/input-nilai/get-quran-surah-list',
            type: 'POST',
            dataType: 'json',
            success: (response) => {
                if (response.success && response.data) {
                    this.renderSurahList(response.data);
                } else {
                    this.showError('Gagal memuat daftar surah');
                }
            },
            error: () => {
                this.showError('Terjadi kesalahan saat memuat daftar surah');
            }
        });
    },

    /**
     * Render surah list in dropdown
     */
    renderSurahList(surahList) {
        const $select = $('#surah-select');
        $select.empty().append('<option value="">Pilih Surah...</option>');

        surahList.forEach(surah => {
            $select.append(
                `<option value="${surah.number}" data-ayah-count="${surah.number_of_ayah}">
                    ${surah.number}. ${surah.name} (${surah.name_arabic}) - ${surah.number_of_ayah} ayat
                </option>`
            );
        });
    },

    /**
     * Update ayah input limits based on selected surah
     */
    updateAyahLimits() {
        const $selected = $('#surah-select option:selected');
        const ayahCount = parseInt($selected.data('ayah-count')) || 1;

        $('#from-ayah, #to-ayah').attr('max', ayahCount);
        $('#to-ayah').val(Math.min(parseInt($('#to-ayah').val()), ayahCount));
    },

    /**
     * Load verses by surah and ayah range
     */
    loadVerses() {
        const surahNumber = parseInt($('#surah-select').val());
        const fromAyah = parseInt($('#from-ayah').val());
        const toAyah = parseInt($('#to-ayah').val());

        if (!surahNumber) {
            this.showError('Pilih surah terlebih dahulu');
            return;
        }

        if (!fromAyah || !toAyah) {
            this.showError('Masukkan rentang ayat');
            return;
        }

        if (fromAyah > toAyah) {
            this.showError('Ayat awal harus lebih kecil dari ayat akhir');
            return;
        }

        // Store for highlighting
        this.highlightFrom = fromAyah;
        this.highlightTo = toAyah;

        this.showLoading();

        $.ajax({
            url: baseUrl + 'backend/munaqosah/input-nilai/get-quran-verses',
            type: 'POST',
            data: {
                surah_number: surahNumber,
                from_ayah: fromAyah,
                to_ayah: toAyah
            },
            dataType: 'json',
            success: (response) => {
                if (response.success && response.data) {
                    this.renderVerses(response.data);
                } else {
                    this.showError(response.message || 'Gagal memuat ayat');
                }
            },
            error: () => {
                this.showError('Terjadi kesalahan saat memuat ayat');
            }
        });
    },

    /**
     * Load verses by Mushaf page number
     */
    loadPage() {
        const pageNumber = parseInt($('#page-number').val());

        if (!pageNumber || pageNumber < 1 || pageNumber > 604) {
            this.showError('Masukkan nomor halaman yang valid (1-604)');
            return;
        }

        this.showLoading();

        $.ajax({
            url: baseUrl + 'backend/munaqosah/input-nilai/get-quran-verses-by-page',
            type: 'POST',
            data: {
                page_number: pageNumber
            },
            dataType: 'json',
            success: (response) => {
                if (response.success && response.data) {
                    this.renderPageVerses(response.data);
                } else {
                    this.showError(response.message || 'Gagal memuat halaman');
                }
            },
            error: () => {
                this.showError('Terjadi kesalahan saat memuat halaman');
            }
        });
    },

    /**
     * Render verses in display area
     */
    renderVerses(data) {
        const $display = $('#quran-display');
        $display.empty();

        // Header
        $display.append(`
            <div class="mb-3 text-center border-bottom pb-2">
                <h5 class="mb-1">${data.surah_name}</h5>
                <p class="text-muted mb-0" style="font-family: 'Amiri', 'Traditional Arabic', serif; font-size: 1.2rem;">
                    ${data.surah_name_arabic}
                </p>
            </div>
        `);

        // Verses
        data.verses.forEach(verse => {
            const isHighlighted = verse.ayah_number >= this.highlightFrom &&
                verse.ayah_number <= this.highlightTo;

            const highlightClass = isHighlighted ? 'bg-warning bg-opacity-25' : '';

            $display.append(`
                <div class="verse-item mb-3 p-2 rounded ${highlightClass}" data-ayah="${verse.ayah_number}">
                    <div class="arabic-text text-right mb-2" style="font-family: 'Amiri', 'Traditional Arabic', serif; font-size: 1.5rem; line-height: 2; direction: rtl;">
                        ${verse.text_arabic} 
                        <span class="ayah-number badge badge-secondary">${verse.ayah_number}</span>
                    </div>
                    <div class="translation-text text-muted" style="font-size: 0.9rem;">
                        <strong>${verse.ayah_number}.</strong> ${verse.translation_id}
                    </div>
                </div>
            `);
        });

        // Scroll to first highlighted verse
        if (this.highlightFrom) {
            setTimeout(() => {
                const $firstHighlight = $(`.verse-item[data-ayah="${this.highlightFrom}"]`);
                if ($firstHighlight.length) {
                    $display.animate({
                        scrollTop: $firstHighlight.position().top
                    }, 300);
                }
            }, 100);
        }
    },

    /**
     * Render page verses
     */
    renderPageVerses(data) {
        const $display = $('#quran-display');
        $display.empty();

        // Header
        $display.append(`
            <div class="mb-3 text-center border-bottom pb-2">
                <h5 class="mb-1">Halaman ${data.page_number}</h5>
                <p class="text-muted mb-0">Mushaf Utsmani</p>
            </div>
        `);

        // Verses
        data.verses.forEach(verse => {
            $display.append(`
                <div class="verse-item mb-3 p-2 rounded" data-ayah="${verse.ayah_number}">
                    <div class="arabic-text text-right mb-2" style="font-family: 'Amiri', 'Traditional Arabic', serif; font-size: 1.5rem; line-height: 2; direction: rtl;">
                        ${verse.text_arabic} 
                        <span class="ayah-number badge badge-secondary">${verse.ayah_number}</span>
                    </div>
                    <div class="translation-text text-muted" style="font-size: 0.9rem;">
                        <strong>${verse.ayah_number}.</strong> ${verse.translation_id}
                    </div>
                </div>
            `);
        });
    },

    /**
     * Show loading state
     */
    showLoading() {
        $('#quran-display').html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                <p>Memuat...</p>
            </div>
        `);
    },

    /**
     * Show placeholder
     */
    showPlaceholder() {
        $('#quran-display').html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-quran fa-3x mb-2"></i>
                <p>Pilih surah dan ayat untuk menampilkan Al-Quran</p>
            </div>
        `);
    },

    /**
     * Show error message
     */
    showError(message) {
        $('#quran-display').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${message}
            </div>
        `);
    }
};

// Initialize on document ready
$(document).ready(function () {
    QuranViewer.init();
});
