# Despliegue (nivel estudiante)

Este documento explica, paso a paso y con lenguaje simple, como publicar `miPedido` en internet usando Railway.

---

## 1) Objetivo del despliegue

El objetivo es que la aplicacion deje de funcionar solo en local (`localhost`) y pase a estar disponible por una URL publica.

En este proyecto, el despliegue incluye:

- Un servicio web para ejecutar PHP (tu app MVC).
- Un servicio MySQL para guardar datos.
- Variables de entorno para no hardcodear credenciales.
- (Opcional) Login con Google/Auth0 funcionando en dominio real.

---

## 2) Que cambia respecto al entorno local

En local usas:

- `docker-compose.yml` para MySQL
- `php -S localhost:8000 -t public` para el servidor web

En Railway cambia el modelo:

- Railway ejecuta la app en un **Web Service**.
- Railway ejecuta MySQL en un **Database Service**.
- Las credenciales y host de DB se pasan por variables (`DB_HOST`, `DB_USER`, etc.).

Idea clave estudiante:  
**mismo codigo de app, distinta infraestructura**.

---

## 3) Requisitos previos

Antes de desplegar, deja esto listo:

1. Repositorio en GitHub actualizado.
2. Proyecto funcionando en local sin errores graves.
3. Cuenta en Railway.
4. (Opcional) Cuenta Auth0 si usaras Google login.
5. (Opcional) Dominio propio si no quieres usar `up.railway.app`.

---

## 4) Arquitectura final en Railway

Tu proyecto queda dividido en dos servicios principales:

### 4.1 Web Service (PHP)

Ejecuta:

- rutas de `public/index.php`
- controladores/modelos/vistas
- assets (`public/js`, `public/css`, `public/img`)

### 4.2 MySQL Service

Guarda:

- usuarios
- clientes
- productos
- pedidos
- tareas
- y el resto del esquema de `config/database.sql`

Ventaja: separar web y DB hace la app mas mantenible y mas realista para produccion.

---

## 5) Paso a paso detallado

## Paso 1: subir tu codigo a GitHub

1. Verifica que no subes secretos:
   - `.env`
   - claves privadas
   - dumps sensibles
2. Revisa `.gitignore`.
3. Haz push de la rama principal.

## Paso 2: crear proyecto en Railway

1. En Railway: `New Project`.
2. Selecciona `Deploy from GitHub Repo`.
3. Elige tu repositorio `miPedido`.

## Paso 3: crear la base de datos MySQL

1. En el canvas del proyecto: `+ New`.
2. Elige `MySQL` (template oficial).
3. Railway creara variables internas:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`

## Paso 4: conectar el Web Service con MySQL

En el servicio web, en `Variables`, crea:

- `DB_HOST = ${{MySQL.MYSQLHOST}}`
- `DB_PORT = ${{MySQL.MYSQLPORT}}`
- `DB_NAME = ${{MySQL.MYSQLDATABASE}}`
- `DB_USER = ${{MySQL.MYSQLUSER}}`
- `DB_PASSWORD = ${{MySQL.MYSQLPASSWORD}}`

Esto funciona porque `config/database.php` ya lee esas variables con `getenv`.

## Paso 5: configurar build/start del servicio web

Para este proyecto PHP MVC, lo mas estable es usar Dockerfile.

Objetivo tecnico:

- tener PHP con `pdo` y `pdo_mysql`,
- apuntar servidor web a carpeta `public/`,
- desplegar de forma repetible.

## Paso 6: inicializar el esquema SQL

Conecta al MySQL de Railway y ejecuta:

- instalacion limpia: `config/database.sql`
- si migras una DB antigua: scripts `migrate_fase4` a `migrate_fase12` en orden.

Importante:

- si el entorno no necesita usuarios MySQL locales extra, evita ejecutar bloques no necesarios de `CREATE USER` en produccion.

## Paso 7: configurar Auth0 (si usas Google)

Cuando ya tengas URL publica, actualiza en Auth0:

- Allowed Callback URLs
- Allowed Logout URLs
- Allowed Web Origins

Variables del web service:

- `AUTH0_DOMAIN`
- `AUTH0_CLIENT_ID`
- `AUTH0_CLIENT_SECRET`
- `AUTH0_CONNECTION` (normalmente `google-oauth2`)
- `AUTH0_REDIRECT_URI` (URL real de callback)

Ejemplo:

- `https://tu-dominio/index.php?page=auth_google_callback`

## Paso 8: activar dominio

1. Servicio web -> `Settings` -> `Domains`.
2. Puedes usar:
   - dominio Railway (`*.up.railway.app`)
   - dominio propio.
3. Si usas dominio propio:
   - configura DNS con los registros que Railway indique,
   - espera propagacion.

## Paso 9: validacion final

Prueba en URL publica:

1. login local,
2. dashboard,
3. CRUD principal,
4. modulos (pedidos, ventas, facturacion, tareas),
5. Auth0/Google (si activado),
6. revisar logs si algo falla.

---

## 6) Variables de entorno (resumen)

## Base de datos

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASSWORD`

## Auth0 (opcionales)

- `AUTH0_DOMAIN`
- `AUTH0_CLIENT_ID`
- `AUTH0_CLIENT_SECRET`
- `AUTH0_CONNECTION`
- `AUTH0_REDIRECT_URI`

---

## 7) Problemas comunes y solucion

## Error de conexion MySQL

Posibles causas:

- variable mal escrita,
- servicio MySQL caido,
- DB no inicializada.

Solucion:

- revisar `DB_*` en Railway,
- revisar estado del servicio MySQL,
- ejecutar schema SQL.

## Error Auth0 callback

Posibles causas:

- callback no coincide exactamente,
- falta `https`,
- dominio no registrado en Auth0.

Solucion:

- copiar URL exacta entre Railway y Auth0.

## Error 500 en web

Solucion:

- abrir logs de Railway del Web Service,
- detectar variable faltante o extension PHP no cargada.

---

## 8) Checklist corto pre-defensa

Antes de presentar:

1. App abre por URL publica.
2. Login correcto.
3. Dashboard sin errores.
4. Modulos clave funcionando.
5. DB persistente tras redeploy.
6. Auth0 funcional (si aplica).

---

## 9) Resultado esperado

Al finalizar este proceso, `miPedido` queda:

- accesible por internet,
- conectado a MySQL en la nube,
- con configuracion separada por variables,
- listo para demostracion de TFG en entorno real.

---

## 10) Fuentes oficiales consultadas

- Railway Docs - Dockerfiles: https://docs.railway.app/deploy/dockerfiles
- Railway Docs - Variables: https://docs.railway.app/develop/variables
- Railway Docs - MySQL: https://docs.railway.com/databases/mysql
- Auth0 Docs: https://auth0.com/docs
