<?php

namespace App\Core\db;

use App\Core\Application;
use App\Core\Model;

abstract class DbModel extends Model
{
    abstract public static function tableName();
    abstract public static function attributes();
    abstract public static function primaryKey();

    public function save()
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($item) => ":$item", $attributes);
        $statement = self::prepare(
            "INSERT INTO $tableName (" .
            implode(',', $attributes).
            ") values (".
            implode(',', $params).
            ");"
        );

        foreach ($attributes as $attribute){
            $statement->bindValue("$attribute",$this->{$attribute});
        }

        $statement->execute();

        return true;
    }

    public function findOne($where)
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $attributes = implode('AND',array_map(fn($item) => "$item = :$item", $attributes));
        $sql = "SELECT * FROM $tableName WHERE $attributes";
        $statement = self::prepare($sql);
        foreach ($where as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        $statement->execute();
        return $statement->fetchObject(static::class);
    }
    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }
}