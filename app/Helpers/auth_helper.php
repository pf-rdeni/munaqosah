<?php

/**
 * ====================================================================
 * AUTH HELPER
 * ====================================================================
 * Helper functions untuk autentikasi
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

if (!function_exists('user')) {
    /**
     * Ambil data user yang sedang login
     *
     * @return object|null
     */
    function user()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return null;
        }

        return (object)[
            'id'       => $session->get('user_id'),
            'username' => $session->get('username'),
            'fullname' => $session->get('fullname'),
            'email'    => $session->get('email'),
        ];
    }
}

if (!function_exists('user_id')) {
    /**
     * Ambil ID user yang sedang login
     *
     * @return int|null
     */
    function user_id()
    {
        return session()->get('user_id');
    }
}

if (!function_exists('logged_in')) {
    /**
     * Cek apakah user sudah login
     *
     * @return bool
     */
    function logged_in(): bool
    {
        return session()->get('logged_in') === true;
    }
}

if (!function_exists('in_groups')) {
    /**
     * Cek apakah user memiliki group tertentu
     *
     * @param string|array $groupName
     * @return bool
     */
    function in_groups($groupName): bool
    {
        $userGroups = session()->get('groups') ?? [];
        
        if (is_array($groupName)) {
            return count(array_intersect($groupName, $userGroups)) > 0;
        }
        
        return in_array($groupName, $userGroups);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Cek apakah user memiliki permission tertentu
     *
     * @param string $permission
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        // Untuk saat ini, admin memiliki semua permission
        if (in_groups('admin')) {
            return true;
        }
        
        // TODO: Implementasi check permission dari database
        return false;
    }
}
