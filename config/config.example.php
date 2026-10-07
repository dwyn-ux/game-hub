<?php
return [
    'app_name' => 'Ashidiq Game Hub',
    'base_url' => 'https://game.smpmuashidiq.sch.id',
    'app_key' => 'GANTI_DENGAN_RANDOM_MINIMAL_32_KARAKTER',
    'setup_secret' => 'GANTI_DENGAN_RANDOM_SETUP_SECRET',
    'setup_enabled' => true,
    'db' => [
        'host' => 'localhost',
        'name' => 'nama_database',
        'user' => 'user_database',
        'pass' => 'password_database',
        'charset' => 'utf8mb4',
    ],
    'upload' => [
        'max_zip_bytes' => 15 * 1024 * 1024,
        'max_extracted_bytes' => 30 * 1024 * 1024,
        'max_files' => 300,
    ],
];
