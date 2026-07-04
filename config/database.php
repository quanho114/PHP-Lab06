<?php

return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_NAME') ?: 'training_crm',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'root',
    'charset'  => 'utf8mb4',
];
