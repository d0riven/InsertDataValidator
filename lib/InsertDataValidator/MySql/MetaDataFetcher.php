<?php

namespace InsertDataValidator\MySql;


class MetaDataFetcher
{
    public function fetchTableDefinition(\PDO $pdo, $tableName)
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM $tableName");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}