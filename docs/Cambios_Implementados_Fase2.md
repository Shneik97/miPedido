# Cambios implementados — Fase 2 (Oleada 2)

Transformación visual unificada bajo un layout maestro (Bootstrap 5 + Font Awesome), refactor de vistas existentes, KPIs en el dashboard, módulo CRUD de **productos** alineado con la tabla `productos` del esquema SQL, y capa ligera de interactividad en JavaScript.

---

## 1. Objetivos de la fase

1. **Layout maestro** reutilizable con barra lateral fija (degradado oscuro), barra superior con marca y usuario, área central en gris claro y contenidos en tarjetas blancas.
2. **Refactor** de `dashboard.php`, `login.php` y vistas de `clientes/` para usar dicho layout sin duplicar cabeceras HTML.
3. **Dashboard** con tarjetas de resumen (KPIs) alimentadas por datos reales de la base de datos donde existan tablas.
4. **Tablas** de clientes con apariencia actualizada, filas alternas (Bootstrap `table-striped`) y acciones con iconos.
5. **Nuevo módulo Productos**: modelo, controlador, vistas y rutas en el front controller.
6. **Interactividad**: script `public/js/main.js` (colapso de sidebar, confirmación de borrado).
7. **Correcciones de UX**: mensajes de error de login dentro del layout; validación de ID en edición de cliente/producto.

---

## 2. Nueva arquitectura visual

### 2.1 Patrón de vistas

- Cada vista asigna variables de contexto (`$pageTitle`, `$currentNav`, opcionalmente `$showSidebar`) y construye el HTML principal en una variable **`$content`** mediante **salida diferida** (`ob_start()` / `ob_get_clean()`).
- A continuación incluye **`app/views/layouts/main.php`**, que pinta el documento HTML5 completo, CDN de **Bootstrap 5.3.2** y **Font Awesome 6.5**, y el pie de página con scripts.

### 2.2 Layout `app/views/layouts/main.php`

| Elemento | Descripción |
|----------|-------------|
| **Sidebar** | Fijo a la izquierda, degradado (`#1e293b` → `#020617`), enlaces a Dashboard, Clientes, Productos y Cerrar sesión con iconos. Estado activo según `$currentNav`. |
| **Navbar superior** | Muestra el texto **«ERP miPedido»**, botón para **colapsar** el sidebar (escritorio) y otro para **abrir/cerrar** en móvil, y el **nombre del usuario** con icono de perfil (`fa-circle-user`) cuando hay sesión. |
| **Área central** | Fondo gris claro (`--content-bg: #f1f5f9`); el contenido de cada pantalla va en **cards** con clase `page-card`. |
| **Login** | `$showSidebar = false`: sin menú lateral; cabecera compacta con texto «Acceso al sistema»; formulario centrado en tarjeta. |

### 2.3 Estilos

- Estilos de layout incrustados en `<style>` dentro del layout para no añadir dependencias de build en esta fase.
- Breakpoint: en pantallas pequeñas el sidebar se oculta fuera de pantalla y se despliega con la clase `mobile-open`.

---

## 3. Archivos nuevos

| Ruta | Rol |
|------|-----|
| `app/views/layouts/main.php` | Layout maestro descrito arriba. |
| `public/js/main.js` | Colapsar `#appSidebar` con `#sidebarToggle`; menú móvil con `#sidebarMobileToggle`; `click` en enlaces con clase `js-confirm-delete` para `confirm()` antes de navegar al borrado. |
| `app/models/Producto.php` | CRUD PDO sobre `productos` (`getAll`, `count`, `create`, `getById`, `update`, `delete`). |
| `app/models/Pedido.php` | Consulta `COUNT(*)` de pedidos con `estado = 'pendiente'` para KPI del dashboard. |
| `app/controllers/ProductoController.php` | CRUD con `requireAuth()`; normalización de `precio`/`stock` desde `$_POST`. |
| `app/views/productos/index.php`, `create.php`, `edit.php` | Listado y formularios con el nuevo diseño. |
| `docs/Cambios_Implementados_Fase2.md` | Este documento. |

---

## 4. Archivos modificados

| Archivo | Cambios principales |
|---------|---------------------|
| `app/models/Cliente.php` | Método `count(): int` para el KPI «Total clientes». |
| `app/views/dashboard.php` | Carga `Cliente`, `Pedido`, `Producto`; muestra tres KPIs (clientes, pedidos pendientes, productos en catálogo) y texto de bienvenida dentro de cards; usa el layout. |
| `app/views/login.php` | Formulario en card; lectura de `$_SESSION['login_error']` para alertas; layout sin sidebar. |
| `app/views/clientes/*.php` | Eliminación de HTML duplicado; tablas `table-hover table-striped`; botones de acción con iconos lápiz/basura y clase `js-confirm-delete` en borrado. |
| `app/controllers/LoginController.php` | Sustitución de `echo` por `$_SESSION['login_error']` para integrar errores en la vista. |
| `app/controllers/ClienteController.php` | En `edit()`, redirección si el ID no existe. |
| `public/index.php` | `require` de `ProductoController`; casos `productos`, `productos_create`, `productos_store`, `productos_edit`, `productos_update`, `productos_delete`. |

---

## 5. Rutas nuevas (parámetro `page`)

- `productos` — listado.
- `productos_create` / `productos_store` — alta.
- `productos_edit` / `productos_update` — edición (`id` por GET/POST).
- `productos_delete` — borrado (`id` por GET).

---

## 6. KPIs del dashboard (origen de datos)

| KPI | Origen |
|-----|--------|
| Total clientes | `Cliente::count()` → tabla `clientes`. |
| Pedidos pendientes | `Pedido::countPendientes()` → tabla `pedidos`, filtro `estado = 'pendiente'`. |
| Productos en catálogo | `Producto::count()` → tabla `productos`. |

---

## 7. Justificación académica breve

- **Separación de responsabilidades:** el layout concentra la identidad visual y la navegación; las vistas solo aportan el fragmento de negocio (`$content`), coherente con MVC.
- **Seguridad:** el módulo productos replica el patrón de `ClienteController` (`requireAuth`). Los enlaces de borrado delegan la confirmación al JS para no depender solo de `onclick` inline.
- **Accesibilidad / UX:** navbar con `aria-label` en botones de menú; tablas responsivas con `table-responsive`.
- **Escalabilidad:** `$currentNav` permite marcar el ítem activo del menú al añadir nuevos módulos (p. ej. pedidos en una fase futura).

---

## 8. Cómo probar

1. Levantar MySQL (`docker compose up -d`) y el servidor PHP (`php -S localhost:8000 -t public`).
2. Iniciar sesión; comprobar Dashboard, Clientes y **Productos** desde el sidebar.
3. Crear/editar/borrar un producto y verificar el KPI de productos y el listado.
4. Probar colapso del sidebar y el diálogo de confirmación al eliminar.

---

*Fase 2 — interfaz profesional compartida y primer módulo de catálogo (productos).*
