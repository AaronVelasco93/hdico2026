CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registro (
    id_alumno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_cuenta VARCHAR(9) NOT NULL UNIQUE,
    primer_apellido VARCHAR(255) NOT NULL,
    segundo_apellido VARCHAR(255) NULL,
    nombres VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,
    contacto VARCHAR(10) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, password_hash, display_name)
VALUES ('admin', '$2y$10$1MMd9IWyT6GPsYPwXmJJDub5UOGv/d3SQgslwJVkuatL7UkH.Yf76', 'Administrador')
ON DUPLICATE KEY UPDATE username = username;
