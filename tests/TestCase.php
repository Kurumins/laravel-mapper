<?php

namespace Tests;
use Illuminate\Database\Capsule\Manager;
use PDOException;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    private $db;

    /**
     * @inheritdoc
     */
    public function setup()
    {
        parent::setUp();
        try {
            $capsule = new Manager;
            $capsule->addConnection([
             'driver' => 'mysql',
             'host' => env('DB_HOST'),
             'database' => env('DB_NAME'),
             'username' => env('DB_USER'),
             'password' => env('DB_PASS'),
            ]);
            // Setup the Eloquent ORM…
            $capsule->bootEloquent();
            $this->db = $capsule->getDatabaseManager();
        } catch (PDOException $e) {
            exit('Connection failed: ' . $e->getMessage());
        }
        $this->resetDb();
    }

    /**
     * @return \Illuminate\Database\DatabaseManager
     */
    public function getConnection(){
        return $this->db;
    }

    /**
     * Delete all previous table in the DB and creates a fresh test db.
     *
     * @return void
     */
    private function resetDb()
    {
        $this->db->statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($this->db->select('SHOW TABLES') as $table) {
            $tableList = get_object_vars($table);
            $this->db->delete('DROP TABLE ' . $tableList[key($tableList)]);
        }
        $this->db->statement('SET FOREIGN_KEY_CHECKS=1;');
        $command = "mysql -u " . env('DB_USER') . " -p" . env('DB_PASS') . " " . env('DB_NAME') . " < " . __DIR__ . '/db-test.sql';
        `$command`;
    }
}

