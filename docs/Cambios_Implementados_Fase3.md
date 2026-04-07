# Cambios implementados — Fase 3

Seguridad basada en **roles (RBAC)**, dashboard con **Chart.js**, módulo de **pedidos** con alta inteligente y **factura imprimible**, configuración de BD por **variables de entorno** y guía **`DESPLIEGUE.md`**.

## Archivos nuevos

| Ruta | Descripción |
|------|-------------|
| `app/helpers/auth_helper.php` | `authEnsureSession()`, `checkRole($rol)`, `isAdmin()`. |
| `app/views/403.php` | Vista «Acceso denegado» con layout. |
| `app/controllers/DashboardController.php` | KPIs + datos demo para gráficos. |
| `app/controllers/PedidoController.php` | CRUD parcial de pedidos, factura. |
| `app/controllers/UsuarioController.php` | Listado de usuarios (solo admin). |
| `app/controllers/ConfigController.php` | Pantalla de configuración (solo admin). |
| `app/models/Usuario.php` | Listado de usuarios. |
| `app/views/pedidos/*` | Listado, alta y vista de factura para impresión. |
| `app/views/usuarios/index.php` | Tabla de usuarios. |
| `app/views/config/index.php` | Texto orientativo para admins. |
| `config/migrate_fase3.sql` | Añade `metodo_pago` a `pedidos` en bases ya existentes. |
| `docs/DESPLIEGUE.md` | Despliegue en hosting e importación SQL. |

## Reglas de negocio (roles)

- **Administrador:** puede eliminar registros (clientes, productos, pedidos) y acceder a **Usuarios** y **Configuración**.
- **Empleado:** no ve enlaces de borrado; si fuerza la URL de borrado, `checkRole('admin')` redirige a **403**.

## Base de datos

- `database.sql` incluye `metodo_pago` en `pedidos`.
- Instalaciones antiguas: ejecutar `config/migrate_fase3.sql` una vez.

## Rutas nuevas relevantes

`403`, `pedidos`, `pedidos_create`, `pedidos_store`, `pedidos_delete`, `pedido_factura`, `usuarios`, `config`.
