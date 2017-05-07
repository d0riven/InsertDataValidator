<?php

/**
 * クラスを使わない構成で一旦書いてみて、実装の手順を確認する
 */

require_once 'vendor/autoload.php';

// tests at local development
$database = 'test';
$host = 'localhost';
$dsn = sprintf('mysql:dbname=%s;host=%s', $database, $host);
$pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_CASE              => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false
    ]
);

$v = new \InsertDataValidator\Validator();

$v->validate($pdo, 'a', ['id' => '1', 'data' => '1.4'], []);
