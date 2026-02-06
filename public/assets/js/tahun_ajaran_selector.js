/**
 * Academic Year Selector
 * Handles switching between academic years via AJAX
 */

$(document).ready(function() {
    // Handle year selection click
    $(document).on('click', '.tahun-ajaran-option', function(e) {
        e.preventDefault();
        
        const year = $(this).data('year');
        const currentYear = $('#currentTahunAjaran').text().trim();
        
        // Don't reload if selecting the same year
        if (year === currentYear) {
            return;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Mengubah Tahun Ajaran',
            text: 'Mohon tunggu...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Send AJAX request
        $.ajax({
            url: baseUrl + '/backend/tahun-ajaran/switch',
            method: 'POST',
            data: { 
                tahun_ajaran: year 
            },
            success: function(response) {
                if (response.success) {
                    // Update display
                    $('#currentTahunAjaran').text(year);
                    
                    // Show success message
                    Swal.fire({
                        title: 'Berhasil',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to refresh all data
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Gagal mengubah tahun ajaran. Silakan coba lagi.',
                    icon: 'error'
                });
            }
        });
    });
});
