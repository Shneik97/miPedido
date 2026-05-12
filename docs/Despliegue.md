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

## 3.1) Que ya esta preparado en el codigo y que haces tu

**En el repositorio (listo para usar):**

- `Dockerfile` para construir y arrancar la app en Railway (PHP + `pdo_mysql`, raiz `public/`, puerto `PORT`).
- `.dockerignore` para imagenes mas ligeras y sin mezclar tu `vendor/` local.
- `.env.example` como guia de variables (copia local a `.env` si quieres; en Railway se configuran en el panel).
- `config/database.php` leyendo `DB_*` desde el entorno.
- Deteccion de HTTPS detras de proxy (`X-Forwarded-Proto`) para cookies de sesion en despliegues con TLS.

**Solo tu puedes / debes hacerlo (Railway y cuentas):**

- Crear proyecto en Railway, conectar GitHub, elegir rama.
- Crear el servicio **MySQL** y enlazar variables al servicio web (ver paso 4).
- Ejecutar `config/database.sql` contra la base de Railway.
- (Opcional) Auth0: aplicacion, URLs de callback y variables `AUTH0_*`.
- (Opcional) Dominio y DNS.
- Revisar **logs** del despliegue si algo falla.

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

En el servicio web, en `Variables`, crea referencias al plugin MySQL (sintaxis oficial de Railway). **Sustituye `MySQL`** por el nombre exacto de tu servicio de base de datos en el canvas si lo renombraste (por ejemplo `MySQL`, `mysql`, etc.):

- `DB_HOST` = `${{MySQL.MYSQLHOST}}`
- `DB_PORT` = `${{MySQL.MYSQLPORT}}`
- `DB_NAME` = `${{MySQL.MYSQLDATABASE}}`
- `DB_USER` = `${{MySQL.MYSQLUSER}}`
- `DB_PASSWORD` = `${{MySQL.MYSQLPASSWORD}}`

Esto funciona porque `config/database.php` ya lee esas variables con `getenv`.

Documentacion: [MySQL en Railway](https://docs.railway.com/databases/mysql) y [variables referenciadas](https://docs.railway.app/develop/variables).

## Paso 5: build y arranque del servicio web (Dockerfile)

En la raiz del repo hay un **`Dockerfile`** pensado para Railway:

- PHP **8.2** con extension **`pdo_mysql`**.
- Arranque con el servidor integrado de PHP (`php -S`) en **`0.0.0.0:${PORT}`** con raiz en **`public/`** (Railway inyecta `PORT` automaticamente).
- Adecuado para **demostracion / TFG**; para trafico muy alto convendria FPM + nginx u otro stack.

**Que no tienes que configurar en Railway** si usas solo este repo:

- Comando de inicio manual (lo define el `Dockerfile`).
- Nixpacks especial: si existe `Dockerfile`, Railway lo usa para construir la imagen (ver [Dockerfiles](https://docs.railway.app/deploy/dockerfiles)).

**`Procfile`** (Heroku con `heroku-php-apache2`) **no** es lo que ejecuta Railway en este flujo; sirve si despliegas en Heroku. En Railway cuenta el `Dockerfile`.

## Paso 6: inicializar el esquema SQL

Conecta al MySQL de Railway (cliente MySQL, pestaña **Data** o tunel) y ejecuta:

- **Instalacion limpia**: el script `config/database.sql` (esquema completo).

Si en el pasado usabas scripts `migrate_fase*.sql` y ya no estan en tu rama, puedes recuperarlos del historial de git si los necesitas; para un despliegue nuevo basta con `database.sql`.

Importante:

- En produccion evita ejecutar bloques de `CREATE USER` / permisos pensados solo para tu PC si el proveedor ya te da usuario y base creados (Railway MySQL suele venir listo).

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

## 10) Probar la imagen Docker en local (opcional)

Con Docker Desktop encendido, desde la raiz del proyecto:

```bash
docker build -t mipedido-railway .
docker run --rm -p 8080:8080 -e PORT=8080 -e DB_HOST=host.docker.internal -e DB_PORT=3307 -e DB_NAME=mipedido -e DB_USER=user_mipedido -e DB_PASSWORD=12345 mipedido-railway
```

Ajusta `DB_*` a tu MySQL local. Abre `http://localhost:8080/`. Asi validas el contenedor antes de subir a Railway.

---

## 11) Fuentes oficiales consultadas

- Railway Docs - Dockerfiles: https://docs.railway.app/deploy/dockerfiles
- Railway Docs - Variables: https://docs.railway.app/develop/variables
- Railway Docs - MySQL: https://docs.railway.com/databases/mysql
- Auth0 Docs: https://auth0.com/docs
