# Deployment Mixto: 2 servicios en Render + PHP en InfinityFree

Este documento deja el proyecto listo para el escenario que comentaste:

- Servicio 1 (Render): Backend Node (`node-api`)
- Servicio 2 (Render): Frontend Flask (`frontend`)
- Servicio adicional (InfinityFree): API PHP (`php-api`) si quieres mantenerla activa

## 1) Arquitectura recomendada

### Render
- `proyectoweb-backend` (Node API)
- `proyectoweb-frontend` (Flask UI)

### InfinityFree
- `php-api` (independiente)

Nota: para evitar conflictos, usa un backend oficial para el frontend principal.
Si Flask apunta a Node (`API_BASE=.../api`), deja PHP como servicio alterno o para pruebas.

## 2) Ya quedó preparado en el repo

- `render.yaml` ahora crea 2 servicios separados con `rootDir`:
  - `node-api`
  - `frontend`
- El backend Node soporta modo API-only con:
  - `ENABLE_NODE_UI=false`
- CORS configurable por variable:
  - `CORS_ORIGINS`

## 3) Variables en Render

### Servicio Node (`proyectoweb-backend`)
Obligatorias:
- `MONGO_URI`
- `DATABASE_URL`
- `PGHOST`
- `PGPORT`
- `PGDATABASE`
- `PGUSER`
- `PGPASSWORD`
- `JWT_SECRET`
- `DEFAULT_ADMIN_PASSWORD`

Recomendadas:
- `NODE_ENV=production`
- `ENABLE_NODE_UI=false`
- `PGSSLMODE=require`
- `CORS_ORIGINS=https://TU-FRONTEND.onrender.com,https://TU-DOMINIO-FRONT.com`

### Servicio Flask (`proyectoweb-frontend`)
- `API_BASE=https://TU-BACKEND.onrender.com/api`
- `FLASK_SECRET=<valor-seguro>`

## 4) Deploy en Render (Blueprint)

1. Push del repo con estos cambios.
2. En Render -> Blueprints -> New Blueprint Instance.
3. Selecciona el repo.
4. Render detecta `render.yaml` y crea 2 servicios.
5. Configura secretos y deploy.

## 5) PHP en InfinityFree

## 5.1 Subida
1. Sube la carpeta `php-api` al hosting (normalmente dentro de `htdocs`).
2. Asegura que `index.php` quede como entrypoint de la app PHP.

## 5.2 Entorno
- Crea `.env` en `php-api` con base en `.env.example`.
- Ajusta DB/JWT según el entorno que vayas a usar allí.

## 5.3 URL final esperada
Ejemplo:
- `https://TU-USUARIO.infinityfreeapp.com/php-api`

## 5.4 CORS
Si un frontend externo consumirá PHP, valida encabezados CORS en `php-api/index.php`.

## 6) Checklist de smoke test

1. Backend Node en Render responde `GET /` con JSON de estado.
2. `POST /api/auth/register` y `POST /api/auth/login` funcionan.
3. Frontend Flask abre y navega login/registro apuntando a `API_BASE` correcto.
4. Si PHP queda activo, prueba al menos `POST /auth/login` en su URL final.

## 7) Recomendación para no duplicar lógica

Si necesitas simplicidad operativa:
- Producción principal: Node + Flask en Render.
- PHP en InfinityFree: entorno alterno (demo/migración/backup), no backend principal del mismo frontend.
