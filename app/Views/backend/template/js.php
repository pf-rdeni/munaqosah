<!-- jQuery -->
<script src="<?= base_url('template/backend/plugins/jquery/jquery.min.js') ?>"></script>

<!-- Bootstrap 4 -->
<script src="<?= base_url('template/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

<!-- AdminLTE App -->
<script src="<?= base_url('template/backend/dist/js/adminlte.min.js') ?>"></script>

<!-- DataTables -->
<script src="<?= base_url('template/backend/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>

<!-- Select2 -->
<script src="<?= base_url('template/backend/plugins/select2/js/select2.full.min.js') ?>"></script>

<!-- SweetAlert2 -->
<script src="<?= base_url('template/backend/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

<!-- Custom Scripts -->
<script>
$(document).ready(function() {
    // =================================================================
    // DARK MODE TOGGLE
    // =================================================================
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeIcon = document.getElementById('darkModeIcon');
    const body = document.body;
    
    // Cek preferensi dark mode dari localStorage
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    if (isDarkMode) {
        body.classList.add('dark-mode');
        darkModeIcon.classList.remove('fa-moon');
        darkModeIcon.classList.add('fa-sun');
    }
    
    // Toggle dark mode saat diklik
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('dark-mode');
            
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            
            if (isDark) {
                darkModeIcon.classList.remove('fa-moon');
                darkModeIcon.classList.add('fa-sun');
            } else {
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
            }
        });
    }
    
    // =================================================================
    // FLASH MESSAGES dengan SweetAlert2
    // =================================================================
    <?php if (session()->getFlashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        html: '<?= session()->getFlashdata('success') ?>',
        timer: 3000,
        showConfirmButton: false
    });
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= session()->getFlashdata('error') ?>',
        timer: 5000,
        showConfirmButton: true
    });
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('warning')): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Perhatian!',
        text: '<?= session()->getFlashdata('warning') ?>',
        timer: 4000,
        showConfirmButton: true
    });
    <?php endif; ?>
    
    // =================================================================
    // KONFIRMASI HAPUS
    // =================================================================
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const name = $(this).data('name') || 'item ini';
        
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data ' + name + ' akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
    
    // =================================================================
    // DATATABLES DEFAULT CONFIG
    // =================================================================
    if ($.fn.DataTable) {
        $.extend($.fn.dataTable.defaults, {
            language: {
                "sEmptyTable":   "Tidak ada data yang tersedia pada tabel ini",
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ entri",
                "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sInfoPostFix":  "",
                "sSearch":       "Cari:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebaiknya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            responsive: true
        });
    }
    // =================================================================
    // LOADING OVERLAY
    // =================================================================
    // Hilangkan overlay saat halaman selesai dirender
    const loader = document.getElementById('loadingOverlay');
    if (loader) {
        setTimeout(function() {
            $(loader).fadeOut('slow');
        }, 500); // Delay sedikit agar transisi halus
    }

});

// Global function for onclick="confirmDelete('url')"
function confirmDelete(url, text = 'Data akan dihapus permanen!') {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

// Global function for Reset Password
function confirmReset(url) {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password akan dikembalikan ke default: JuriMunaqosah123',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>
