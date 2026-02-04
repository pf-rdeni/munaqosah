<?php

/**
 * ====================================================================
 * BASE CONTROLLER
 * ====================================================================
 * Controller dasar yang diwarisi oleh semua controller lain
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Instance dari request saat ini
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Array helper yang akan di-load otomatis
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form', 'auth'];

    /**
     * Session
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Database
     *
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Data yang akan dikirim ke view
     *
     * @var array
     */
    protected $data = [];

    /**
     * Inisialisasi controller
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Load session
        $this->session = \Config\Services::session();
        
        // Load database
        $this->db = \Config\Database::connect();
        
        // Set data global untuk view
        $this->data['siteName'] = 'Munaqosah SDIT An-Nahl';
        $this->data['siteDescription'] = 'Sistem Penilaian Ujian Munaqosah';
    }

    /**
     * Cek apakah user sudah login
     *
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        return $this->session->get('logged_in') === true;
    }

    /**
     * Redirect ke login jika belum login
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }
        return null;
    }

    /**
     * Ambil data user yang sedang login
     *
     * @return array|null
     */
    protected function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        // Ambil data terbaru dari DB untuk memastikan foto profile update
        $dbUser = $this->db->table('users')->select('user_image')->where('id', $this->session->get('user_id'))->get()->getRowArray();

        $currentUser = [
            'id'       => $this->session->get('user_id'),
            'username' => $this->session->get('username'),
            'fullname' => $this->session->get('fullname'),
            'email'    => $this->session->get('email'),
            'user_image' => $dbUser['user_image'] ?? null,
            'groups'   => $this->session->get('groups') ?? [],
        ];



        return $currentUser;
    }

    /**
     * Cek apakah user memiliki group tertentu
     *
     * @param string|array $groupName
     * @return bool
     */
    protected function inGroups($groupName): bool
    {
        $userGroups = $this->session->get('groups') ?? [];
        
        if (is_array($groupName)) {
            return count(array_intersect($groupName, $userGroups)) > 0;
        }
        
        return in_array($groupName, $userGroups);
    }

    /**
     * Set flash message
     *
     * @param string $type success, error, warning, info
     * @param string $message
     */
    protected function setFlash(string $type, string $message): void
    {
        $this->session->setFlashdata($type, $message);
    }

    /**
     * Get Tahun Ajaran Dynamic
     * 
     * @return string
     */
    protected function getTahunAjaran(): string
    {
        $bulan = (int)date('m');
        $tahun = (int)date('Y');
        return ($bulan >= 7) ? $tahun . '/' . ($tahun + 1) : ($tahun - 1) . '/' . $tahun;
    }
}
