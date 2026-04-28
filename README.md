# miPedido

## Descripción

ERP en PHP siguiendo el patrón **MVC**, orientado a la **gestión de pedidos** para PYMEs (desarrollo académico / TFG).

## Requisitos

- **Docker Desktop** (para MySQL con Docker Compose).
- **PHP** instalado y disponible en la terminal (`php` en el PATH).

## Instrucciones de levantamiento

1. Ejecutar `docker compose up -d` para la base de datos (**puerto 3307** en el host).
2. Ejecutar `php -S localhost:8000 -t public` para el servidor web (desde la raíz del proyecto).

Abre en el navegador: **http://localhost:8000/** o **http://localhost:8000/index.php**

## Credenciales de prueba

| Campo  | Valor            |
|--------|------------------|
| Email  | `admin@test.com` (coherente con `config/database.sql`) |
| Clave  | `admin123`       |

> Solo para entorno local de desarrollo. No uses estas credenciales en producción.

## Estructura del proyecto

| Carpeta / archivo | Contenido |
|-------------------|-----------|
| `app/` | Lógica MVC: **controladores**, **modelos** y **vistas**; el layout común está en `app/views/layouts/`. |
| `config/` | Conexión a base de datos (`database.php`) y script SQL de esquema (`database.sql`). |
| `public/` | Punto de entrada web (`index.php`); debe ser la raíz del servidor HTTP. |
| `docs/` | Documentación técnica y registro de cambios del proyecto. |
| `docker-compose.yml` | Servicio MySQL para desarrollo local. |

## Base de datos

Credenciales por defecto alineadas con `config/database.php` y Docker: usuario `root`, contraseña `root`, base de datos `mipedido`, host `127.0.0.1` y puerto **3307**.

## Mejoras fase 4 (pedidos realizado, historial, tareas, WhatsApp)

- Esquema actualizado en `config/database.sql` (instalaciones nuevas / volumen Docker limpio).
- Si **ya tenías** la base `mipedido` creada, ejecuta **una vez** en MySQL el script:
  - `config/migrate_fase4_pedidos_realizado_tareas_historial.sql`
- Documentación para la memoria del TFG: [docs/TFG_mejoras_pedidos_tareas_whatsapp.md](docs/TFG_mejoras_pedidos_tareas_whatsapp.md).
- Calendario de tareas (menú **Calendario**): si la base ya existía, ejecuta también **`config/migrate_fase5_calendario_es_personal.sql`** una vez.
- Preferencias de interfaz (**Mi entorno**): si la base ya existía, ejecuta **`config/migrate_fase6_preferencias_ui.sql`** una vez.
- Seguridad (bloqueo login + CSRF): si la base ya existía, ejecuta **`config/migrate_fase7_seguridad_login_csrf_sesion.sql`** una vez.
- Permisos finos por usuario (admin): si la base ya existía, ejecuta **`config/migrate_fase8_permisos_usuarios.sql`** una vez.
- El documento del TFG incluye **fases 4 a 6** y las mejoras de cabecera/avisos: [docs/TFG_mejoras_pedidos_tareas_whatsapp.md](docs/TFG_mejoras_pedidos_tareas_whatsapp.md).

## Checklist de defensa (demo rápida 10-15 min)

Este bloque te sirve para enseñar, en vivo, que la aplicación tiene defensas básicas reales y no solo teoría.

### 1) Login con bloqueo escalado (15 min -> 24 h)

1. Entra a `http://localhost:8000/`.
2. Escribe el email correcto (`admin@test.com`) pero contraseña incorrecta.
3. Repite hasta llegar a 5 fallos.
4. Debe aparecer mensaje de bloqueo temporal de 15 minutos.
5. Tras ese tiempo, vuelve a fallar 5 veces.
6. Debe aparecer bloqueo de 24 horas.

Qué explicar al tribunal:
- El sistema evita intentos infinitos de contraseña.
- El bloqueo es progresivo para frenar fuerza bruta.

### 2) CSRF (formularios protegidos)

1. Entra con sesión iniciada y abre cualquier formulario POST (por ejemplo, crear producto o crear tarea).
2. Envía normal: debe funcionar.
3. Si manipulas el formulario y quitas el campo oculto `_csrf`, debe rechazar la acción.

Qué explicar al tribunal:
- Cada formulario lleva un token único de sesión.
- Si el token no coincide, el servidor corta la petición.

### 3) Sesión y cookies seguras (entorno real HTTPS)

1. Inicia sesión y navega entre módulos.
2. Cierra sesión.
3. Intenta volver atrás y refrescar páginas privadas.
4. Debe pedir login de nuevo.

Qué explicar al tribunal:
- El ID de sesión se regenera al iniciar sesión.
- La cookie de sesión se configura con medidas seguras (`HttpOnly`, `SameSite`, y `Secure` en HTTPS).

### 4) Control de acceso por rol

1. Accede como admin y abre módulos de gestión (usuarios/clientes/productos).
2. Accede con un usuario empleado (si tienes uno) y verifica que no puede hacer acciones de admin.

Qué explicar al tribunal:
- El backend valida permisos, no solo el menú visible.

### 5) Frase final corta para defensa

"La aplicación aplica seguridad por capas: protege el inicio de sesión contra intentos repetidos, valida formularios con token CSRF y endurece la sesión/cookies para uso real en HTTPS."

## Funciones clave explicadas fácil

Esta sección es para entender rápido "qué hace cada parte" sin entrar en código complejo.

### WhatsApp inteligente (sin API de pago)

- En la lista de pedidos, el botón WhatsApp prepara el texto del mensaje.
- Primero intenta abrir la app de WhatsApp del equipo (`whatsapp://`).
- Si no existe app, abre WhatsApp Web (`wa.me`) automáticamente.
- El botón de copiar sigue disponible como plan B.
- El servidor NO envía el mensaje solo: la persona lo revisa y lo confirma en su WhatsApp.

### Login seguro (bloqueo progresivo)

- Si fallas 5 veces seguidas, bloquea 15 minutos.
- Si después vuelves a fallar 5 veces, bloquea 24 horas.
- Tras 24 horas, el ciclo vuelve a empezar en 15 minutos.

### Formularios protegidos (CSRF)

- Cada formulario POST lleva un token oculto.
- Si falta o no coincide, el servidor rechaza la petición.
- Evita envíos "forzados" desde otra web con tu sesión abierta.

### Sesión y cookies

- La sesión se regenera al iniciar sesión correctamente.
- Cookie con `HttpOnly` y `SameSite`.
- En HTTPS real, también usa `Secure`.

### Roles y permisos

- Admin: puede gestionar más módulos (usuarios, altas completas, etc.).
- Empleado: acciones limitadas a su rol.
- El permiso se valida en servidor, no solo en el menú visual.
- Además, desde **Editar usuario**, el admin puede marcar permisos finos (todos o selección).
- Los permisos finos se guardan en tablas `permisos` y `usuario_permisos`.

### Tareas, calendario y avisos

- Tareas asignadas por admin + tareas personales del calendario.
- Campana con avisos de próximos vencimientos y tareas nuevas.
- Estado leído/no leído de avisos guardado en el navegador (localStorage).

### Ventas por mes y productos más vendidos

- El gráfico de ventas ahora se mueve por bloques de 6 meses.
- Puedes retroceder hasta un máximo de 3 años atrás.
- El bloque "Productos más vendidos" se calcula en el mismo periodo visible del gráfico.
- Así, cuando cambias de periodo, cambian tanto las barras de ventas como el ranking de productos.

### Apartado Ventas (nuevo módulo)

- Menú dedicado `Ventas` con histórico mensual (hasta 36 meses).
- Para cada mes se muestra:
  - total vendido,
  - número de pedidos,
  - ticket medio.
- Incluye detalle de pedidos del mes con acceso directo a:
  - factura del pedido,
  - historial del pedido.

### Apartado Facturación (nuevo módulo)

- Menú dedicado `Facturación` con historial de facturas.
- Muestra por factura:
  - código/ID, fecha, cliente, total,
  - estado de factura (`Pendiente` o `Realizada`),
  - estado del pedido y método de pago.
- Filtros rápidos: `Todas`, `Pendientes`, `Realizadas`.
- Acciones:
  - ver/imprimir factura,
  - ver historial del pedido,
  - cerrar como realizada (si está pendiente),
  - editar factura pendiente (abre en pestaña nueva).
  - (sin botón de revertir/reemitir en el listado, para simplificar el flujo operativo).

## Presentación pública + crear cuenta + Google (Auth0)

Se añadió una entrada pública para explicar el producto y permitir alta de usuarios.

### Nuevas rutas públicas

- `index.php` (por defecto ahora va a `page=home`)
- `index.php?page=home` -> pantalla de presentación con secciones animadas.
- `index.php?page=login` -> inicio de sesión.
- `index.php?page=register` -> alta de cuenta local.
- `index.php?page=register_store` -> procesamiento del registro local.
- `index.php?page=auth_google_start` -> inicia flujo Google con Auth0.
- `index.php?page=auth_google_callback` -> callback OAuth de Auth0.

### Qué hace cada pantalla nueva

- `home`: landing con resumen funcional (ventas, pedidos, facturación, tareas, seguridad) y botones superiores de acceso.
- `register`: formulario de alta con nombre, email y contraseña (validaciones básicas).
- Botón Google en registro: redirige a Auth0 para autenticación federada.

### Variables de entorno para Auth0

Configura estas variables antes de probar Google:

- `AUTH0_DOMAIN`
- `AUTH0_CLIENT_ID`
- `AUTH0_CLIENT_SECRET`
- `AUTH0_CONNECTION` (habitual: `google-oauth2`)
- `AUTH0_REDIRECT_URI` (opcional; si no se define, se calcula automáticamente)

Ejemplo de callback local:

- `http://localhost:8000/index.php?page=auth_google_callback`

### Configuración mínima en Auth0 (fuente oficial)

Fuente primaria recomendada:
- [Auth0 Docs](https://auth0.com/docs)

Pasos:
1. Crear aplicación tipo **Regular Web Application**.
2. Activar conexión de Google en Auth0.
3. Añadir URL de callback permitida (`Allowed Callback URLs`).
4. Verificar `Allowed Logout URLs` y `Allowed Web Origins` para tu entorno local.

### Prueba rápida (demo)

1. Abre `http://localhost:8000/` y comprueba la pantalla de presentación.
2. Pulsa **Crear cuenta** y registra un usuario local.
3. Inicia sesión con ese usuario.
4. Cierra sesión y prueba **Registrarme con Google (Auth0)**.
5. Tras callback correcto, debes acabar en `Dashboard` con sesión iniciada.

### Límites actuales (importante para defensa)

- El acceso Google depende de la configuración externa de Auth0.
- Si faltan variables `AUTH0_*` o el callback no coincide, el sistema mostrará error guiado y seguirá disponible el login local.

## Coherencia y seguridad reciente (acciones críticas)

Se aplicaron ajustes para que el comportamiento sea consistente con el estado del pedido/factura y más seguro ante envíos forzados:

- **Pedidos realizados**:
  - ya no muestran botón de eliminar en la lista.
  - el backend también bloquea su eliminación.
- **Eliminar pedido pendiente**:
  - al borrar un pedido no realizado, el stock de productos se repone automáticamente.
- **Acciones críticas en POST + CSRF**:
  - marcar pedido/factura como realizado,
  - eliminar pedido.
  - ambas pasan por validación de token CSRF en servidor.
- **Facturación (UI)**:
  - botón editar abre en pestaña nueva.
  - botón de revertir/reemitir eliminado del listado.
