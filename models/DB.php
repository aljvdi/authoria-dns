<?php

namespace Javadi\Authoria\DNS\models;

use Medoo\Medoo;

class DB
{
    private Medoo $_db;
    private string $_db_file = __DIR__ . '/../db/dns.db';

    public function __construct()
    {
        if (!file_exists($this->_db_file)) {
            // Use SQLite3 to create the database file
            try {
                $dns_db = new \SQLite3($this->_db_file);
            }
            catch (\Exception $e) {
                die("Error on creating database: " . $e->getMessage());
            } finally {
                $dns_db->close();
                unset($dns_db);
            }
        }

        $this->_db = new Medoo([
            'type' => 'sqlite',
            'database' => __DIR__ . '/../db/dns.db'
        ]);
    }

    public function getDBInstance(): Medoo
    {
        return $this->_db;
    }

    public static function uuidV4(): string {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

}