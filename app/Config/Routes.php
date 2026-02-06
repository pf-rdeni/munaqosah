<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================================
// ROUTE PUBLIK (TANPA LOGIN)
// =====================================================================

// Halaman Login
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

// Halaman utama redirect ke login
$routes->get('/', 'AuthController::login');

// =====================================================================
// ROUTE BACKEND (MEMBUTUHKAN LOGIN)
// =====================================================================

$routes->group('backend', ['namespace' => 'App\Controllers\Backend'], static function($routes) {
    
    // Dashboard
    $routes->get('/', 'Munaqosah\Dashboard::index');
    $routes->get('dashboard', 'Munaqosah\Dashboard::index');
    $routes->get('index', 'Munaqosah\Dashboard::index'); // Fix 404 for backend/index
    
    // Academic Year Switching
    $routes->post('tahun-ajaran/switch', 'TahunAjaran::switch');
    
    // Sertifikat Munaqosah
    $routes->group('sertifikat', ['namespace' => 'App\Controllers\Backend\Munaqosah'], function ($routes) {
        $routes->get('/', 'Sertifikat::index');
        $routes->post('upload', 'Sertifikat::uploadTemplate');
        $routes->get('configure/(:any)', 'Sertifikat::configure/$1');
        $routes->post('save-config', 'Sertifikat::saveFieldConfig');
        $routes->get('preview/(:any)', 'Sertifikat::preview/$1');
        $routes->get('delete/(:any)', 'Sertifikat::delete/$1');
    });

    // Cetak Sertifikat
    $routes->group('cetak-sertifikat', ['namespace' => 'App\Controllers\Backend\Munaqosah'], function ($routes) {
        $routes->get('/', 'CetakSertifikat::index');
        $routes->get('print/(:num)', 'CetakSertifikat::print/$1');
        $routes->get('print-batch', 'CetakSertifikat::printBatch');
    });

    // Data Siswa (Pindah ke Munaqosah)
    $routes->get('siswa', 'Munaqosah\Siswa::index');
    $routes->get('siswa/create', 'Munaqosah\Siswa::create');
    $routes->post('siswa/store', 'Munaqosah\Siswa::store');
    $routes->get('siswa/detail/(:num)', 'Munaqosah\Siswa::detail/$1');
    $routes->get('siswa/edit/(:num)', 'Munaqosah\Siswa::edit/$1');
    $routes->post('siswa/update/(:num)', 'Munaqosah\Siswa::update/$1');
    $routes->post('siswa/updateFoto', 'Munaqosah\Siswa::updateFoto');
    $routes->get('siswa/delete/(:num)', 'Munaqosah\Siswa::delete/$1');
    $routes->post('siswa/import', 'Munaqosah\Siswa::import');
    $routes->post('siswa/saveImport', 'Munaqosah\Siswa::saveImport');
    $routes->post('siswa/updateHafalan', 'Munaqosah\Siswa::updateHafalan');
    $routes->get('siswa/downloadTemplate', 'Munaqosah\Siswa::downloadTemplate');

    // Manajemen User
    $routes->get('users', 'Users::index');
    $routes->get('users/create', 'Users::create');
    $routes->post('users/store', 'Users::store');
    $routes->get('users/edit/(:num)', 'Users::edit/$1');
    $routes->post('users/update/(:num)', 'Users::update/$1');
    $routes->get('users/delete/(:num)', 'Users::delete/$1');

    // Setting
    $routes->group('setting', function($routes) {
        $routes->get('reset-nilai', 'Munaqosah\ResetNilai::index');
        $routes->post('reset-nilai/preview', 'Munaqosah\ResetNilai::preview');
        $routes->post('reset-nilai/execute', 'Munaqosah\ResetNilai::execute');
    });

    // Manajemen Juri
    $routes->get('juri', 'Munaqosah\Juri::index');
    $routes->get('juri/create', 'Munaqosah\Juri::create');
    $routes->post('juri/store', 'Munaqosah\Juri::store');
    $routes->get('juri/edit/(:num)', 'Munaqosah\Juri::edit/$1');
    $routes->post('juri/update/(:num)', 'Munaqosah\Juri::update/$1');
    $routes->get('juri/delete/(:num)', 'Munaqosah\Juri::delete/$1');
    $routes->get('juri/reset-password/(:num)', 'Munaqosah\Juri::resetPassword/$1');
    $routes->get('juri/getJuriKriteria/(:num)', 'Munaqosah\Juri::getJuriKriteria/$1');
    $routes->post('juri/saveJuriKriteria', 'Munaqosah\Juri::saveJuriKriteria');
    $routes->get('juri/generateUsername/(:num)', 'Munaqosah\Juri::generateUsername/$1');
    $routes->post('juri/updateGrupJuri', 'Munaqosah\Juri::updateGrupJuri');

    // Manajemen Grup Materi
    $routes->get('grup-materi', 'Munaqosah\GrupMateri::index');
    $routes->get('grup-materi/create', 'Munaqosah\GrupMateri::create');
    $routes->post('grup-materi/store', 'Munaqosah\GrupMateri::store');
    $routes->get('grup-materi/edit/(:num)', 'Munaqosah\GrupMateri::edit/$1');
    $routes->post('grup-materi/update/(:num)', 'Munaqosah\GrupMateri::update/$1');
    $routes->get('grup-materi/delete/(:num)', 'Munaqosah\GrupMateri::delete/$1');

    // Manajemen Materi Ujian
    $routes->get('materi', 'Munaqosah\Materi::index');
    $routes->get('materi/create', 'Munaqosah\Materi::create');
    $routes->post('materi/store', 'Munaqosah\Materi::store');
    $routes->get('materi/edit/(:num)', 'Munaqosah\Materi::edit/$1');
    $routes->post('materi/update/(:num)', 'Munaqosah\Materi::update/$1');
    $routes->get('materi/delete/(:num)', 'Munaqosah\Materi::delete/$1');

    // Manajemen Kriteria Materi
    $routes->get('materi/kriteria/(:num)', 'Munaqosah\Kriteria::index/$1');
    $routes->post('kriteria/store/(:num)', 'Munaqosah\Kriteria::store/$1');
    $routes->post('kriteria/update/(:num)', 'Munaqosah\Kriteria::update/$1');
    $routes->get('kriteria/delete/(:num)', 'Munaqosah\Kriteria::delete/$1');

    // Manajemen Rubrik Penilaian
    $routes->get('rubrik', 'Munaqosah\Rubrik::index');
    $routes->get('rubrik/manage/(:num)', 'Munaqosah\Rubrik::manage/$1');
    $routes->post('rubrik/save', 'Munaqosah\Rubrik::save');

    // Manajemen Kriteria Skoring (Predikat)
    $routes->get('predikat', 'Munaqosah\Predikat::index');
    $routes->get('predikat/create', 'Munaqosah\Predikat::create');
    $routes->get('predikat/copy/(:num)', 'Munaqosah\Predikat::copy/$1');
    $routes->post('predikat/store', 'Munaqosah\Predikat::store');
    $routes->get('predikat/edit/(:num)', 'Munaqosah\Predikat::edit/$1');
    $routes->post('predikat/update/(:num)', 'Munaqosah\Predikat::update/$1');
    $routes->get('predikat/delete/(:num)', 'Munaqosah\Predikat::delete/$1');

    // Registrasi Peserta (Undian No Tes)
    $routes->get('peserta', 'Munaqosah\Peserta::index');
    $routes->post('peserta/undian', 'Munaqosah\Peserta::undian');
    $routes->post('peserta/reset', 'Munaqosah\Peserta::reset');
    $routes->post('peserta/saveSettings', 'Munaqosah\Peserta::saveSettings');
    $routes->post('peserta/saveTahfidzPilihan', 'Munaqosah\Peserta::saveTahfidzPilihan');
    $routes->get('peserta/printKartu', 'Munaqosah\Peserta::printKartu');
    $routes->get('peserta/printKartu/(:segment)', 'Munaqosah\Peserta::printKartu/$1');

    // Monitoring Nilai
    $routes->get('monitoring/nilai', 'Munaqosah\MonitoringNilai::index');

    // Input Nilai (Juri)
    $routes->get('munaqosah/input-nilai', 'Munaqosah\InputNilai::index');
    $routes->post('munaqosah/input-nilai/load-form', 'Munaqosah\InputNilai::loadForm');
    $routes->post('munaqosah/input-nilai/save', 'Munaqosah\InputNilai::save');
    $routes->post('munaqosah/input-nilai/authorize-edit', 'Munaqosah\InputNilai::authorizeEdit');
    $routes->post('munaqosah/input-nilai/refresh-history', 'Munaqosah\InputNilai::refreshHistory');
    $routes->get('munaqosah/input-nilai/get-next-peserta-from-antrian', 'Munaqosah\InputNilai::getNextPesertaFromAntrian');

    // Manajemen Antrian
    $routes->get('antrian', 'Antrian::index');
    $routes->get('antrian/monitoring', 'Antrian::monitoring');
    $routes->get('antrian/get-queue-data', 'Antrian::getQueueData');
    $routes->post('antrian/register', 'Antrian::register');
    $routes->post('antrian/update-status', 'Antrian::updateStatus');
    $routes->post('antrian/delete', 'Antrian::delete');

    // Dokumentasi Sistem
    $routes->group('dokumentasi', function($routes) {
        $routes->get('/', 'Munaqosah\Dokumentasi::index');
        $routes->get('siswa', 'Munaqosah\Dokumentasi::siswa');
        $routes->get('peserta', 'Munaqosah\Dokumentasi::peserta');
        $routes->get('monitoring', 'Munaqosah\Dokumentasi::monitoring');
        $routes->get('antrian', 'Munaqosah\Dokumentasi::antrian');
        $routes->get('penilaian', 'Munaqosah\Dokumentasi::penilaian');
        $routes->get('konfigurasi', 'Munaqosah\Dokumentasi::konfigurasi');
        $routes->get('penjurian', 'Munaqosah\Dokumentasi::penjurian');
        $routes->get('sertifikat', 'Munaqosah\Dokumentasi::sertifikat');
    });

    // Profil User
    $routes->get('profil', 'Profil::index');
    $routes->post('profil/update', 'Profil::update');
    $routes->post('profil/updateFoto', 'Profil::updateFoto');
});
