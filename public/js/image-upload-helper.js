/**
 * Image Upload Helper
 * Helper untuk resize dan crop gambar sebelum upload
 * Dapat digunakan di berbagai halaman
 * 
 * Dependencies:
 * - SweetAlert2 (Swal)
 * - Cropper.js (untuk fungsi crop)
 * - jQuery (untuk modal Bootstrap)
 */

(function (window) {
    'use strict';

    /**
     * ImageUploadHelper - Namespace untuk semua fungsi helper
     */
    const ImageUploadHelper = {
        /**
         * Fungsi universal untuk resize dan compress gambar sebelum upload
         * @param {File} file - File gambar yang akan di-resize
         * @param {Object} options - Opsi konfigurasi
         * @param {number} options.maxWidth - Lebar maksimal (default: 2000)
         * @param {number} options.maxHeight - Tinggi maksimal (default: 2000)
         * @param {number} options.quality - Kualitas JPEG (0-1, default: 0.85)
         * @param {number} options.maxFileSize - Ukuran file maksimal dalam bytes (default: 500KB)
         * @returns {Promise<File>} - Promise yang resolve dengan File yang sudah di-resize
         */
        resizeImageFile: function (file, options = {}) {
            const {
                maxWidth = 2000,
                maxHeight = 2000,
                quality = 0.85,
                maxFileSize = 500 * 1024
            } = options;

            return new Promise((resolve, reject) => {
                // Jika bukan gambar, langsung return file asli
                if (!file.type.startsWith('image/')) {
                    resolve(file);
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        // Hitung dimensi baru dengan mempertahankan aspect ratio
                        let width = img.width;
                        let height = img.height;

                        // Jika gambar lebih besar dari maxWidth atau maxHeight, resize
                        if (width > maxWidth || height > maxHeight) {
                            const ratio = Math.min(maxWidth / width, maxHeight / height);
                            width = width * ratio;
                            height = height * ratio;
                        }

                        // Buat canvas untuk resize
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');

                        // Enable image smoothing untuk kualitas lebih baik
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';

                        // Draw image ke canvas
                        ctx.drawImage(img, 0, 0, width, height);

                        // Fungsi untuk mengoptimalkan kualitas berdasarkan ukuran file
                        function optimizeQuality(currentQuality) {
                            canvas.toBlob(function (blob) {
                                if (!blob) {
                                    reject(new Error('Gagal mengkonversi gambar'));
                                    return;
                                }

                                // Jika sudah cukup kecil atau quality sudah minimum, gunakan blob ini
                                if (blob.size <= maxFileSize || currentQuality <= 0.5) {
                                    const resizedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(resizedFile);
                                } else {
                                    // Jika masih terlalu besar, kurangi quality dan coba lagi
                                    optimizeQuality(currentQuality - 0.1);
                                }
                            }, 'image/jpeg', currentQuality);
                        }

                        // Mulai dengan quality yang ditentukan
                        optimizeQuality(quality);
                    };
                    img.onerror = function () {
                        // Jika error, return file original
                        resolve(file);
                    };
                    img.src = e.target.result;
                };
                reader.onerror = function () {
                    // Jika error, return file original
                    resolve(file);
                };
                reader.readAsDataURL(file);
            });
        },

        /**
         * Fungsi untuk resize dan compress gambar sebelum crop
         * @param {File} file - File gambar yang akan di-resize
         * @param {number} maxWidth - Lebar maksimal (default: 2000)
         * @param {number} maxHeight - Tinggi maksimal (default: 2000)
         * @param {number} quality - Kualitas JPEG (0-1, default: 0.85)
         * @param {Function} callback - Callback function dengan parameter (resizedFile)
         */
        resizeImageBeforeCrop: function (file, maxWidth = 2000, maxHeight = 2000, quality = 0.85, callback) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    // Hitung dimensi baru dengan mempertahankan aspect ratio
                    let width = img.width;
                    let height = img.height;

                    // Jika gambar lebih besar dari maxWidth atau maxHeight, resize
                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = width * ratio;
                        height = height * ratio;
                    }

                    // Buat canvas untuk resize
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');

                    // Enable image smoothing untuk kualitas lebih baik
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';

                    // Draw image ke canvas
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert ke blob dengan quality yang ditentukan
                    canvas.toBlob(function (blob) {
                        if (blob) {
                            // Convert blob ke File untuk kompatibilitas
                            const resizedFile = new File([blob], file.name, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            callback(resizedFile);
                        } else {
                            callback(file); // Fallback ke file original jika gagal
                        }
                    }, 'image/jpeg', quality);
                };
                img.onerror = function () {
                    callback(file); // Fallback ke file original jika error
                };
                img.src = e.target.result;
            };
            reader.onerror = function () {
                callback(file); // Fallback ke file original jika error
            };
            reader.readAsDataURL(file);
        },

        /**
         * Pastikan Cropper.js sudah dimuat
         * @param {Function} callback - Callback function yang dipanggil saat Cropper.js sudah dimuat
         */
        ensureCropperLoaded: function (callback) {
            if (typeof Cropper !== 'undefined' && typeof Cropper === 'function') {
                callback();
                return;
            }

            let attempts = 0;
            const maxAttempts = 50;
            const checkInterval = setInterval(function () {
                attempts++;
                if (typeof Cropper !== 'undefined' && typeof Cropper === 'function') {
                    clearInterval(checkInterval);
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat library Cropper.js. Pastikan koneksi internet stabil atau refresh halaman.'
                        });
                    } else {
                        console.error('Gagal memuat library Cropper.js');
                    }
                }
            }, 100);
        },

        /**
         * Fungsi untuk validasi dan resize file gambar
         * @param {string} inputId - ID dari elemen input file
         * @param {Object} options - Opsi konfigurasi
         * @param {number} options.maxSizeBeforeResize - Ukuran maksimal sebelum resize (default: 50MB)
         * @param {number} options.maxSizeAfterResize - Ukuran maksimal setelah resize untuk PDF (default: 5MB)
         * @param {Array} options.allowedTypes - Tipe file yang diizinkan (default: ['image/jpeg', 'image/png', 'application/pdf'])
         * @param {Function} options.onSuccess - Callback saat berhasil (optional)
         * @param {Function} options.onError - Callback saat error (optional)
         * @returns {Promise<boolean>} - Promise yang resolve dengan true jika file valid, false jika tidak valid
         */
        validateAndResizeFile: async function (inputId, options = {}) {
            const {
                maxSizeBeforeResize = 50 * 1024 * 1024, // 50MB
                maxSizeAfterResize = 5 * 1024 * 1024, // 5MB
                allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'],
                onSuccess = null,
                onError = null
            } = options;

            const fileInput = document.getElementById(inputId);
            if (!fileInput) {
                if (onError) onError('Input file tidak ditemukan');
                return false;
            }

            const file = fileInput.files[0];
            if (!file) {
                return true;
            }

            const errorElement = document.getElementById(inputId + 'Error');
            let fileLabel;
            if (!inputId.includes('PhotoProfil')) {
                fileLabel = fileInput.closest('.custom-file')?.querySelector('.custom-file-label');
            }

            const fileType = file.type;

            // Validasi tipe file
            if (!allowedTypes.includes(fileType)) {
                fileInput.value = '';
                if (errorElement) {
                    errorElement.textContent = `Format file yang dipilih "${file.name}" (${fileType}) tidak valid. Format harus JPG, PNG, atau PDF`;
                    errorElement.classList.remove('d-none');
                    errorElement.style.display = 'block';
                }
                if (onError) onError('Format file tidak valid');
                return false;
            }

            // Validasi ukuran file sebelum resize
            if (file.size > maxSizeBeforeResize) {
                fileInput.value = '';
                if (errorElement) {
                    errorElement.textContent = `Ukuran file "${file.name}" (${(file.size / 1024 / 1024).toFixed(2)}MB) terlalu besar (maksimal ${(maxSizeBeforeResize / 1024 / 1024).toFixed(0)}MB). Silakan kompres file terlebih dahulu.`;
                    errorElement.classList.remove('d-none');
                    errorElement.style.display = 'block';
                }
                if (onError) onError('Ukuran file terlalu besar');
                return false;
            }

            // Jika file adalah gambar, lakukan resize otomatis
            if (fileType.startsWith('image/')) {
                // Tampilkan loading untuk file besar
                const isLargeFile = file.size > 2 * 1024 * 1024; // > 2MB
                let loadingSwal = null;
                if (isLargeFile && typeof Swal !== 'undefined') {
                    loadingSwal = Swal.fire({
                        title: 'Memproses gambar...',
                        text: 'Sedang mengoptimalkan ukuran gambar, harap tunggu...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                try {
                    // Resize gambar dengan maksimal 2000x2000px dan 500KB
                    const resizedFile = await this.resizeImageFile(file, {
                        maxWidth: 2000,
                        maxHeight: 2000,
                        quality: 0.85,
                        maxFileSize: 500 * 1024
                    });

                    // Tutup loading jika ada
                    if (loadingSwal && typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    // Set file yang sudah di-resize ke input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(resizedFile);
                    fileInput.files = dataTransfer.files;

                    // Update label
                    if (fileLabel) {
                        fileLabel.textContent = resizedFile.name;
                    }
                    if (errorElement) {
                        errorElement.classList.add('d-none');
                    }

                    // Tampilkan notifikasi sukses untuk file besar
                    if (isLargeFile && file.size !== resizedFile.size && typeof Swal !== 'undefined') {
                        const originalSize = (file.size / (1024 * 1024)).toFixed(2);
                        const newSize = (resizedFile.size / (1024 * 1024)).toFixed(2);
                        Swal.fire({
                            icon: 'success',
                            title: 'Gambar berhasil dioptimalkan!',
                            html: `Ukuran file: ${originalSize} MB → ${newSize} MB`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }

                    if (onSuccess) onSuccess(resizedFile);
                    return true;
                } catch (error) {
                    // Tutup loading jika ada
                    if (loadingSwal && typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                    console.error('Error resizing image:', error);

                    // Jika error, coba gunakan file original
                    if (file.size <= maxSizeAfterResize) {
                        if (fileLabel) {
                            fileLabel.textContent = file.name;
                        }
                        if (errorElement) {
                            errorElement.classList.add('d-none');
                        }
                        if (onSuccess) onSuccess(file);
                        return true;
                    } else {
                        fileInput.value = '';
                        if (errorElement) {
                            errorElement.textContent = `Gagal memproses gambar. Ukuran file terlalu besar (${(file.size / 1024 / 1024).toFixed(2)}MB).`;
                            errorElement.classList.remove('d-none');
                            errorElement.style.display = 'block';
                        }
                        if (onError) onError(error.message);
                        return false;
                    }
                }
            } else {
                // Untuk PDF, validasi ukuran normal
                if (file.size > maxSizeAfterResize) {
                    fileInput.value = '';
                    if (errorElement) {
                        errorElement.textContent = `Ukuran file "${file.name}" (${(file.size / 1024 / 1024).toFixed(2)}MB) melebihi batas maksimal ${(maxSizeAfterResize / 1024 / 1024).toFixed(0)}MB`;
                        errorElement.classList.remove('d-none');
                        errorElement.style.display = 'block';
                    }
                    if (onError) onError('Ukuran file PDF terlalu besar');
                    return false;
                }

                // File PDF valid
                if (fileLabel) {
                    fileLabel.textContent = file.name;
                }
                if (errorElement) {
                    errorElement.classList.add('d-none');
                }
                if (onSuccess) onSuccess(file);
                return true;
            }
        }
    };

    // Export ke global scope
    window.ImageUploadHelper = ImageUploadHelper;

    // Export untuk module system (jika menggunakan module bundler)
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = ImageUploadHelper;
    }

})(window);
