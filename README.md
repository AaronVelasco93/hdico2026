# Registro de Alumnos (PHP + MySQLi)

## Descripcion general
Este proyecto es una aplicacion web sencilla para administrar alumnos.
Permite:
- Registrar alumnos
- Listar alumnos
- Editar alumnos
- Eliminar alumnos
- Exportar alumnos a CSV

La app esta hecha en PHP con arquitectura simple por capas y conexion a MySQL usando `mysqli`.

## Estructura del proyecto
```text
hdico2026/
|-- src/
|   |-- index.php
|   |-- process.php
|   |-- config/
|   |   `-- database.php
|   `-- src/
|       |-- Database.php
|       |-- AlumnoRepository.php
|       |-- AlumnoValidator.php
|       `-- helpers.php
|-- Files/
|   `-- DB/
|       `-- alumnos.sql
`-- README.md
```

## Logica del programa

### 1) Carga principal (`src/index.php`)
- Carga utilidades y clases necesarias (`helpers`, `Database`, `AlumnoRepository`).
- Crea la conexion y el repositorio.
- Si llega `?action=export`, genera y descarga el archivo CSV con todos los alumnos.
- Recupera estado temporal del formulario (datos previos, errores y mensajes flash).
- Si llega `?edit=<id>`, busca al alumno y precarga el formulario para actualizar.
- Consulta todos los registros para mostrarlos en la tabla.
- Renderiza:
  - Formulario (alta o edicion, segun el contexto)
  - Tabla de alumnos
  - Boton de exportacion
  - Mensajes de exito/error

### 2) Procesamiento de acciones (`src/process.php`)
- Solo acepta peticiones `POST`.
- Valida token CSRF.
- Identifica accion enviada por formulario:
  - `create`: crea alumno nuevo
  - `update`: actualiza alumno existente
  - `delete`: elimina alumno por id
- Para `create` y `update`:
  - Valida datos con `AlumnoValidator`.
  - Si hay errores, guarda errores/datos en sesion y redirige al formulario.
  - Si todo es valido, ejecuta operacion en `AlumnoRepository`.
- Maneja excepciones:
  - Detecta duplicados (error tipo `duplicate`)
  - Muestra mensaje amigable al usuario
- Redirige siempre a `index.php` para evitar reenvio del formulario.

### 3) Capa de datos (`src/src/AlumnoRepository.php`)
`AlumnoRepository` centraliza todo el acceso a tabla `registro`.

Metodos principales:
- `all()`: obtiene todos los alumnos ordenados por `id_alumno DESC`.
- `findById($id)`: obtiene un alumno por id.
- `create($alumno)`: inserta nuevo registro.
- `update($id, $alumno)`: actualiza registro existente.
- `delete($id)`: elimina registro.

Todas las operaciones usan sentencias preparadas para mayor seguridad.

### 4) Conexion a base de datos (`src/src/Database.php`)
- Lee configuracion desde `src/config/database.php`.
- Crea una sola instancia reutilizable de `mysqli`.
- Configura charset `utf8mb4`.
- Si falla la conexion, lanza excepcion controlada.

### 5) Validacion (`src/src/AlumnoValidator.php`)
Valida y normaliza entradas del formulario:
- `primer_apellido`: obligatorio, max 255
- `segundo_apellido`: opcional, max 255
- `nombres`: obligatorio, max 255
- `no_cuenta`: 8 a 9 digitos
- `correo`: obligatorio, formato valido, max 255
- `contacto`: exactamente 10 digitos

Devuelve:
- `data`: datos limpios
- `errors`: errores por campo

### 6) Helpers (`src/src/helpers.php`)
Funciones utilitarias:
- `e()`: escapa salida HTML
- `redirect()`: redirige y termina ejecucion
- `setFlash()` / `pullFlash()`: mensajes temporales en sesion
- `csrfToken()` / `isValidCsrfToken()`: proteccion CSRF
- `rememberForm()` / `pullFormState()`: conserva datos y errores entre redirecciones

## Base de datos
Archivo SQL: `Files/DB/alumnos.sql`

Incluye:
- Creacion de base `registro_alumnos`
- Uso de la base
- Recreacion de tabla `registro`
- Campos necesarios para el CRUD

## Configuracion rapida
Editar `src/config/database.php` con tus credenciales:
- `host`
- `port`
- `dbname`
- `username`
- `password`

Tambien puedes usar variables de entorno:
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

## Flujo resumido de una operacion
1. Usuario envia formulario en `index.php`.
2. `process.php` recibe POST y valida CSRF.
3. `AlumnoValidator` valida datos.
4. `AlumnoRepository` ejecuta operacion en MySQL.
5. Se guarda mensaje flash.
6. Redireccion a `index.php`.
7. `index.php` muestra resultado y tabla actualizada.

## Requisitos
- XAMPP o entorno con PHP + MySQL
- Extension `mysqli` habilitada
- Sesiones habilitadas en PHP

