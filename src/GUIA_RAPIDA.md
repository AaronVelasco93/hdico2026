# Guia Rapida - HDICO 2026

## 1) Requisitos

- Docker Desktop encendido
- Docker Compose v2

## 2) Levantar el proyecto

```bash
docker compose up --build
```

## 3) URLs

- Frontend: `http://localhost:8080`
- Backend API: `http://localhost:8000/api.php`
- MySQL local: `localhost:3307`

## 4) Login inicial

- Usuario: `admin`
- Password: `Admin123*`

## 5) Funciones principales

- Iniciar sesion / cerrar sesion
- Registrar alumno
- Modificar alumno
- Eliminar alumno
- Exportar CSV

## 6) Comandos utiles

Detener:

```bash
docker compose down
```

Reiniciar limpio (borra volumen de BD):

```bash
docker compose down -v
docker compose up --build
```

## 7) Si algo falla

- Verifica que Docker Desktop este abierto.
- Revisa contenedores:

```bash
docker compose ps
```

- Ver logs:

```bash
docker compose logs -f
```
