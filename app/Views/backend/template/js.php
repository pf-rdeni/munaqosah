<!-- jQuery -->
<script src="<?= base_url('template/backend/plugins/jquery/jquery.min.js') ?>"></script>

<!-- Bootstrap 4 -->
<script src="<?= base_url('template/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

<!-- AdminLTE App -->
<script src="<?= base_url('template/backend/dist/js/adminlte.min.js') ?>"></script>

<!-- DataTables -->
<script src="<?= base_url('template/backend/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('template/backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>

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
        text: '<?= session()->getFlashdata('success') ?>',
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
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                emptyTable: 'Tidak ada data',
                zeroRecords: 'Tidak ada data yang cocok',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                search: 'Cari:',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            responsive: true
        });
    }
});
</script>
