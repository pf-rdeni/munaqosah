<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class SettingUndianModel extends Model
{
    protected $table = 'tbl_munaqosah_setting_undian';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'key',
        'value',
        'tahun_ajaran'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get setting value by key and tahun ajaran
     */
    public function getSetting(string $key, string $tahunAjaran): ?string
    {
        $result = $this->where('key', $key)
                       ->where('tahun_ajaran', $tahunAjaran)
                       ->first();
        return $result ? $result['value'] : null;
    }

    /**
     * Set or update a setting
     */
    public function setSetting(string $key, string $value, string $tahunAjaran): bool
    {
        $existing = $this->where('key', $key)
                         ->where('tahun_ajaran', $tahunAjaran)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], ['value' => $value]);
        }

        return $this->insert([
            'key' => $key,
            'value' => $value,
            'tahun_ajaran' => $tahunAjaran
        ]) !== false;
    }

    /**
     * Get all settings for tahun ajaran
     */
    public function getAllSettings(string $tahunAjaran): array
    {
        $results = $this->where('tahun_ajaran', $tahunAjaran)->findAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }
}
