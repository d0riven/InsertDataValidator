<?php

namespace InsertDataValidator;

use PDO;

class Validator
{
    public function validate(PDO $pdo, $tableName)
    {
        $this->loadTableSchema($pdo, $tableName);
    }

    public function loadTableSchema(PDO $pdo, $tableName)
    {
        $stmt = $pdo->query("show columns from $tableName");
        $schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($schema as $columnMetaData) {
            new Column($columnMetaData['field'], $columnMetaData['type'], $columnMetaData['null'], $columnMetaData['default']);
        }
    }
}