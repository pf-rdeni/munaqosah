<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;

class Dokumentasi extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dokumentasi Sistem',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/index', $data);
    }

    public function siswa()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Data Siswa',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/siswa', $data);
    }

    public function peserta()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Registrasi Peserta',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/peserta', $data);
    }

    public function monitoring()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Monitoring Nilai',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/monitoring', $data);
    }

    public function antrian()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Antrian',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/antrian', $data);
    }

    public function penilaian()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Penilaian',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/penilaian', $data);
    }

    public function konfigurasi()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Konfigurasi',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/konfigurasi', $data);
    }

    public function penjurian()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Penjurian',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/penjurian', $data);
    }

    public function sertifikat()
    {
        $data = [
            'title' => 'Dokumentasi Sistem Sertifikat',
            'user'  => $this->getCurrentUser()
        ];
        return view('backend/dokumentasi/sertifikat', $data);
    }
}
