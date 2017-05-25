CREATE TABLE IF NOT EXISTS test_type_range (
  `d_bit` BIT,

  `d_tinyint` TINYINT,
  `d_utinyint` TINYINT UNSIGNED,

  `d_smallint` SMALLINT,
  `d_usmallint` SMALLINT UNSIGNED,

  `d_mediumint` MEDIUMINT,
  `d_umediumint` MEDIUMINT UNSIGNED,

  `d_int` INT,
  `d_uint` INT UNSIGNED,

  `d_bigint` BIGINT,
  `d_ubigint` BIGINT UNSIGNED,

  `d_decimal` DECIMAL,
  `d_udecimal` DECIMAL UNSIGNED,

  `d_float` FLOAT,
  `d_ufloat` FLOAT UNSIGNED,

  `d_double` DOUBLE,
  `d_udouble` DOUBLE UNSIGNED,

  `d_date` DATE,
  `d_datetime` DATETIME,
  `d_time` TIME,
  `d_timestamp` TIMESTAMP,
  `d_year_2` YEAR(2), -- Unsupported YEAR(2) greater than or equals MySQL5.6.6
  `d_year_4` YEAR(4),

  `d_char` CHAR(255),
  `d_varchar` VARCHAR(255),

  `d_binary` BINARY(255),
  `d_varbinariy` VARBINARY(255),

  `d_tinytext` TINYTEXT,
  `d_text` TEXT,
  `d_mediumtext` MEDIUMTEXT,
  `d_longtext` LONGTEXT,

  `d_tinyblob` TINYBLOB,
  `d_blob` BLOB,
  `d_mediumblob` MEDIUMBLOB,
  `d_longblob` LONGBLOB,

  `d_enum` ENUM('one', 'two', 'three'),
  `d_set` SET('one', 'two', 'three')
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS test_type_length (
  `d_char` CHAR(32),
  `d_varchar` VARCHAR(32)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS test_null (
  `d_null_allowable` TINYINT NULL,
  `d_null_block` TINYINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


