<?php

namespace App\Core\db;

use App\Core\Application;
use PDO;

class Database
{
    public PDO $pdo;

    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        $this->pdo = new PDO($dsn,$user,$password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR.'/Migrations');
        $toApplyMigrations = array_diff($files,$appliedMigrations);
        foreach ($toApplyMigrations as $migration){
            if ($migration === '.' || $migration === '..'){
                continue;
            }

            require_once Application::$ROOT_DIR.'/Migrations/'.$migration;
            $className = pathinfo($migration,PATHINFO_FILENAME);
            $classNameSpace = "Migrations\\".$className;
            $instance = new $classNameSpace();
            echo 'Applying migration '.$migration.PHP_EOL;
            $instance->up();
            echo 'Applied migration'.$migration.PHP_EOL;
            $newMigrations[] = $migration;

        }

        if (!empty($newMigrations)){
            $this->saveMigrations($newMigrations);
        } else {
            $this->log('All migrations are applied');
        }

    }

    public function saveMigrations(array $migration)
    {
        $str = implode(',',array_map(fn($m) => "('$m')",$migration));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) values 
            $str
        ");
        $statement->execute();
    }
    public function createMigrationsTable()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id int AUTO_INCREMENT primary key,
                migration VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) 
        ENGINE = INNODB;");
    }

    public function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT * FROM migrations");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function log($message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
}