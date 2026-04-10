# HDICO 2026 - Login + CRUD + Exportacion CSV en 3 Contenedores

Proyecto dividido en servicios independientes:

- `db`: MySQL 8.4
- `backend`: API PHP 8.3 + Apache (`mysqli`)
- `frontend`: HTML/CSS/JS servido con Nginx

## Requisitos

- Docker Desktop activo
- Docker Compose v2

## Levantar el proyecto

```bash
docker compose up --build
```

Servicios disponibles:

- Frontend: `http://localhost:8080`
- Backend API: `http://localhost:8000/api.php`
- MySQL host local: `localhost:3307`

Para detener:

```bash
docker compose down
```

Para reiniciar desde cero (borra datos de MySQL del volumen):

```bash
docker compose down -v
docker compose up --build
```

## Credenciales iniciales

- Usuario: `admin`
- Password: `Admin123*`

Se crean automaticamente desde:

- [01_schema.sql](C:\xampp\htdocs\hdico2026\src\db\init\01_schema.sql)

## Funcionalidad implementada

- Login por sesion (cookies)
- Validacion de sesion (`me`)
- Logout
- CRUD de alumnos
- Exportacion de alumnos a CSV (modulo restaurado)
- Validaciones de datos en backend
- Mensajes de error/success en frontend

## Endpoints API

- `POST /api.php?action=login`
- `POST /api.php?action=logout`
- `GET /api.php?action=me`
- `GET /api.php?action=alumnos`
- `POST /api.php?action=alumnos_create`
- `PUT /api.php?action=alumnos_update&id={id}`
- `DELETE /api.php?action=alumnos_delete&id={id}`
- `GET /api.php?action=export` (descarga CSV)

## Estructura principal

- [docker-compose.yml](C:\xampp\htdocs\hdico2026\src\docker-compose.yml)
- [backend/Dockerfile](C:\xampp\htdocs\hdico2026\src\backend\Dockerfile)
- [frontend/Dockerfile](C:\xampp\htdocs\hdico2026\src\frontend\Dockerfile)
- [backend/public/api.php](C:\xampp\htdocs\hdico2026\src\backend\public\api.php)
- [backend/src/helpers.php](C:\xampp\htdocs\hdico2026\src\backend\src\helpers.php)
- [frontend/index.html](C:\xampp\htdocs\hdico2026\src\frontend\index.html)
- [frontend/app.js](C:\xampp\htdocs\hdico2026\src\frontend\app.js)

## Notas de operacion

- La exportacion CSV requiere sesion iniciada.
- El frontend llama al backend con `credentials: include` para mantener sesion.
- El backend habilita CORS para `http://localhost:8080` (variable `FRONTEND_ORIGIN`).
