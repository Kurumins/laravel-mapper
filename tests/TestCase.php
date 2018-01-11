<?php

namespace Tests;
use Illuminate\Database\Capsule\Manager;
use PDO;
use PDOException;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Manager
     */
    private $db;

    public function setup()
    {
        parent::setUp();
        try {
//            $pdo = new PDO('mysql:dbname='.env('DB_NAME').';host='.env('DB_HOST'), env('DB_USER'), env('DB_PASS'));
//            $this->db = new MySqlConnection($pdo, env('DB_USER'), env('DB_PASS'));

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
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    /**
     * @return Manager
     */
    public function getConnection(){
        return $this->db;
    }
}

