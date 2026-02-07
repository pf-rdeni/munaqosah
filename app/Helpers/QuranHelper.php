<?php

namespace App\Helpers;

/**
 * QuranHelper
 * Helper class untuk memuat dan mengelola data Al-Quran dari file JSON
 */
class QuranHelper
{
    private static $cache = [];
    private static $basePath = FCPATH . 'assets/quran/json/';
    
    /**
     * Daftar nama surah (1-114)
     */
    private static $surahNames = [
        1 => 'Al-Fatihah', 2 => 'Al-Baqarah', 3 => 'Ali \'Imran', 4 => 'An-Nisa\'',
        5 => 'Al-Ma\'idah', 6 => 'Al-An\'am', 7 => 'Al-A\'raf', 8 => 'Al-Anfal',
        9 => 'At-Taubah', 10 => 'Yunus', 11 => 'Hud', 12 => 'Yusuf',
        13 => 'Ar-Ra\'d', 14 => 'Ibrahim', 15 => 'Al-Hijr', 16 => 'An-Nahl',
        17 => 'Al-Isra\'', 18 => 'Al-Kahf', 19 => 'Maryam', 20 => 'Taha',
        21 => 'Al-Anbiya\'', 22 => 'Al-Hajj', 23 => 'Al-Mu\'minun', 24 => 'An-Nur',
        25 => 'Al-Furqan', 26 => 'Ash-Shu\'ara\'', 27 => 'An-Naml', 28 => 'Al-Qasas',
        29 => 'Al-\'Ankabut', 30 => 'Ar-Rum', 31 => 'Luqman', 32 => 'As-Sajdah',
        33 => 'Al-Ahzab', 34 => 'Saba\'', 35 => 'Fatir', 36 => 'Ya-Sin',
        37 => 'As-Saffat', 38 => 'Sad', 39 => 'Az-Zumar', 40 => 'Ghafir',
        41 => 'Fussilat', 42 => 'Ash-Shura', 43 => 'Az-Zukhruf', 44 => 'Ad-Dukhan',
        45 => 'Al-Jathiyah', 46 => 'Al-Ahqaf', 47 => 'Muhammad', 48 => 'Al-Fath',
        49 => 'Al-Hujurat', 50 => 'Qaf', 51 => 'Adh-Dhariyat', 52 => 'At-Tur',
        53 => 'An-Najm', 54 => 'Al-Qamar', 55 => 'Ar-Rahman', 56 => 'Al-Waqi\'ah',
        57 => 'Al-Hadid', 58 => 'Al-Mujadilah', 59 => 'Al-Hashr', 60 => 'Al-Mumtahanah',
        61 => 'As-Saf', 62 => 'Al-Jumu\'ah', 63 => 'Al-Munafiqun', 64 => 'At-Taghabun',
        65 => 'At-Talaq', 66 => 'At-Tahrim', 67 => 'Al-Mulk', 68 => 'Al-Qalam',
        69 => 'Al-Haqqah', 70 => 'Al-Ma\'arij', 71 => 'Nuh', 72 => 'Al-Jinn',
        73 => 'Al-Muzzammil', 74 => 'Al-Muddaththir', 75 => 'Al-Qiyamah', 76 => 'Al-Insan',
        77 => 'Al-Mursalat', 78 => 'An-Naba\'', 79 => 'An-Nazi\'at', 80 => '\'Abasa',
        81 => 'At-Takwir', 82 => 'Al-Infitar', 83 => 'Al-Mutaffifin', 84 => 'Al-Inshiqaq',
        85 => 'Al-Buruj', 86 => 'At-Tariq', 87 => 'Al-A\'la', 88 => 'Al-Ghashiyah',
        89 => 'Al-Fajr', 90 => 'Al-Balad', 91 => 'Ash-Shams', 92 => 'Al-Lail',
        93 => 'Ad-Duha', 94 => 'Ash-Sharh', 95 => 'At-Tin', 96 => 'Al-\'Alaq',
        97 => 'Al-Qadr', 98 => 'Al-Bayyinah', 99 => 'Az-Zalzalah', 100 => 'Al-\'Adiyat',
        101 => 'Al-Qari\'ah', 102 => 'At-Takathur', 103 => 'Al-\'Asr', 104 => 'Al-Humazah',
        105 => 'Al-Fil', 106 => 'Quraish', 107 => 'Al-Ma\'un', 108 => 'Al-Kauthar',
        109 => 'Al-Kafirun', 110 => 'An-Nasr', 111 => 'Al-Masad', 112 => 'Al-Ikhlas',
        113 => 'Al-Falaq', 114 => 'An-Nas'
    ];

    /**
     * Get surah data from JSON file
     * 
     * @param int $surahNumber Nomor surah (1-114)
     * @return array|null
     */
    public static function getSurahData(int $surahNumber): ?array
    {
        if ($surahNumber < 1 || $surahNumber > 114) {
            return null;
        }

        // Check cache
        if (isset(self::$cache[$surahNumber])) {
            return self::$cache[$surahNumber];
        }

        $filePath = self::$basePath . $surahNumber . '.json';
        
        if (!file_exists($filePath)) {
            return null;
        }

        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        // Cache the data
        self::$cache[$surahNumber] = $data;

        return $data;
    }

    /**
     * Get specific verses from a surah
     * 
     * @param int $surahNumber Nomor surah
     * @param int $fromAyah Ayat awal
     * @param int $toAyah Ayat akhir (optional, default = fromAyah)
     * @return array
     */
    public static function getVerses(int $surahNumber, int $fromAyah, int $toAyah = null): array
    {
        $surahData = self::getSurahData($surahNumber);
        
        if (!$surahData) {
            return [];
        }

        $toAyah = $toAyah ?? $fromAyah;
        $surahKey = (string)$surahNumber;
        
        if (!isset($surahData[$surahKey])) {
            return [];
        }

        $result = [
            'surah_number' => $surahNumber,
            'surah_name' => $surahData[$surahKey]['name_latin'] ?? self::$surahNames[$surahNumber],
            'surah_name_arabic' => $surahData[$surahKey]['name'] ?? '',
            'verses' => []
        ];

        for ($i = $fromAyah; $i <= $toAyah; $i++) {
            $ayahKey = (string)$i;
            
            if (isset($surahData[$surahKey]['text'][$ayahKey])) {
                $result['verses'][] = [
                    'ayah_number' => $i,
                    'text_arabic' => $surahData[$surahKey]['text'][$ayahKey],
                    'translation_id' => $surahData[$surahKey]['translations']['id']['text'][$ayahKey] ?? ''
                ];
            }
        }

        return $result;
    }

    /**
     * Get list of all surahs
     * 
     * @return array
     */
    public static function getSurahList(): array
    {
        $list = [];
        
        for ($i = 1; $i <= 114; $i++) {
            $surahData = self::getSurahData($i);
            $surahKey = (string)$i;
            
            $list[] = [
                'number' => $i,
                'name' => self::$surahNames[$i],
                'name_arabic' => $surahData[$surahKey]['name'] ?? '',
                'number_of_ayah' => $surahData[$surahKey]['number_of_ayah'] ?? 0
            ];
        }

        return $list;
    }

    /**
     * Get verses by Mushaf page number (Utsmani)
     * 
     * @param int $pageNumber Nomor halaman (1-604)
     * @return array
     */
    public static function getVersesByPage(int $pageNumber): array
    {
        $pageMapping = self::getPageMapping();
        
        if (!isset($pageMapping[$pageNumber])) {
            return [];
        }

        $pageInfo = $pageMapping[$pageNumber];
        $result = [
            'page_number' => $pageNumber,
            'verses' => []
        ];

        // A page can contain verses from multiple surahs
        foreach ($pageInfo as $surahInfo) {
            $verses = self::getVerses(
                $surahInfo['surah'],
                $surahInfo['from_ayah'],
                $surahInfo['to_ayah']
            );
            
            if (!empty($verses['verses'])) {
                $result['verses'] = array_merge($result['verses'], $verses['verses']);
            }
        }

        return $result;
    }

    /**
     * Get Mushaf page mapping
     * This is a simplified mapping - in production, use complete mapping file
     * 
     * @return array
     */
    private static function getPageMapping(): array
    {
        // Load from JSON file if exists
        $mappingFile = self::$basePath . '../mushaf_pages.json';
        
        if (file_exists($mappingFile)) {
            $content = file_get_contents($mappingFile);
            return json_decode($content, true) ?? [];
        }

        // Fallback: basic mapping (simplified, not complete)
        return [
            1 => [['surah' => 1, 'from_ayah' => 1, 'to_ayah' => 7]],
            2 => [['surah' => 2, 'from_ayah' => 1, 'to_ayah' => 5]],
            // ... (will be completed with full mapping file)
        ];
    }
}
