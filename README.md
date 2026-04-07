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
| Email  | `admin@admin.com` |
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
