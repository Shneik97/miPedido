# Descripción técnica — Proyecto «miPedido» (ERP)

## 1. Propósito

Aplicación web orientada a la gestión de pedidos para PYMEs, desarrollada como Trabajo de Fin de Grado. En el estado actual del repositorio, la parte implementada en código cubre **autenticación basada en sesión**, **hash seguro de contraseñas** (`password_hash` / `password_verify`) y **CRUD de clientes** con acceso restringido a usuarios autenticados. El modelo de datos prevé además **usuarios con rol**, **productos**, **pedidos** y **detalle de pedidos**.

## 2. Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Lenguaje | PHP |
| Base de datos | MySQL |
| Acceso a datos | PDO (consultas preparadas) |
| Frontend (vistas de clientes) | HTML5, Bootstrap 5.3 (CDN) |
| Contenedor (opcional) | Docker Compose — MySQL 8, puerto host `3307` → contenedor `3306` |

## 3. Arquitectura

Organización **MVC**:

- **Modelo** (`app/models/`): acceso a datos (clase `Cliente`).
- **Controlador** (`app/controllers/`): `LoginController`, `ClienteController`.
- **Vista** (`app/views/`): `login.php`, `dashboard.php`, vistas bajo `clientes/`.
- **Configuración** (`config/`): clase `Database` y script `database.sql`.
- **Front controller** (`public/index.php`): despacho por parámetro `page` vía `$_GET['page']`.

## 4. Estructura de directorios relevante

```
miPedido/
├── config/
│   ├── database.php      # Clase de conexión PDO
│   └── database.sql      # Esquema relacional
├── docs/                 # Documentación del proyecto
├── public/
│   └── index.php         # Punto de entrada y rutas
├── app/
│   ├── controllers/
│   ├── models/
│   └── views/
├── docker-compose.yml    # Orquestación local de MySQL (opcional)
└── hash_updater.php      # Script puntual de migración de contraseñas (eliminar tras uso)
```

## 5. Configuración de base de datos

La clase `Database` define host `127.0.0.1`, puerto `3307`, base de datos `mipedido`, usuario y contraseña. La conexión usa DSN MySQL y `PDO::ATTR_ERRMODE` en modo excepción. Las credenciales deben alinearse con el servicio MySQL (local o Docker).

## 6. Modelo de datos (resumen)

Definido en `config/database.sql`:

- **usuarios**: `id`, `nombre`, `email` (único), `password` (almacenamiento como hash bcrypt recomendado), `rol` ENUM(`admin`,`empleado`).
- **clientes**: datos de contacto y dirección.
- **productos**: nombre, descripción, precio, stock.
- **pedidos**: relación con cliente, fecha, estado (pendiente, en_proceso, enviado, entregado, cancelado).
- **detalle_pedidos**: líneas de pedido (producto, cantidad, precio unitario), FK a pedidos y productos con `ON DELETE CASCADE`.

Las tablas de pedidos y productos están **definidas en SQL**; la **implementación PHP** de esos módulos puede desarrollarse en fases posteriores.

## 7. Flujo de peticiones

1. El navegador solicita `public/index.php` (opcionalmente `?page=...`).
2. `index.php` evalúa `page` e incluye vistas o instancia `ClienteController` según la ruta.
3. El caso por defecto carga `LoginController` y la vista de login.
4. Tras login correcto (`password_verify` contra el hash almacenado), se redirige a `page=dashboard`.
5. Las acciones del módulo clientes exigen sesión válida (`$_SESSION['usuario']`); si no existe, redirección al login.
6. `logout` destruye la sesión y redirige al inicio.

## 8. Módulos implementados en código

### 8.1 Autenticación

- Formulario POST con email y contraseña.
- Consulta preparada por email a la tabla `usuarios`.
- Verificación con **`password_verify($password, $usuario['password'])`** frente al hash almacenado.
- Sesión PHP con datos del usuario; redirección al dashboard (la lógica por rol puede ampliarse en el futuro).

### 8.2 Dashboard

- Comprueba existencia de `$_SESSION['usuario']`; si no hay sesión, redirige al login.
- Muestra nombre de usuario y enlace de cierre de sesión.

### 8.3 Clientes (CRUD)

- Listado con tabla Bootstrap.
- Alta (`create`/`store`), edición (`edit`/`update`) y borrado (`delete`) con parámetro `id` en query string donde corresponde.
- El modelo `Cliente` encapsula `SELECT`, `INSERT`, `UPDATE`, `DELETE` con parámetros enlazados.
- El controlador aplica **control de acceso por sesión** antes de ejecutar cualquier acción.

## 9. Seguridad

**Implementado:**

- PDO con sentencias preparadas en el modelo de clientes y en la consulta de login.
- Contraseñas verificadas con `password_verify` (almacenamiento como hash bcrypt vía `password_hash` tras migración con `hash_updater.php`).
- Escape de salida con `htmlspecialchars` en vistas de clientes.
- Protección del módulo clientes frente a acceso anónimo (comprobación de sesión).

**Recomendaciones para evolución del proyecto:** tokens CSRF en formularios, política explícita de permisos por rol, regeneración de ID de sesión tras login, variables de entorno para secretos y credenciales, HTTPS en despliegue.

## 10. Entorno local

Desarrollo previsto en máquina local con MySQL accesible (por ejemplo mediante **Docker Compose** en el puerto `3307`) y herramientas como MySQL Workbench para inspección del esquema. La aplicación PHP debe servirse con el documento raíz en `public/`.

## 11. Limitaciones actuales respecto a un ERP completo

- No hay gestión implementada en PHP de productos ni del ciclo completo de pedidos pese a existir el esquema SQL.
- El dashboard no incluye aún gráficos ni KPIs analíticos.
- La interfaz no está unificada en todas las pantallas (Bootstrap parcial).

---

*Documento alineado con el estado del código y con la documentación de cambios en `docs/Cambios_Implementados_Fase1.md`.*
