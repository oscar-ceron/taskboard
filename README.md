# TaskBoard

TaskBoard es una aplicación web para administrar comercios afiliados a una pasarela de pagos y las transacciones asociadas a cada comercio. Los usuarios se registran e inician sesión; cada cuenta trabaja con sus propios comercios.

## Funcionalidades

- Registro, inicio y cierre de sesión.
- Listado de comercios del usuario autenticado y vista de su actividad.
- Registro, edición y eliminación de transacciones.
- Validación de datos y restricción de acceso a recursos de otros usuarios en las rutas web autenticadas.
- API REST JSON protegida con tokens Bearer de Laravel Sanctum.
- Consulta y revocación de los tokens del usuario autenticado.
- Cambio de estado de las transacciones:
  - `Iniciada` puede pasar a `Completada` o `Fallida`.
  - `Fallida` puede volver a `Iniciada`.
  - `Completada` es un estado final y no se puede eliminar.
- Componentes Blade para mostrar el estado de cada transacción y la actividad de un comercio.

## Tecnologías

- PHP 8.3 o superior.
- Laravel 13 (`laravel/framework` `^13.17`).
- Laravel Sanctum para autenticación de la API.
- Composer 2.
- Base de datos SQLite o MySQL/MariaDB.
- Blade para las vistas.

La interfaz principal tiene estilos en sus vistas Blade. Node.js y npm solo son necesarios si se trabaja con los recursos de Vite.

## Instalación y configuración local

Ejecuta los comandos desde la raíz del proyecto.

1. Instala las dependencias de PHP y crea `.env` si todavía no existe:

   ```powershell
   composer install
   if (!(Test-Path .env)) { Copy-Item .env.example .env }
   php artisan key:generate
   ```

2. Configura la conexión a la base de datos en `.env`.

   El archivo `.env.example` usa SQLite. Si eliges SQLite, verifica que exista `database/database.sqlite`; en PowerShell puedes crearlo si falta:

   ```powershell
   if (!(Test-Path database\database.sqlite)) {
       New-Item -ItemType File database\database.sqlite | Out-Null
   }
   ```

   Para MySQL/MariaDB, crea primero una base de datos y configura `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`.

3. Configura el almacenamiento de sesiones:

   ```env
   SESSION_DRIVER=file
   ```

   El `.env.example` del proyecto trae `SESSION_DRIVER=database`, pero no hay una migración para la tabla `sessions`. El driver `file` almacena las sesiones en `storage/framework/sessions` y evita que Laravel intente consultar esa tabla. Si cambiaste `.env` después de almacenar la configuración en caché, ejecuta:

   ```powershell
   php artisan config:clear
   ```

4. Crea las tablas y los datos de demostración:

   ```powershell
   php artisan migrate --seed
   ```

   El seeder crea dos cuentas de demostración y un comercio para cada una:

   | Usuario | Correo | Contraseña local |
   |---|---|---|
   | Ana López | `ana@taskboard.test` | `password123` |
   | Carlos Pérez | `carlos@taskboard.test` | `password123` |

   Estas credenciales son únicamente para desarrollo local. No uses datos ni contraseñas de demostración en producción. El seeder actual no crea transacciones.

5. Inicia el servidor:

   ```powershell
   php artisan serve
   ```

   Abre `http://127.0.0.1:8000`. Sin iniciar sesión, las rutas protegidas redirigen a `/login`.

> **Cuidado:** `php artisan migrate:fresh --seed` elimina todas las tablas y los datos existentes antes de volver a crearlos.

## Rutas principales

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `GET` | `/registro` | Invitado | Formulario de registro |
| `POST` | `/registro` | Invitado | Crea una cuenta e inicia sesión |
| `GET` | `/login` | Invitado | Formulario de acceso |
| `POST` | `/login` | Invitado | Inicia sesión; limitado a 5 intentos por minuto |
| `POST` | `/logout` | Autenticado | Cierra la sesión |
| `GET` | `/` | Autenticado | Redirige al listado de comercios |
| `GET` | `/comercios` | Autenticado | Lista los comercios del usuario |
| `GET` | `/comercios/{comercio}` | Autenticado | Muestra un comercio y sus transacciones |
| `GET` | `/comercios/{comercio}/transacciones/nueva` | Autenticado | Formulario para registrar una transacción |
| `POST` | `/transacciones` | Autenticado | Registra una transacción |
| `GET` | `/transacciones/{transaccion}/editar` | Autenticado | Formulario de edición |
| `PUT` | `/transacciones/{transaccion}` | Autenticado | Actualiza una transacción |
| `DELETE` | `/transacciones/{transaccion}` | Autenticado | Elimina una transacción no completada |
| `PATCH` | `/transacciones/{transaccion}/mover/{estado}` | Autenticado | Cambia el estado según las transiciones permitidas |

También hay rutas de demostración para un formulario en `/practica/formulario-demo` y `/practica/enviar`.

## API REST

Las rutas de API están definidas en `routes/api.php` y Laravel les agrega el prefijo `/api`. El endpoint de inicio de sesión emite tokens Bearer con Sanctum; las demás rutas están protegidas con `auth:sanctum` y requieren autenticación. Sin un token válido responden `401 Unauthorized`.

### Obtener y usar un token

Envía las credenciales y un nombre de dispositivo a `POST /api/login`:

```http
POST http://127.0.0.1:8000/api/login
Accept: application/json
Content-Type: application/json

{
  "email": "ana@taskboard.test",
  "password": "password123",
  "dispositivo": "Postman en mi PC"
}
```

La respuesta `201 Created` contiene el token en texto plano una sola vez. En Postman, copia su valor en **Authorization → Bearer Token** para las siguientes peticiones. No guardes el token real en la colección que compartirás o subirás al repositorio. El inicio de sesión está limitado a 5 intentos por minuto.

| Método | Ruta | Comportamiento |
|---|---|---|
| `POST` | `/api/login` | Público; valida credenciales y `dispositivo`, y emite un token (`201 Created`) |
| `GET` | `/api/yo` | Devuelve los datos básicos del usuario autenticado |
| `POST` | `/api/logout` | Revoca solo el token usado en la petición; responde `204 No Content` |
| `GET` | `/api/tokens` | Lista los tokens propios con `id`, `name`, `last_used_at` y `created_at`; no devuelve el hash |
| `DELETE` | `/api/tokens/{id}` | Revoca un token propio; responde `204`, o `404` si no pertenece al usuario |
| `GET` | `/api/comercios` | Lista solo los comercios del usuario; no incluye las transacciones en esta lista |
| `GET` | `/api/comercios/{comercio}` | Devuelve un comercio propio y sus transacciones; responde `404` si no le pertenece |
| `GET` | `/api/transacciones` | Lista las transacciones del usuario; admite el filtro opcional `?estado=Completada` |
| `POST` | `/api/transacciones` | Crea una transacción para un comercio propio y responde `201 Created` |
| `GET` | `/api/transacciones/{transaccion}` | Devuelve una transacción propia; responde `404` si no existe o no le pertenece |
| `DELETE` | `/api/transacciones/{transaccion}` | Elimina una transacción propia y responde `204 No Content` |
| `PUT`, `PATCH` | `/api/transacciones/{transaccion}` | La ruta está registrada, pero la acción `update()` todavía no está implementada |

El filtro de `GET /api/transacciones` compara el parámetro `estado` con el estado guardado en las transacciones del usuario autenticado. Los estados de la aplicación son `Iniciada`, `Completada` y `Fallida`. Sin parámetro devuelve todas las transacciones propias; si el valor no tiene coincidencias, devuelve `200` con `"data": []`.

### Colección de Postman

La colección exportada de Postman está en [`postman/TaskBoard API.postman_collection.json`](./postman/TaskBoard%20API.postman_collection.json). Usa el formato Postman Collection v2.1 e incluye peticiones para listar comercios y transacciones, consultar detalles, filtrar por estado, crear transacciones válidas e inválidas y eliminar una transacción. Tras importar la colección, configura un Bearer Token válido para sus peticiones protegidas; el archivo compartido no debe contener tokens reales.

Algunos ejemplos usan IDs fijos (`comercio_id: 1`, transacción `2` y eliminación de transacción `7`). Esos registros no están garantizados en una instalación limpia; comprueba que existan y, para DELETE, verifica cuidadosamente el ID antes de enviar la petición. El seeder actual crea comercios, pero no transacciones.

Ejemplo de creación:

```http
POST http://127.0.0.1:8000/api/transacciones
Accept: application/json
Content-Type: application/json

{
  "comercio_id": 1,
  "cliente_nombre": "Luis Mena",
  "monto": 25.50
}
```

La validación requiere un comercio existente, un nombre de cliente y un monto positivo de hasta `10000`. El estado inicial se establece en `Iniciada` por el valor predeterminado de la base de datos. Aunque se envíe `estado` en el JSON, actualmente no se incluye en las reglas del Form Request y no se guarda desde esta petición.

Para obtener errores de validación como `422 Unprocessable Content` en JSON, incluye `Accept: application/json`. `Content-Type: application/json` indica que el cuerpo de la petición está en JSON.

> **Tokens de dispositivo:** cada llamada válida a `/api/login` crea un token nuevo con el nombre enviado en `dispositivo`. `/api/tokens` muestra solo los metadatos permitidos del usuario autenticado. `last_used_at` puede ser `null` mientras el token no se haya usado en una ruta protegida. El cierre de sesión elimina el token de la petición actual; la revocación de otro dispositivo se hace con `DELETE /api/tokens/{id}` y solo dentro de la relación del usuario autenticado.

## Modelo de datos

```text
User 1 ──< Comercio 1 ──< Transaccion 1 ──< EventoTransaccion
```

- **User:** nombre, correo electrónico único y contraseña con hash.
- **Comercio:** usuario propietario, nombre, rubro y teléfono opcional.
- **Transaccion:** comercio, monto, nombre del cliente y estado (`Iniciada`, `Completada` o `Fallida`).
- **EventoTransaccion:** tipo y detalle opcional asociado a una transacción. El modelo y la tabla están disponibles para la bitácora; los controladores actuales no crean eventos automáticamente.
- **Personal access token:** Sanctum guarda los hashes de los tokens en `personal_access_tokens`; la API no devuelve esa columna al listar dispositivos.

Las migraciones de estas tablas están en `database/migrations`. Las migraciones de `cache` y `jobs` son parte del esqueleto de Laravel.

## Estructura del proyecto

```text
app/
  Http/
    Controllers/          Controladores web
      Api/                 Endpoints REST de la API
    Requests/              Validación de registro y transacciones
    Resources/             Recursos JSON de comercios y transacciones
  Models/                  User, Comercio, Transaccion y EventoTransaccion
database/
  migrations/             Esquema de la base de datos
  seeders/                Datos de demostración
resources/views/
  auth/                   Formularios de registro e inicio de sesión
  comercios/              Listado y detalle de comercios
  transacciones/          Formularios de creación y edición
  components/             Badges Blade
  layouts/                Layout principal
routes/web.php            Rutas web y middleware
routes/api.php            Rutas REST con prefijo /api
```

## Desarrollo

- Ejecuta las pruebas automatizadas con `php artisan test`.
- Lista las rutas web con `php artisan route:list` y las rutas API con `php artisan route:list --path=api`.
- Para compilar los recursos de Vite, instala las dependencias de Node con `npm install` y ejecuta `npm run build`.

## Solución de problemas

| Error o síntoma | Causa probable | Qué revisar |
|---|---|---|
| `Table '...sessions' doesn't exist` | `SESSION_DRIVER=database` y no existe la tabla `sessions`. | Configura `SESSION_DRIVER=file` o agrega y ejecuta una migración para la tabla de sesiones. |
| `Unknown database` o error de conexión | La base configurada en `.env` no existe o las credenciales no coinciden. | Revisa la configuración `DB_*` y que el servicio de base de datos esté activo. |
| `No application encryption key has been specified` | Falta generar la clave de la aplicación. | Ejecuta `php artisan key:generate`. |
| Los cambios de `.env` no se reflejan | Laravel está usando configuración en caché. | Ejecuta `php artisan config:clear`. |
| Un invitado es enviado a `/login` | Las rutas principales requieren autenticación. | Registra una cuenta o inicia sesión. |
