<?php
// Configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_munaqosah';

// Old tables to drop
$tables = [
    'siswa',
    'peserta',
    'antrian',
    'juri',
    'grup_materi',
    'materi_ujian',
    'kriteria_materi_ujian',
    'nilai_ujian',
    'alquran',
    'bobot',
    'tanda_tangan'
];

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Connected to database '$db'.\n";
echo "Dropping old tables...\n";

foreach ($tables as $table) {
    // Check if table exists first (optional with IF EXISTS, but good for logging)
    $sql = "DROP TABLE IF EXISTS `$table`";
    if ($mysqli->query($sql) === TRUE) {
        echo "Dropped table: $table\n";
    } else {
        echo "Error dropping table $table: " . $mysqli->error . "\n";
    }
}

echo "Cleanup complete.\n";
$mysqli->close();
