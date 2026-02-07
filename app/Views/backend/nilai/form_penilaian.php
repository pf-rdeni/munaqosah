<style>
    @font-face {
        font-family: 'LPMQ';
        src: url('<?= base_url('assets/fonts/lpmq.otf') ?>') format('opentype');
        font-weight: normal;
        font-style: normal;
    }
    .text-arab {
        font-family: 'LPMQ', serif;
        font-size: 1.8em;
        line-height: 2.2;
        text-align: right;
    }
    .quran-verse {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .verse-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ccc;
        border-radius: 50%;
        margin-left: 10px;
        font-size: 0.8em;
    }
    
    /* Mushaf Page Layout Styles */
    .mushaf-page {
        border: 3px solid #d4af37;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        background: linear-gradient(to bottom, #fefefe, #f9f7f0);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .mushaf-header {
        text-align: center;
        border-bottom: 2px solid #d4af37;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }
    
    .mushaf-page-number {
        display: inline-block;
        background: linear-gradient(135deg, #d4af37, #f4e4a6);
        color: #333;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: bold;
        font-size: 1.1em;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .mushaf-verses {
        font-family: 'LPMQ', serif;
        font-size: 1.8em;
        line-height: 2.8;
        text-align: justify;
        direction: rtl;
        padding: 15px;
    }
    
    .mushaf-verses .ayah-marker {
        display: inline-block;
        background: #d4af37;
        color: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        font-size: 0.55em;
        margin: 0 8px;
        vertical-align: middle;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .highlight-surah-start {
        background-color: #fff9c4; /* Subtle yellow highlight */
        border-radius: 8px;
        padding: 5px 0;
        display: inline;
        box-shadow: 0 0 10px rgba(255, 235, 59, 0.5);
    }

    /* Dark Mode Support */
    .dark-mode .mushaf-page {
        background: linear-gradient(to bottom, #2c2c2c, #1a1a1a);
        border-color: #b38f2d; /* Slightly muted gold */
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        color: #e0e0e0;
    }

    .dark-mode .mushaf-header {
        border-bottom-color: #b38f2d;
    }

    .dark-mode .mushaf-page-number {
        background: linear-gradient(135deg, #b38f2d, #8a6d21);
        color: #fff;
    }

    .dark-mode .quran-verse {
        border-bottom-color: #444;
    }

    .dark-mode .verse-number {
        border-color: #666;
        color: #bbb;
    }

    .dark-mode .highlight-surah-start {
        background-color: #4a4a00; /* Darker highlight for dark mode */
        color: #fff;
        box-shadow: 0 0 10px rgba(255, 255, 0, 0.2);
    }

    [id^="quran-container-"].dark-mode,
    .dark-mode [id^="quran-container-"],
    .dark-mode .bg-light {
        background-color: #212529 !important;
        border-color: #444 !important;
    }

    .dark-mode .table-hover tbody tr:hover {
        background-color: rgba(255,255,255,.15) !important;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <div class="user-block">
                    <img class="img-circle" 
                         src="<?= (!empty($peserta['foto'])) ? base_url($peserta['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode($peserta['nama_siswa']) . '&background=random&color=fff&bold=true&length=2' ?>" 
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($peserta['nama_siswa']) ?>&background=random&color=fff&bold=true&length=2';"
                         alt="User Image">
                    <span class="username"><a href="#"><?= $peserta['nama_siswa'] ?></a></span>
                    <span class="description">No Peserta: <strong style="font-size: 1.2em; color: black;"><?= $peserta['no_peserta'] ?></strong> | NISN: <?= $peserta['nisn'] ?></span>
                </div>
            </div>
            
            <?php if(isset($isGraded) && $isGraded): ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-check-circle mr-2"></i> <strong>Sudah Dinilai</strong><br>
                    Anda sudah melakukan penilaian untuk peserta ini. Data tidak dapat diedit kembali.
                </div>
            <?php endif; ?>

            <?php if(isset($lockedByOther) && $lockedByOther): ?>
                <div class="alert alert-danger text-center">
                    <i class="fas fa-lock mr-2"></i> <strong>Akses Dikunci</strong><br>
                    Peserta ini sudah dinilai oleh Juri lain: <strong><?= esc($otherJuriName) ?></strong>.<br>
                    Anda tidak dapat mengubah nilai ini.
                </div>
            <?php endif; ?>

            <form id="form-input-nilai" data-mode="<?= $juri['kondisional_set'] ?>">
                <input type="hidden" name="no_peserta" value="<?= $peserta['no_peserta'] ?>">
                
                <?php if(isset($lockedByOther) && $lockedByOther): ?>
                    <!-- Form Hidden for Privacy -->
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-lock fa-5x mb-3 text-secondary"></i>
                        <h5>Formulir tidak tersedia.</h5>
                        <p>Peserta ini sedang/sudah dinilai oleh Juri lain.</p>
                    </div>
                <?php else: ?>
                    <div class="card-body">
                        <!-- Tabs Header -->
                        <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                            <?php foreach($items as $index => $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                   id="tab-<?= $item['key'] ?>" 
                                   data-toggle="pill" 
                                   href="#content-<?= $item['key'] ?>" 
                                   role="tab" 
                                   aria-controls="content-<?= $item['key'] ?>" 
                                   aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                    <?= $item['objek'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content pt-3" id="custom-content-below-tabContent">
                        <?php foreach($items as $index => $item): ?>
                            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                                 id="content-<?= $item['key'] ?>" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-<?= $item['key'] ?>">
                                
                                <div class="callout callout-info d-flex justify-content-between align-items-center">
                                    <h5>Materi: <strong><?= $item['label'] ?></strong></h5>
                                    
                                    <?php if(isset($item['objek_id']) && $item['objek_id'] > 0): ?>
                                    <div>
                                        <div class="btn-group btn-group-sm mr-2" role="group">
                                            <button type="button" class="btn btn-outline-secondary btn-zoom-out" title="Perkecil Text">
                                                <i class="fas fa-search-minus"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary disabled zoom-label" style="min-width: 60px; pointer-events: none; opacity: 1;">100%</button>
                                            <button type="button" class="btn btn-outline-secondary btn-zoom-in" title="Perbesar Text">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                        </div>
                                        <select class="form-control form-control-sm d-inline-block mr-2 display-mode-select" style="width: auto;" data-container="#quran-container-<?= $item['key'] ?>">
                                            <option value="per-ayat">Per Ayat</option>
                                            <option value="per-halaman">Per Halaman</option>
                                        </select>
                                        <button type="button" class="btn btn-sm btn-outline-info btn-view-ayat" 
                                                data-surah="<?= $item['objek_id'] ?>" 
                                                data-target="#quran-container-<?= $item['key'] ?>">
                                            <i class="fas fa-eye mr-1"></i> Lihat Ayat
                                        </button>
                                        <div class="custom-control custom-checkbox d-inline-block ml-3">
                                            <input type="checkbox" class="custom-control-input chk-auto-open" id="chk-auto-<?= $item['key'] ?>">
                                            <label class="custom-control-label font-weight-normal text-muted" for="chk-auto-<?= $item['key'] ?>" style="cursor: pointer;">Auto Buka</label>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Quran Container (Hidden Default) -->
                                <div id="quran-container-<?= $item['key'] ?>" class="collapse mb-3 border p-3 bg-light rounded" style="max-height: 400px; overflow-y: auto;">
                                    <div class="text-center text-muted loading-ayat">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Memuat Ayat...
                                    </div>
                                    <div class="ayat-content"></div>
                                    <!-- Page Navigation (for Per Halaman mode) -->
                                    <div class="page-navigation mt-3 text-center" style="display: none;">
                                        <button type="button" class="btn btn-secondary btn-sm prev-page-btn" disabled>
                                            <i class="fas fa-chevron-left"></i> Halaman Sebelumnya
                                        </button>
                                        <span class="mx-3 page-info font-weight-bold"></span>
                                        <button type="button" class="btn btn-secondary btn-sm next-page-btn" disabled>
                                            Halaman Selanjutnya <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>

                                            <th style="width: 40%">Kriteria</th>
                                            <th style="width: 25%">
                                                <?= ($juri['kondisional_set'] == 'nilai_pengurangan') ? 'Input Kesalahan' : 'Nilai' ?>
                                            </th>
                                            <th>Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($kriteriaList)): ?>
                                            <tr><td colspan="3" class="text-center text-muted">Belum ada kriteria yang disetting untuk Juri ini.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($kriteriaList as $kIndex => $k): ?>
                                            <?php 
                                                 // Logic for Labels & Limits
                                                 $isPengurangan = ($juri['kondisional_set'] == 'nilai_pengurangan');
                                                 $maxVal = $k['nilai_maksimal'] ?? 100;
                                                 
                                                 $suffix = $isPengurangan ? 'x Salah' : '/ ' . $maxVal;
                                                 // Max limit applies to BOTH modes now
                                                 $maxAttr = 'max="' . $maxVal . '"';
                                                 
                                                 $hintText = $isPengurangan 
                                                    ? 'Isi jumlah kesalahan (Max ' . $maxVal . ').' 
                                                    : 'Isi nilai (0-' . $maxVal . ').';

                                                 // Existing Value Logic
                                                 $currentVal = 0;
                                                 $disabledAttr = ((isset($isGraded) && $isGraded) || (isset($lockedByOther) && $lockedByOther)) ? 'disabled' : '';
                                                 
                                                 $lookupKey = ($item['key'] === 'general') ? 'General' : ($item['objek_id'] ?? 'General');
                                                 
                                                 if (isset($existingScores[$lookupKey][$k['id']])) {
                                                     $currentVal = $existingScores[$lookupKey][$k['id']];
                                                 }
                                            ?>
                                            <tr>

                                                <td>
                                                    <strong><?= $k['nama_kriteria'] ?></strong>
                                                </td>
                                                <td style="min-width: 200px;">
                                                    <div class="input-group flex-nowrap">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="btn btn-danger btn-min" <?= $disabledAttr ?>><i class="fas fa-minus"></i></button>
                                                        </div>
                                                        <input type="number" 
                                                               class="form-control input-nilai-field text-center font-weight-bold" 
                                                               style="font-size: 1.2em; min-width: 60px;"
                                                               name="nilai[<?= $item['key'] ?>][<?= $k['id'] ?>]" 
                                                               min="0" 
                                                               <?= $maxAttr ?>
                                                               data-max="<?= $maxVal ?>"
                                                               value="<?= $currentVal ?>"
                                                               required
                                                               <?= $disabledAttr ?>>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-success btn-plus" <?= $disabledAttr ?>><i class="fas fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-muted text-sm">
                                                    <?= $hintText ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <div class="form-group mt-3">
                                    <label>Catatan Khusus (Opsional)</label>
                                    <textarea name="catatan[<?= $item['key'] ?>]" class="form-control" rows="2" placeholder="Catatan untuk materi ini..." <?= $disabledAttr ?>></textarea>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <?php if(isset($lockedByOther) && $lockedByOther): ?>
                             <!-- No Cancel needed if locked strictly -->
                             <button type="button" class="btn btn-secondary" onclick="location.reload()">Kembali</button>
                        <?php else: ?>
                             <button type="button" class="btn btn-secondary" onclick="confirmCancel()"><i class="fas fa-arrow-left mr-1"></i> Kembali</button>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if(isset($lockedByOther) && $lockedByOther): ?>
                            <button type="button" class="btn btn-secondary" onclick="location.reload()">Tutup</button>
                        <?php elseif(!isset($isGraded) || !$isGraded): ?>
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-2"></i> Simpan Nilai</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-warning" id="btn-unlock"><i class="fas fa-lock mr-1"></i> Edit Nilai (Otorisasi)</button>
                            <!-- Secondary close button on right is redundant if we have 'Kembali' on left, but keeps UI balanced -->
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Otorisasi -->
<div class="modal fade" id="modal-auth" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Otorisasi Kepala/Admin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-auth">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required placeholder="User Kepala/Admin">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Verifikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    // Load saved display mode preference from localStorage
    let savedDisplayMode = localStorage.getItem('quranDisplayMode') || 'per-ayat';
    
    // Apply saved preference to all dropdowns on page load
    $(document).ready(function() {
        $('.display-mode-select').val(savedDisplayMode);
    });
    
    // Save display mode when changed
    $('.display-mode-select').on('change', function() {
        let selectedMode = $(this).val();
        localStorage.setItem('quranDisplayMode', selectedMode);
        
        // Update all other dropdowns to match
        $('.display-mode-select').val(selectedMode);
        
        // Clear the current display to force re-render with new mode
        let containerId = $(this).data('container');
        if (containerId) {
            let container = $(containerId);
            container.data('current-mode', null); // Reset mode to force re-render
            container.find('.ayat-content').empty();
        }
    });
    
    // Logic View Ayat with Display Mode Support
    // Auto Open Logic
    const AUTO_OPEN_KEY = 'autoOpenAyat';
    let isAutoOpen = localStorage.getItem(AUTO_OPEN_KEY) === 'true';

    // Initialize checkboxes
    $('.chk-auto-open').prop('checked', isAutoOpen);

    // Toggle listener
    $('.chk-auto-open').change(function() {
        isAutoOpen = $(this).is(':checked');
        localStorage.setItem(AUTO_OPEN_KEY, isAutoOpen);
        $('.chk-auto-open').prop('checked', isAutoOpen); // sync all checkboxes
    });

    // Helper to auto-open if enabled
    function triggerAutoOpen(targetContainer) {
        if (isAutoOpen) {
            let container = $(targetContainer);
            if (container.hasClass('collapse') && !container.hasClass('show')) {
                container.closest('.tab-pane').find('.btn-view-ayat').trigger('click');
            }
        }
    }

    // Trigger on page load for active tab
    setTimeout(() => {
        triggerAutoOpen('.tab-pane.active [id^="quran-container-"]');
    }, 500);

    // Trigger on tab switch
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        let target = $(e.target).attr("href"); // newly activated tab
        triggerAutoOpen(target + ' [id^="quran-container-"]');
    });

    $('.btn-view-ayat').click(function() {
        let surahId = $(this).data('surah');
        let targetId = $(this).data('target');
        let container = $(targetId);
        let contentDiv = container.find('.ayat-content');
        let loadingDiv = container.find('.loading-ayat');
        
        // Get display mode from localStorage (global setting)
        let displayMode = localStorage.getItem('quranDisplayMode') || 'per-ayat';

        // Toggle visibilitas
        container.collapse('toggle');
        
        // Jika kosong atau mode berubah, ambil data
        let currentMode = container.data('current-mode');
        if (contentDiv.is(':empty') || currentMode !== displayMode) {
            loadingDiv.show();
            contentDiv.hide();
            
            // Store current mode
            container.data('current-mode', displayMode);
            
            $.ajax({
                url: '<?= base_url("assets/quran/json/") ?>' + surahId + '.json',
                dataType: 'json',
                success: function(res) {
                    loadingDiv.hide();
                    if(res && res[surahId]) {
                        if (displayMode === 'per-halaman') {
                            renderPerHalaman(res, surahId, contentDiv);
                        } else {
                            renderPerAyat(res, surahId, contentDiv);
                        }
                    } else {
                        contentDiv.html('<div class="text-danger text-center">Gagal memuat ayat. Struktur data tidak dikenali.</div>').show();
                    }
                },
                error: function() {
                    loadingDiv.hide();
                    contentDiv.html('<div class="text-danger text-center">Gagal mengambil data ayat (File JSON tidak ditemukan).</div>').show();
                }
            });
        }
    });
    
    // Helper to convert number to Arabic
    function quranNumberToArabic(number) {
        let arabic = "";
        let numberString = number.toString();
        for (let i = 0; i < numberString.length; i++) {
            arabic += String.fromCharCode(numberString.charCodeAt(i) + 1584);
        }
        return arabic;
    }

    // Render Per Ayat (Original Mode - One verse per line)
    function renderPerAyat(res, surahId, contentDiv) {
        // Hide page navigation in Per Ayat mode
        let container = contentDiv.closest('[id^="quran-container-"]');
        container.find('.page-navigation').hide();
        
        let html = '';
        let verses = res[surahId].text;
        
        if(verses) {
            $.each(verses, function(ayatNo, ayatText) {
                html += `<div class="quran-verse">
                    <div class="d-flex justify-content-between">
                        <div class="verse-number">${quranNumberToArabic(ayatNo)}</div>
                        <div class="text-arab w-100" style="font-size: ${currentFontSize}em;">${ayatText}</div>
                    </div>
                </div>`;
            });
            contentDiv.html(html).show();
            applyFontSize();
        }
    }
    
    // Render Per Halaman (Mushaf Layout Mode - Using Real Page Mapping)
    let mushafPagesData = null; // Cache for page mapping data
    
    function renderPerHalaman(res, surahId, contentDiv) {
        // Load mushaf page mapping if not already loaded
        if (!mushafPagesData) {
            $.ajax({
                url: '<?= base_url("assets/quran/mushaf_pages_complete.json") ?>?v=<?= time() ?>',
                async: false,
                dataType: 'json',
                success: function(data) {
                    mushafPagesData = data;
                }
            });
        }
        
        // Find which page contains this surah
        let pageNumber = findPageBySurah(parseInt(surahId));
        
        if (!pageNumber || !mushafPagesData[pageNumber]) {
            contentDiv.html('<div class="text-danger text-center">Halaman untuk surah ini belum tersedia dalam mapping.</div>').show();
            return;
        }
        
        let pageData = mushafPagesData[pageNumber];
        
        // Build mushaf layout
        let html = '<div class="mushaf-page">';
        
        // Header with page number and Juz
        html += '<div class="mushaf-header">';
        html += `<div class="mushaf-page-number">صَفْحَة ${pageNumber} | Halaman ${pageNumber}</div>`;
        html += `<div class="mt-2"><strong>Juz ${pageData.juz}</strong></div>`;
        html += '</div>';
        
        // Verses in mushaf format (flowing text with ayah markers)
        html += '<div class="mushaf-verses" style="font-size: ' + currentFontSize + 'em;">';
        
        // Process each verse range on this page
        let isFirstSurahOnPage = true;
        for (let verseRange of pageData.verses) {
            let surahNum = verseRange.surah;
            let fromAyah = verseRange.from;
            let toAyah = verseRange.to;
            
            // Load surah data
            let surahData = null;
            $.ajax({
                url: '<?= base_url("assets/quran/json/") ?>' + surahNum + '.json',
                async: false,
                dataType: 'json',
                success: function(data) {
                    surahData = data;
                }
            });
            
            if (!surahData || !surahData[surahNum]) continue;
            
            // Add Bismillah if this is the start of a new surah (ayah 1) and not Surah 9
            if (fromAyah === 1 && surahNum !== 9 && surahNum !== 1) {
                html += '<div class="text-center mb-3" style="font-size: 1.1em;">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</div>';
            }
            
            // Add surah name if starting new surah mid-page
            if (fromAyah === 1 && !isFirstSurahOnPage) {
                let surahName = surahData[surahNum].name_latin || surahData[surahNum].name || 'Surah ' + surahNum;
                html += `<div class="text-center my-2" style="font-size: 0.9em; color: #666;">( ${surahName} )</div>`;
            }
            
            // Add verses from this range
            let verses = surahData[surahNum].text;
            for (let ayahNum = fromAyah; ayahNum <= toAyah; ayahNum++) {
                if (verses[ayahNum]) {
                    // Highlight ONLY the first verse of the target surah on this page
                    let isFirstVerseOfTarget = (surahNum === parseInt(surahId) && ayahNum === fromAyah);
                    
                    if (isFirstVerseOfTarget) {
                        html += '<span id="target-surah-start-' + surahId + '" class="highlight-surah-start">';
                    }
                    
                    html += verses[ayahNum] + ' <span class="ayah-marker">' + quranNumberToArabic(ayahNum) + '</span> ';
                    
                    if (isFirstVerseOfTarget) {
                        html += '</span>';
                    }
                }
            }
            
            isFirstSurahOnPage = false;
        }
        
        html += '</div>'; // Close mushaf-verses
        html += '</div>'; // Close mushaf-page
        
        contentDiv.html(html).show();
        
        // Show page navigation and update buttons
        let container = contentDiv.closest('#quran-container-' + contentDiv.closest('[id^="quran-container-"]').attr('id').split('-').pop());
        let navDiv = container.find('.page-navigation');
        let prevBtn = navDiv.find('.prev-page-btn');
        let nextBtn = navDiv.find('.next-page-btn');
        let pageInfo = navDiv.find('.page-info');
        
        navDiv.show();
        pageInfo.text('Halaman ' + pageNumber);
        
        // Enable/disable buttons based on available pages
        prevBtn.prop('disabled', pageNumber <= 582);
        nextBtn.prop('disabled', pageNumber >= 604);
        
        // Store current page number
        container.data('current-page', pageNumber);
        container.data('current-surah', surahId);
        
        // Remove old click handlers and add new ones
        prevBtn.off('click').on('click', function() {
            if (pageNumber > 582) {
                renderPageByNumber(pageNumber - 1, contentDiv);
                // Scroll to bottom when going to previous page
                setTimeout(function() {
                    container[0].scrollTop = container[0].scrollHeight;
                }, 100);
            }
        });
        
        nextBtn.off('click').on('click', function() {
            if (pageNumber < 604) {
                renderPageByNumber(pageNumber + 1, contentDiv);
                // Scroll to top when going to next page
                setTimeout(function() {
                    container[0].scrollTop = 0;
                }, 100);
            }
        });

        // Auto-scroll to target surah
        setTimeout(function() {
            let targetEl = contentDiv.find('#target-surah-start-' + surahId);
            if (targetEl.length) {
                // Calculate precise offset relative to container
                let offset = targetEl.offset().top - container.offset().top;
                // Scroll container so target is at the very top (with small 5px padding)
                container.animate({ scrollTop: container.scrollTop() + offset - 5 }, 500);
            }
        }, 300);

        applyFontSize();
    }
    
    // New function to render a specific page number
    function renderPageByNumber(pageNum, contentDiv) {
        if (!mushafPagesData || !mushafPagesData[pageNum]) {
            contentDiv.html('<div class="text-danger text-center">Halaman ' + pageNum + ' tidak tersedia.</div>').show();
            return;
        }
        
        let pageData = mushafPagesData[pageNum];
        
        // Build mushaf layout (similar to renderPerHalaman, but for a specific page)
        let html = '<div class="mushaf-page">';
        
        // Header with page number and Juz
        html += '<div class="mushaf-header">';
        html += `<div class="mushaf-page-number">صَفْحَة ${pageNum} | Halaman ${pageNum}</div>`;
        html += `<div class="mt-2"><strong>Juz ${pageData.juz}</strong></div>`;
        html += '</div>';
        
        // Verses in mushaf format (flowing text with ayah markers)
        html += '<div class="mushaf-verses" style="font-size: ' + currentFontSize + 'em;">';
        
        // Process each verse range on this page
        let isFirstSurahOnPage = true;
        for (let verseRange of pageData.verses) {
            let surahNum = verseRange.surah;
            let fromAyah = verseRange.from;
            let toAyah = verseRange.to;
            
            // Load surah data
            let surahData = null;
            $.ajax({
                url: '<?= base_url("assets/quran/json/") ?>' + surahNum + '.json',
                async: false,
                dataType: 'json',
                success: function(data) {
                    surahData = data;
                }
            });
            
            if (!surahData || !surahData[surahNum]) continue;
            
            // Add Bismillah if this is the start of a new surah (ayah 1) and not Surah 9
            if (fromAyah === 1 && surahNum !== 9 && surahNum !== 1) {
                html += '<div class="text-center mb-3" style="font-size: 1.1em;">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</div>';
            }
            
            // Add surah name if starting new surah mid-page
            if (fromAyah === 1 && !isFirstSurahOnPage) {
                let surahName = surahData[surahNum].name_latin || surahData[surahNum].name || 'Surah ' + surahNum;
                html += `<div class="text-center my-2" style="font-size: 0.9em; color: #666;">( ${surahName} )</div>`;
            }
            
            // Add verses from this range
            let verses = surahData[surahNum].text;
            for (let ayahNum = fromAyah; ayahNum <= toAyah; ayahNum++) {
                if (verses[ayahNum]) {
                    html += verses[ayahNum] + ' <span class="ayah-marker">' + quranNumberToArabic(ayahNum) + '</span> ';
                }
            }
            
            isFirstSurahOnPage = false;
        }
        
        html += '</div>'; // Close mushaf-verses
        html += '</div>'; // Close mushaf-page
        
        contentDiv.html(html).show();
        
        // Update page navigation buttons
        let container = contentDiv.closest('#quran-container-' + contentDiv.closest('[id^="quran-container-"]').attr('id').split('-').pop());
        let navDiv = container.find('.page-navigation');
        let prevBtn = navDiv.find('.prev-page-btn');
        let nextBtn = navDiv.find('.next-page-btn');
        let pageInfo = navDiv.find('.page-info');
        
        pageInfo.text('Halaman ' + pageNum);
        prevBtn.prop('disabled', pageNum <= 582);
        nextBtn.prop('disabled', pageNum >= 604);
        
        container.data('current-page', pageNum);
        
        // Re-attach click handlers for new pageNum
        prevBtn.off('click').on('click', function() {
            if (pageNum > 582) {
                renderPageByNumber(pageNum - 1, contentDiv);
                // Scroll to bottom when going to previous page
                setTimeout(function() {
                    container[0].scrollTop = container[0].scrollHeight;
                }, 100);
            }
        });
        
        nextBtn.off('click').on('click', function() {
            if (pageNum < 604) {
                renderPageByNumber(pageNum + 1, contentDiv);
                // Scroll to top when going to next page
                setTimeout(function() {
                    container[0].scrollTop = 0;
                }, 100);
            }
        });
        
        applyFontSize();
    }
    
    // Find which page contains a given surah (first occurrence)
    function findPageBySurah(surahNumber) {
        if (!mushafPagesData) return null;
        
        for (let pageNum in mushafPagesData) {
            let pageData = mushafPagesData[pageNum];
            for (let verseRange of pageData.verses) {
                if (verseRange.surah === surahNumber) {
                    return parseInt(pageNum);
                }
            }
        }
        return null; // Surah not found in mapping
    }

    // Zoom Logic
    // Default size
    let currentFontSize = parseFloat(localStorage.getItem('quranFontSize')) || 1.8;
    
    // Apply saved size immediately to styled elements (if any exist static) or dynamic ones
    function applyFontSize() {
        $('.text-arab').css('font-size', currentFontSize + 'em');
        $('.mushaf-verses').css('font-size', currentFontSize + 'em');
        
        // Update percentage label (base 1.0 = 100%)
        let percentage = Math.round(currentFontSize * 100);
        $('.zoom-label').text(percentage + '%');
        
        // Save to local storage
        localStorage.setItem('quranFontSize', currentFontSize);
    }

    applyFontSize(); // Initialize labels on load

    $('.btn-zoom-in').click(function() {
        currentFontSize += 0.2;
        applyFontSize();
    });

    $('.btn-zoom-out').click(function() {
        if (currentFontSize > 0.8) { // Minimum limit
            currentFontSize -= 0.2;
            applyFontSize();
        }
    });

    // Unlock Logic
    $('#btn-unlock').click(function() {
        $('#modal-auth').modal('show');
    });

    $('#form-auth').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url('backend/munaqosah/input-nilai/authorize-edit') ?>',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    $('#modal-auth').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Buka Kunci Form (Input, Textarea, dan Button)
                    $('#form-input-nilai input, #form-input-nilai textarea, #form-input-nilai button').prop('disabled', false);
                    $('.alert-warning').fadeOut();
                    
                    // Replace buttons
                    $('.card-footer').html(`
                        <div class="d-flex justify-content-between w-100">
                             <div>
                                <button type="button" class="btn btn-secondary" onclick="confirmCancel()"><i class="fas fa-arrow-left mr-1"></i> Kembali</button>
                             </div>
                             <div>
                                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-2"></i> Simpan Nilai</button>
                             </div>
                        </div>
                    `);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });

    $('#form-input-nilai').submit(function(e) {
        e.preventDefault();
        
        let mode = $(this).data('mode'); // 'nilai_pengurangan' or 'nilai_default' (or other)

        // 1. Validasi: Cek input kosong dan cek NOL untuk mode default
        let isValid = true;
        let emptyCount = 0;
        let zeroCount = 0;
        
        $('.input-nilai-field').each(function() {
            let val = $(this).val();
            
            // Cek kosong
            if (val === '') {
                isValid = false;
                emptyCount++;
                $(this).addClass('is-invalid');
            } 
            // Cek Nol untuk mode Non-Pengurangan
            else if (mode !== 'nilai_pengurangan' && parseFloat(val) == 0) {
                isValid = false;
                zeroCount++;
                $(this).addClass('is-invalid');
            }
            else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            let msg = 'Harap periksa input data.';
            if (emptyCount > 0) msg += ' Ada ' + emptyCount + ' kolom kosong.';
            if (zeroCount > 0) msg += ' Nilai 0 tidak diperbolehkan untuk materi ini.';
            
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: msg,
            });
            return; 
        }

        // 2. Buat HTML Ringkasan
        let summaryHtml = '<div class="text-left" style="font-size: 0.9em;">';
        summaryHtml += '<table class="table table-sm table-bordered mt-2"><thead><tr class="bg-light"><th>Materi</th><th>Detail Input</th></tr></thead><tbody>';
        
        // Loop melalui setiap tab-pane untuk dikelompokkan berdasarkan Item
        $('.tab-pane').each(function() {
            let itemLabel = $(this).find('h5 strong').text();
            let inputs = $(this).find('.input-nilai-field');
            
            if (inputs.length > 0) {
                summaryHtml += '<tr>';
                summaryHtml += '<td style="vertical-align: middle;"><strong>' + itemLabel + '</strong></td>';
                summaryHtml += '<td>';
                
                inputs.each(function() {
                    let kriteriaName = $(this).closest('tr').find('td:first strong').text();
                    let val = parseFloat($(this).val()) || 0;
                    let maxVal = parseFloat($(this).data('max')) || 100;
                    let displayVal = val;
                    let suffix = ''; 
                    
                    if (mode === 'nilai_pengurangan') {
                        let finalScore = maxVal - val;
                        // Show: Kesalahan (15) => Nilai (85)
                        displayVal = `Kesalahan: ${val} <i class="fas fa-arrow-right mx-1"></i> Nilai: <strong>${finalScore}</strong>`;
                    } else {
                        // Nilai Biasa
                        displayVal = `<strong>${val}</strong>`;
                    }
                    
                    summaryHtml += '<div>- ' + kriteriaName + ': ' + displayVal + '</div>';
                });
                
                summaryHtml += '</td></tr>';
            }
        });
        summaryHtml += '</tbody></table></div>';
        summaryHtml += '<p class="mt-2 text-muted">Pastikan data di atas sudah sesuai.</p>';

        // 3. Konfirmasi
        Swal.fire({
            title: 'Konfirmasi Simpan',
            html: summaryHtml,
            icon: 'question',
            width: '600px',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('backend/munaqosah/input-nilai/save') ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if(res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Tutup Form & Reset Pencarian
                                $('#penilaian-container').empty();
                                $('.card-primary').CardWidget('expand');
                                $('#no_peserta').val('').focus();
                                
                                // Segarkan Riwayat
                                if(window.loadHistory) window.loadHistory();
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                    }
                });
            }
        });
    });
    
    // Pilih konten otomatis saat fokus
    $('.input-nilai-field').focus(function() {
        $(this).select();
    });

    // Custom Input Logic (+/- Buttons)
    $('.btn-plus').click(function() {
        let input = $(this).closest('.input-group').find('input.input-nilai-field');
        let currentVal = parseInt(input.val()) || 0;
        let maxVal = parseInt(input.attr('max')) || 100;
        
        if (currentVal < maxVal) {
            input.val(currentVal + 1).trigger('change');
        }
    });

    $('.btn-min').click(function() {
        let input = $(this).closest('.input-group').find('input.input-nilai-field');
        let currentVal = parseInt(input.val()) || 0;
        let minVal = parseInt(input.attr('min')) || 0;
        
        if (currentVal > minVal) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    // Dirty State Tracking
    let formIsDirty = false;
    $('.input-nilai-field, textarea').on('change keyup', function() {
        formIsDirty = true;
    });

    // Custom Cancel Confirmation
    window.confirmCancel = function() {
        if (formIsDirty) {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Anda telah mengubah nilai. Data yang belum disimpan akan hilang. Yakin ingin kembali?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Kembali',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } else {
            location.reload();
        }
    };
</script>
