CREATE DATABASE IF NOT EXISTS registro_alumnos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE registro_alumnos;

DROP TABLE IF EXISTS registro;

CREATE TABLE registro (
    id_alumno INT AUTO_INCREMENT PRIMARY KEY,
    no_cuenta BIGINT NOT NULL,
    primer_apellido VARCHAR(100) NOT NULL,
    segundo_apellido VARCHAR(100) DEFAULT NULL,
    nombres VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    contacto VARCHAR(10) NOT NULL
);
