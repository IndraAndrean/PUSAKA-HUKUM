<?php

return [
    'disk' => 'local',
    'directory' => 'backups',
    'temporary_directory' => 'temp/backups',
    'retention' => (int) env('BACKUP_RETENTION', 20),
    'mysqldump_binary' => env('MYSQLDUMP_BINARY'),
    'mysql_binary' => env('MYSQL_BINARY'),
    'timeout' => (int) env('BACKUP_PROCESS_TIMEOUT', 600),
];
