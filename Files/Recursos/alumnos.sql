CREATE DATABASE registro_alumnos;

CREATE table registro (
    id_alumno INT PRIMARY KEY AUTO_INCREMENT,
    no_cuenta INT(8),
    primer_apellido VARCHAR(255),
    segundo_apellido VARCHAR(255),
    nombres VARCHAR(255),
    correo VARCHAR(255),
    contacto VARCHAR(10)
);