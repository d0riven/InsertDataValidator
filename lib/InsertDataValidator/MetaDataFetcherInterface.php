<?php

namespace InsertDataValidator;


interface MetaDataFetcherInterface
{
    public function fetchTableDefinition(\PDO $pdo, $tableName);
}