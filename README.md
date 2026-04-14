# HDICO 2026

Aplicacion web para gestion de alumnos con autenticacion, CRUD y exportacion CSV, desplegada en una arquitectura de 3 contenedores con Docker.

## Descripcion general

La version actual del proyecto esta organizada en servicios independientes:

- `frontend`: interfaz web en HTML, CSS y JavaScript
- `backend`: API en PHP 8.3 + Apache
- `db`: base de datos MySQL 8.4

El sistema permite:

- Iniciar sesion
- Consultar alumnos registrados
- Registrar alumnos
- Modificar alumnos
- Eliminar alumnos
- Exportar la informacion a CSV

## Tecnologias utilizadas

- Docker
- Docker Compose v2
- PHP 8.3
- Apache
- MySQL 8.4
- `mysqli`
- Nginx 1.27 Alpine
- HTML5
- CSS3
- JavaScript vanilla

## Arquitectura

El proyecto corre con tres contenedores:

1. `db`
   Base de datos MySQL con volumen persistente e inicializacion automatica mediante script SQL.
2. `backend`
   API PHP que maneja autenticacion, sesiones, validaciones, CRUD y exportacion CSV.
3. `frontend`
   Aplicacion web estatica que consume la API usando `fetch` y mantiene la sesion con `credentials: include`.

## Requisitos

- Docker Desktop activo
- Docker Compose v2

## Como levantar el proyecto

Desde la carpeta `src/` ejecuta:

```bash
docker compose up --build
```

Para detenerlo:

```bash
docker compose down
```

Para reiniciar desde cero y eliminar el volumen de base de datos:

```bash
docker compose down -v
docker compose up --build
```

## URLs del entorno

- Frontend: `http://localhost:8080`
- Backend API: `http://localhost:8000/api.php`
- MySQL expuesto localmente: `localhost:3307`

## Credenciales iniciales

- Usuario: `admin`
- Password: `Admin123*`

Estas credenciales se crean automaticamente desde [src/db/init/01_schema.sql](/Users/huronmarron/Desktop/clases2026/hdico2026/src/db/init/01_schema.sql).

## Funcionalidad implementada

- Login por sesion
- Verificacion de sesion activa
- Logout
- CRUD completo de alumnos
- Validacion de datos en backend
- Exportacion de alumnos a CSV
- Mensajes de error y exito en frontend

## Endpoints principales

- `POST /api.php?action=login`
- `POST /api.php?action=logout`
- `GET /api.php?action=me`
- `GET /api.php?action=alumnos`
- `POST /api.php?action=alumnos_create`
- `PUT /api.php?action=alumnos_update&id={id}`
- `DELETE /api.php?action=alumnos_delete&id={id}`
- `GET /api.php?action=export`

## Estructura principal

```text
hdico2026/
|-- README.md
|-- Files/
|   |-- DB/
|   |   `-- alumnos.sql
|   |-- descripcion_proyecto_tecnico.txt
|   `-- descripcion_proyecto_usuario.txt
`-- src/
    |-- docker-compose.yml
    |-- GUIA_RAPIDA.md
    |-- README_CONTENEDOR.md
    |-- db/
    |   `-- init/
    |       `-- 01_schema.sql
    |-- backend/
    |   |-- Dockerfile
    |   |-- config/
    |   |   `-- database.php
    |   |-- public/
    |   |   `-- api.php
    |   `-- src/
    |       |-- AlumnoRepository.php
    |       |-- AlumnoValidator.php
    |       |-- ApiException.php
    |       |-- AuthRepository.php
    |       |-- AuthService.php
    |       |-- Database.php
    |       `-- helpers.php
    |-- frontend/
    |   |-- Dockerfile
    |   |-- app.js
    |   |-- config.js
    |   |-- index.html
    |   |-- nginx.conf
    |   `-- styles.css
    `-- index.php
```

## Archivos clave

- [src/docker-compose.yml](/Users/huronmarron/Desktop/clases2026/hdico2026/src/docker-compose.yml)
- [src/backend/public/api.php](/Users/huronmarron/Desktop/clases2026/hdico2026/src/backend/public/api.php)
- [src/backend/src/AuthService.php](/Users/huronmarron/Desktop/clases2026/hdico2026/src/backend/src/AuthService.php)
- [src/backend/src/AlumnoRepository.php](/Users/huronmarron/Desktop/clases2026/hdico2026/src/backend/src/AlumnoRepository.php)
- [src/frontend/index.html](/Users/huronmarron/Desktop/clases2026/hdico2026/src/frontend/index.html)
- [src/frontend/app.js](/Users/huronmarron/Desktop/clases2026/hdico2026/src/frontend/app.js)

## Flujo general de la aplicacion

1. El usuario entra al frontend en `http://localhost:8080`.
2. El frontend consulta la API para verificar si existe sesion activa.
3. El usuario inicia sesion con sus credenciales.
4. El backend valida el usuario contra MySQL y crea la sesion.
5. El frontend permite crear, editar, eliminar y listar alumnos.
6. Cuando se solicita, el backend genera la exportacion CSV.

## Notas importantes

- La exportacion CSV requiere sesion iniciada.
- El backend usa CORS para permitir solicitudes desde `http://localhost:8080`.
- La sesion se conserva con cookies.
- En el repositorio tambien existe una version previa mas simple en PHP tradicional, pero la implementacion principal actual es la version con Docker ubicada en `src/`.
