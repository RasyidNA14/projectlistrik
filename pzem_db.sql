CREATE DATABASE IF NOT EXISTS pzem_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pzem_db;

CREATE TABLE IF NOT EXISTS sensor_data (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tegangan   FLOAT        NOT NULL COMMENT 'Volt (V)',
  arus       FLOAT        NOT NULL COMMENT 'Ampere (A)',
  daya       FLOAT        NOT NULL COMMENT 'Watt (W)',
  waktu      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
