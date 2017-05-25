# doriven/InsertDataValidator

This library frees engineers from the task of creating trivial validation of database table definitions.

**This library only support MySQL** (but we plan to support other databases from Version 1)

## Installation

```
$ php composer.phar require doriven/insert-data-validator 
```

## Usage

The definition of the `user` table in MySQL is as follows:

```sql
+----------------+---------------------+------+-----+-------------------+----------------+
| Field          | Type                | Null | Key | Default           | Extra          |
+----------------+---------------------+------+-----+-------------------+----------------+
| id             | int(11)             | NO   | PRI | NULL              | auto_increment |
| age            | tinyint(3) unsigned | YES  |     | NULL              |                |
| email          | varchar(64)         | YES  |     | NULL              |                |
| name           | varchar(32)         | YES  |     | NULL              |                |
| password       | varchar(64)         | YES  |     | NULL              |                |
| reminder_token | varchar(64)         | YES  |     | NULL              |                |
| created_at     | datetime            | YES  |     | NULL              |                |
| updated_at     | timestamp           | NO   |     | CURRENT_TIMESTAMP |                |
+----------------+---------------------+------+-----+-------------------+----------------+
```

You use this library as follows if you insert data to table name of a `user` in program.

```php
<?php

$pdo = new PDO($dsn, $user, $pass);
$v = new \InsertDataValidator\Validator();

$validUserData = [
    'age' => 18,
    'email' => 'hoge@example.com',
    'name' => 'Jack',
    'password' => md5('example' . 'salt'),
    'reminder_token' => '',
    'created_at' => date('Y-m-d H:i:s')
];

$invalidUserData = [
    'age' => 256, // over the unsigned tinyint value range (0 ~ 255)
    'birthday' => '1985-05-15', # not exist column
    'email' => 'hoge@example.com',
    'name' => 'Pablo Diego José Francisco de Paula Juan Nepomuceno María de los Remedios Cipriano de la Santísima Trinidad', # over 32 characters
    'password' => md5('example' . 'salt'),
    'reminder_token' => '',
    'created_at' => date('Y-m-d H:i:s')
];

try {
    $v->validate($pdo, 'user', $validUserData);
    $v->validate($pdo, 'user', $invalidUserData); // Exception thrown
} catch (\InsertDataValidator\Exception\ValidationException $e) {
    echo $e->getMessage(), PHP_EOL;
    /**
     * Exists violation of table schema. table_name = user, errors = Array
     * (
     *     [0] => `age` is not valid. reason = [256 must be less than or equal  to 255]
     *     [1] => `name` is not valid. reason = ["Pablo Diego José Francisco de  Paula Juan Nepomuceno María de los Remedios Cipriano de la Santísima Tri nidad" must have a length lower than 32]
     *     [2] => Insert data has ghost column. ghosts = [`birthday`]
     * )
     */
    throw $e;
}

// DataInsert
// ***
```

This library validate throw exception if insert data has violation.
* insert value over data type range.
* insert value over data type length.
* insert value has not exists column (we called ghost column)

### Method

```
validate($pdo, $tableName, $data, $ignoreValidationColumn = [])
```
* $pdo is \\PDO
* $tableName is the target insert table.
  * You should put constant value because of possible to inject SQL.
  * If variables that can be injected from the outside are used, there is a possibility of becoming a security hole.
* $data is the insert data to the $table.
  * You should format array that has column name => value correspond relation.
* $ignoreValidationColumn is put in the array is excluded from validation.
  * i.e. ['age', 'name']
 
### Unsupported Type

We have not yet implemented all type validation.
The following are data types that have not been implemented.

* Time
* Bit
* Binary
* VarBinary
* TinyText
* Text
* MediumText
* LongText
* TinyBlob
* Blob
* MediumBlob
* LongBlob
* Enum
* Set

Columns containing these types are excluded from validation.
