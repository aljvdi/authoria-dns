<?php
/**
 * This file is used to initialise SQLite database
 * @package Javadi\Authoria\DNS
 * @version 1.0.0
 * @since 1.0.0
 * @author Alex Javadi
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Javadi\Authoria\DNS\models\DB;

$db = new DB();

try {

    if ($db->getDBInstance()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='dns'")->fetch() === false) {
        if ($db->getDBInstance()->create('dns', [
            'id' => [
                'INTEGER',
                'PRIMARY KEY',
                'AUTOINCREMENT'
            ],
            'uid' => [
                'TEXT',
                'NOT NULL',
                'UNIQUE'
            ],
            'domain' => [
                'TEXT',
                'NOT NULL'
            ],
            'verified' => [
                'INTEGER',
                'NOT NULL',
                'DEFAULT 0'
            ],
            'created_at' => [
                'INTEGER',
                'NOT NULL'
            ],
            'ttl' => [
                'INTEGER',
                'NOT NULL',
                'DEFAULT 300000'
            ],
            'updated_at' => [
                'INTEGER',
                'NULL'
            ],
        ])) {
            echo "Table 'dns' created successfully\n";
        }
        else {
            echo "Error on creating table dns\n";
        }
    }
    else {
        echo "Table dns already exists\n";
    }
}
catch (\Exception $e) {
    echo "Error on creating table dns: " . $e->getMessage() . "\n";
}
finally {
    unset($db);
}
exit(0);