# Guía SQL (simple)

Este proyecto usa dos caminos, según tu caso.
Nivel estudiante:
- Si es tu primera instalación -> usa `database.sql`.
- Si ya tenías una base antigua -> usa migraciones por fases.

## Caso 1: instalación limpia (recomendado)

1. Crea la base ejecutando:
   - `config/database.sql`
2. Inicia la app.

Con esto ya tienes el esquema completo actual.

## Caso 2: base antigua ya existente

Si ya tenías datos y versión antigua, ejecuta estas migraciones en orden:

1. `config/migrate_fase4_pedidos_realizado_tareas_historial.sql`
2. `config/migrate_fase5_calendario_es_personal.sql`
3. `config/migrate_fase6_preferencias_ui.sql`
4. `config/migrate_fase7_seguridad_login_csrf_sesion.sql`
5. `config/migrate_fase8_permisos_usuarios.sql`
6. `config/migrate_fase9_onboarding_admin.sql`
7. `config/migrate_fase10_workspace_aislamiento.sql`
8. `config/migrate_fase11_verificacion_correo.sql`
9. `config/migrate_fase12_planes_usuario.sql`

Sugerencia práctica:
- Ejecuta una fase, verifica que no haya error, y luego pasa a la siguiente.
- Así te será más fácil saber en qué punto falló si algo va mal.

## Nota importante

- Para nuevos entornos, usa siempre `database.sql`.
- Las migraciones son solo para actualizar bases antiguas sin perder datos.

## Checklist pre-defensa (2 minutos)

Ejecuta estas comprobaciones rápidas en MySQL Workbench:

1. Usuario admin demo existe:
   - `SELECT id, email, rol FROM usuarios WHERE email='admin@test.com';`
2. Columnas clave de seguridad y cuenta están:
   - `SHOW COLUMNS FROM usuarios LIKE 'workspace_key';`
   - `SHOW COLUMNS FROM usuarios LIKE 'onboarding_version';`
   - `SHOW COLUMNS FROM usuarios LIKE 'email_verificado';`
   - `SHOW COLUMNS FROM usuarios LIKE 'plan_actual';`
   - `SHOW COLUMNS FROM usuarios LIKE 'failed_login_count';`
3. Tablas de permisos existen:
   - `SHOW TABLES LIKE 'permisos';`
   - `SHOW TABLES LIKE 'usuario_permisos';`
4. Tablas de negocio existen:
   - `SHOW TABLES LIKE 'clientes';`
   - `SHOW TABLES LIKE 'productos';`
   - `SHOW TABLES LIKE 'pedidos';`
   - `SHOW TABLES LIKE 'tareas';`
5. En la app:
   - Login correcto.
   - Dashboard carga sin errores.
   - Configuración muestra verificación y plan.
