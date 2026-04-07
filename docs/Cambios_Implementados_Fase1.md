# Cambios implementados — Fase 1

Este documento describe, de forma secuencial y justificable ante tribunal académico, los archivos creados, las modificaciones de código y el motivo de cada decisión de diseño.

---

## 1. Objetivos de la fase

1. Documentar la arquitectura y el stack en un único documento técnico (`Descripcion_Tecnica.md`).
2. Cerrar la brecha de seguridad por **acceso anónimo** al CRUD de clientes mediante comprobación de sesión.
3. Sustituir la comparación de contraseñas en **texto plano** por **`password_verify`** frente a hashes almacenados con **`password_hash`**.
4. Facilitar la **migración** de contraseñas ya existentes en la base de datos mediante un script de uso único.
5. Versionar un **`docker-compose.yml`** coherente con el puerto `3307` usado en `config/database.php`.
6. Mantener trazabilidad académica de todos los cambios en este fichero.

---

## 2. Archivos nuevos

### 2.1 `docs/Descripcion_Tecnica.md`

- **Qué es:** Memoria técnica del proyecto en Markdown: propósito, stack, MVC, estructura de carpetas, modelo de datos, flujos, seguridad y limitaciones.
- **Por qué:** Responde a la necesidad de documentación ordenada para el TFG y alinea la memoria con el estado real del código tras esta fase (sesión, hash, Docker).

### 2.2 `docs/Cambios_Implementados_Fase1.md` (este archivo)

- **Qué es:** Registro explícito de la fase de implementación para la memoria y para el tribunal.
- **Por qué:** Demuestra qué se ha hecho, dónde y con qué criterio (trazabilidad).

### 2.3 `hash_updater.php` (raíz del proyecto) — **eliminado tras migración**

- **Qué era:** Script PHP ejecutable solo por **CLI** que migraba contraseñas en texto plano a `password_hash`. Tras uso correcto debe borrarse por seguridad.
- **Nota:** El script fue diseñado intencionalmente para **no** ejecutarse desde el navegador (`PHP_SAPI !== 'cli'`).

### 2.4 `docker-compose.yml` (raíz del proyecto)

- **Qué es:** Servicio `mysql:8.0`, variable `MYSQL_DATABASE=mipedido`, `MYSQL_ROOT_PASSWORD=root`, mapeo de puertos **`"3307:3306"`**, volumen persistente `mysql_data` y montaje de `./config/database.sql` como script de inicialización del contenedor (`docker-entrypoint-initdb.d/01-init.sql`).
- **Por qué:** Reproduce el entorno local documentado (Workbench / Docker) y mantiene el mismo puerto que `config/database.php` sin editar el código PHP al cambiar de máquina.

### 2.5 `README.md` (raíz del proyecto)

- **Qué es:** Guía rápida para compañeros de equipo: descripción del ERP, requisitos (Docker Desktop y PHP), pasos para levantar MySQL y el servidor PHP integrado, credenciales de prueba, mapa de carpetas y referencia a la base de datos.
- **Por qué:** Reduce fricción al incorporar a otra persona al proyecto y documenta el flujo local validado (`docker compose` + `php -S localhost:8000 -t public`).

### 2.6 Usuario administrador inicial (script puntual, no versionado)

- **Qué fue:** Un script `insert_admin.php` en la raíz (solo CLI) insertó o actualizó en la tabla `usuarios` un registro con nombre `Admin`, email `admin@admin.com`, rol `admin` y contraseña `admin123` almacenada mediante **`password_hash`**. Tras ejecutarlo una vez por consola, el archivo se eliminó por seguridad (mismo criterio que `hash_updater.php`).
- **Por qué:** Permite iniciar sesión en la aplicación sin crear usuarios a mano en MySQL Workbench y mantiene coherencia con el login basado en `password_verify`.

---

## 3. Archivos modificados

### 3.1 `app/controllers/ClienteController.php`

| Ámbito | Descripción |
|--------|-------------|
| **Líneas ~7–17** | Nuevo método privado `requireAuth(): void` que inicia sesión si hace falta (`session_status() === PHP_SESSION_NONE`) y, si no existe `$_SESSION['usuario']`, redirige a `index.php` y termina la ejecución. |
| **Líneas ~19–61** | Al inicio de cada método público (`index`, `create`, `store`, `edit`, `update`, `delete`) se llama a `$this->requireAuth()`. |
| **Rutas `require`** | Sustitución de rutas relativas por `__DIR__ . '/../views/...'` para resolución estable independientemente del directorio de trabajo. |

**Justificación académica:** El front controller no aplicaba autenticación al módulo de clientes; cualquier usuario podía acceder por URL directa. Centralizar la comprobación en `requireAuth()` evita duplicar lógica y refuerza el principio de **mínimo privilegio** y **control de acceso** descrito en requisitos no funcionales de seguridad.

---

### 3.2 `app/controllers/LoginController.php`

| Ámbito | Descripción |
|--------|-------------|
| **Línea ~3** | `require_once __DIR__ . '/../../config/database.php'` en lugar de rutas relativas frágiles al directorio de ejecución. |
| **Líneas ~5–8** | Uso de `$_SERVER['REQUEST_METHOD'] === 'POST'` y operador null coalescing para email y contraseña. |
| **Líneas ~18–22** | Autenticación satisfactoria solo si `password_verify($password, $usuario['password'])` es verdadero; una sola asignación a `$_SESSION['usuario']`. |
| **Líneas ~24–28** | Mensajes de error diferenciados (usuario inexistente vs contraseña incorrecta) manteniendo el comportamiento previo. |
| **Eliminado** | Comparación directa `$password == $usuario['password']`, duplicado de `$_SESSION['usuario']` y ramas redundantes de rol que redirigían al mismo destino. |

**Justificación académica:** Cumple la recomendación explícita de usar funciones de PHP para **hash y verificación segura** de contraseñas, alineada con buenas prácticas OWASP y con el feedback del tribunal sobre documentar/implantar hash frente a almacenamiento en claro.

**Orden obligatorio con la base de datos:** Hasta ejecutar `hash_updater.php`, si las contraseñas siguen en texto plano, **`password_verify` fallará**. Por tanto, la migración debe realizarse **antes** de probar el login en producción de desarrollo.

---

### 3.3 `app/models/Cliente.php` (antes `app/models/cliente.php`)

| Ámbito | Descripción |
|--------|-------------|
| **Renombrado** | El fichero pasa de `cliente.php` a `Cliente.php` para coincidir con `require_once __DIR__ . '/../models/Cliente.php'` del controlador. |
| **Contenido** | Misma lógica que el modelo anterior; sin cambios funcionales en consultas PDO. |

**Justificación académica:** En sistemas de ficheros **sensibles a mayúsculas** (despliegue Linux típico), `Cliente.php` y `cliente.php` son distintos; unificar el nombre evita fallos en entorno de producción y mejora la coherencia con PSR de nombres de clase/archivo.

---

## 4. Archivos no modificados pero relacionados

- **`config/database.php`:** Sin cambios; debe seguir apuntando al host/puerto donde escuche MySQL (p. ej. `127.0.0.1:3307` con Docker Compose).
- **`config/database.sql`:** Sin cambios en esta fase; el campo `password` ya tenía tamaño `VARCHAR(255)` adecuado para hashes bcrypt.
- **`public/index.php`:** Sin cambios; el flujo de rutas sigue igual.

---

## 5. Instrucciones de uso de `hash_updater.php` (una vez, luego borrar)

*(El script ya fue eliminado del proyecto tras su ejecución; si necesitas repetir la migración en otra máquina, recupera la versión desde el historial de Git o vuelve a crear el fichero a partir de la memoria del TFG.)*

1. Asegúrate de que MySQL está accesible con los mismos datos que `config/database.php` (por ejemplo contenedor Docker en marcha en el puerto 3307).
2. Abre una terminal en la **raíz del proyecto** `miPedido` (donde estaba `hash_updater.php`).
3. Ejecuta: `php hash_updater.php`
4. Revisa la salida: filas **Actualizado** (texto plano migrado a hash) u **Omitido** (ya eran hashes).
5. Prueba el login en la aplicación con las **mismas contraseñas** que antes (ahora almacenadas como hash).
6. **Elimina** el archivo `hash_updater.php` del proyecto para evitar que pueda ejecutarse de nuevo o quedar expuesto si se despliega el código.

---

## 6. Instrucciones breves de Docker Compose

Desde la raíz del proyecto:

```bash
docker compose up -d
```

- MySQL quedará en `localhost:3307` (usuario `root`, contraseña `root`, base `mipedido` según este `docker-compose.yml` y tu `database.php`).
- La primera vez que se crea el volumen, se puede aplicar el SQL de inicialización montado en el contenedor.

---

## 7. Resumen ejecutivo

| Elemento | Acción |
|----------|--------|
| Documentación | Creada en `docs/` |
| Acceso CRUD clientes | Protegido por sesión en `ClienteController` |
| Login | `password_verify` + rutas con `__DIR__` |
| Migración contraseñas | `hash_updater.php` (CLI, uso único, borrar después) |
| Entorno MySQL | `docker-compose.yml` puerto 3307→3306 |
| Modelo cliente | Renombrado a `Cliente.php` por coherencia y portabilidad |
| Guía de equipo | `README.md` en la raíz |
| Usuario inicial | Admin creado vía script puntual (`insert_admin.php`, ejecutado y borrado) |

---

*Fase 1 — documentación y endurecimiento básico de seguridad (sesión + contraseñas). Evoluciones futuras sugeridas en `Descripcion_Tecnica.md` (roles, CSRF, dashboard analítico, módulos pedidos/productos).*
