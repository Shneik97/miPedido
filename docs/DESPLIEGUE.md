# Despliegue en hosting (producción)

Guía orientativa para publicar **miPedido** fuera del entorno local (PHP + MySQL), con credenciales fuera del código fuente.

## 1. Requisitos del servidor

- **PHP** 8.0 o superior (recomendado 8.2+) con extensiones: `pdo`, `pdo_mysql`, `session`, `json`.
- **MySQL** 5.7+ / **MariaDB** 10.3+.
- HTTPS habilitado (certificado SSL) para proteger sesiones y formularios en producción.

## 2. Subir el proyecto

1. Sube por **FTP/SFTP** o por el panel de archivos del hosting el contenido del repositorio (o un `.zip` generado con `git archive` / exportación).
2. La **raíz pública** del dominio debe apuntar a la carpeta **`public/`** (donde está `index.php`). En muchos hostings esto se configura como *Document Root* o *carpeta pública*.
   - Si no puedes cambiar el document root, algunos paneles permiten enlazar solo `public` como subcarpeta; en el peor caso, documenta en la memoria una regla de reescritura que redirija todo al front controller (menos recomendable).

### Ejemplos de plataformas

| Plataforma | Notas breves |
|------------|----------------|
| **000webhost** | Subida por gestor de archivos o FTP; crea base MySQL desde el panel; configura variables de entorno si el plan lo permite, o un `config/database.local.php` **no versionado** (añadir a `.gitignore`). |
| **Render** (Web Service PHP) | Suelen usar **Dockerfile** o buildpack; define variables de entorno en el panel; la URL pública será la que asigne Render. Conecta un MySQL gestionado o externo (PlanetScale, Railway, etc.). |

En ambos casos el flujo es el mismo: código en el servidor + base de datos remota + variables de entorno para no guardar contraseñas en Git.

## 3. Importar la base de datos

1. Desde **phpMyAdmin** (o cliente MySQL del hosting), crea una base de datos vacía (ej. `mipedido`).
2. Importa el fichero **`config/database.sql`** (pestaña Importar → elegir archivo → Ejecutar).
3. Si tu base **ya existía** de una versión anterior sin la columna `metodo_pago` en `pedidos`, ejecuta también **`config/migrate_fase3.sql`** una sola vez (o el `ALTER` equivalente).

Comprueba que las tablas `usuarios`, `clientes`, `productos`, `pedidos`, `detalle_pedidos` existen y que hay al menos un usuario administrador (puedes insertarlo con contraseña generada con `password_hash` en PHP o desde un script puntual en entorno controlado).

## 4. Configuración segura: variables de entorno

El fichero **`config/database.php`** lee la conexión desde variables de entorno, con valores por defecto para desarrollo local:

| Variable | Descripción | Ejemplo local |
|----------|-------------|----------------|
| `DB_HOST` | Host del servidor MySQL | `127.0.0.1` |
| `DB_PORT` | Puerto | `3307` |
| `DB_NAME` | Nombre de la base de datos | `mipedido` |
| `DB_USER` | Usuario MySQL | `root` |
| `DB_PASSWORD` | Contraseña MySQL | *(secreto)* |

### Cómo definirlas

- **Apache**: en `.htaccess` (si `AllowOverride` lo permite):
  ```apache
  SetEnv DB_HOST "mysql.tu-hosting.com"
  SetEnv DB_NAME "mipedido"
  SetEnv DB_USER "usuario_mysql"
  SetEnv DB_PASSWORD "contraseña_segura"
  ```
- **nginx + PHP-FPM**: `fastcgi_param` o fichero de entorno del pool.
- **Panel (cPanel, Plesk, Render, etc.)**: suele haber una sección **Environment** / **Variables** donde introduces cada clave.

**No subas** al repositorio contraseñas reales. Usa solo el ejemplo en documentación y valores reales solo en el servidor o en un fichero local ignorado por Git.

## 5. Comprobaciones finales

1. Abre la URL pública y prueba **login** y navegación básica.
2. Revisa permisos de carpetas si el hosting exige `writable` para sesiones (normalmente la sesión va a `/tmp` del servidor).
3. Activa **HTTPS** y fuerza redirección HTTP → HTTPS si el panel lo permite.

## 6. Resumen

| Paso | Acción |
|------|--------|
| 1 | Subir código; document root = `public/` |
| 2 | Crear BD e importar `database.sql` (+ `migrate_fase3.sql` si aplica) |
| 3 | Definir `DB_*` en el servidor |
| 4 | Probar login y módulos críticos |

---

*Documento académico para el TFG — miPedido.*
