# Manual de seguridad y roles — miPedido

## Resumen

La aplicación distingue dos roles en la tabla `usuarios` (`rol`): **administrador** y **empleado**. La autenticación se realiza con **email y contraseña**; las contraseñas se almacenan con **`password_hash()`** (algoritmo por defecto de PHP, actualmente bcrypt) y la comprobación al iniciar sesión usa **`password_verify()`**.

## Administrador (jefe)

El rol **admin** concentra la **gestión completa** del sistema:

- Acceso al **Dashboard**, **Clientes**, **Productos** y **Pedidos** con **creación, edición y eliminación** de registros en catálogos y pedidos.
- **Eliminación** de clientes, productos y pedidos (acciones destructivas que afectan a la integridad del histórico).
- **Módulo de personal**: alta, edición y baja de usuarios (`Usuarios`), con asignación de rol.
- **Configuración** global de la aplicación (pantalla reservada a administradores).

En la interfaz, las entradas de menú **Usuarios** y **Configuración** solo son visibles para administradores. Si un usuario sin permisos intenta abrir esas URLs directamente, la aplicación **redirige a la página de acceso denegado** (`403`).

## Empleado

El rol **empleado** está pensado para el **uso operativo diario** sin comprometer la integridad de los datos maestros ni la gobernanza del sistema:

- **Clientes y productos**: solo **lectura** (listados). No puede dar de alta ni editar fichas; esas acciones quedan para el administrador.
- **Pedidos**: puede **consultar** el listado y **crear nuevos pedidos** (incluida la factura en PDF/vista de impresión según la funcionalidad disponible).
- **No puede eliminar** clientes, productos ni pedidos: los botones de borrado no se muestran y, si se invocara la URL de borrado manualmente, el servidor respondería con **redirección a 403**.
- **No tiene acceso** al módulo de **Usuarios** ni a **Configuración** (mismo criterio: menú oculto y bloqueo en servidor con **403**).

Así se separa la **operación** (empleado: consulta de maestros y **creación de pedidos**) de la **administración, altas/edición de maestros y el borrado** (administrador), reduciendo riesgos de pérdida de datos o cambios no autorizados en cuentas y ajustes del sistema.

## Buenas prácticas

- Usar **contraseñas robustas** en producción y cambiar las credenciales por defecto del entorno de desarrollo.
- No compartir la cuenta de administrador; crear un usuario **empleado** por persona cuando solo necesiten operar pedidos.
- Revisar periódicamente la lista de usuarios y desactivar o eliminar cuentas que ya no se usen (manteniendo siempre **al menos un administrador** activo; la aplicación impide eliminar o degradar al único admin).

## Referencias técnicas

- Comprobación de rol: `app/helpers/auth_helper.php` (`checkRole`, `isAdmin`).
- Rutas: `public/index.php`.
- Gestión de usuarios y contraseñas (`password_hash` / validaciones): `UsuarioController`, modelo `Usuario`.
- Configuración solo admin: `ConfigController`.
- Altas, ediciones y borrados en catálogos: `ClienteController` y `ProductoController` (escritura y `delete` reservados a administrador).
