# Arquitectura de Servicios Separados - Comunicación

Los archivos están organizados en 3 servicios independientes que se comunican entre sí.

---

## 1) Servicios

### Backend Node (node-api)
- **Puerto local**: 4000
- **Responsabilidad**: API REST + BD (PostgreSQL + MongoDB)
- **Archivo config**: `render.yaml` (servicio: `proyectoweb-backend`)
- **Env vars principales**:
  - `ENABLE_NODE_UI=false` → Solo API, sin HTML
  - `CORS_ORIGINS` → Dominios permitidos para acceso desde otros servicios
  - `NODE_ENV=production` → Para Render

### Frontend Flask (frontend)
- **Puerto local**: 5000
- **Responsabilidad**: UI (login, registro, dashboard por rol)
- **Archivo config**: `render.yaml` (servicio: `proyectoweb-frontend`)
- **Consume**: Backend Node via `API_BASE`
- **Env vars principales**:
  - `API_BASE=http://localhost:4000/api` (dev)
  - `FLASK_SECRET` → Clave de sesión

### Backend PHP (php-api) - OPCIONAL
- **Responsabilidad**: Alternativo a Node o backup
- **Se configura**: Manualmente en InfinityFree u otro hosting
- **Usa**: Misma BD PostgreSQL que Node

---

## 2) Flujo de Comunicación

```
Usuario → [Frontend Flask]
           ↓ (HTTP requests)
           [Backend Node API]
           ↓ (queries)
           [PostgreSQL / MongoDB]
```

### Ejemplo: Login

1. Usuario abre `http://localhost:5000/login` (Flask)
2. Flask renderiza formulario HTML
3. Usuario envía credenciales
4. Flask hace: `POST http://localhost:4000/api/auth/login` (Backend Node)
5. Backend verifica en PostgreSQL y responde con token JWT
6. Flask almacena token en sesión y redirige según rol

---

## 3) Configuración Local (desarrollo)

### Backend Node
```bash
cd node-api
cp .env.example .env
# Edita .env:
# - ENABLE_NODE_UI=true (si quieres HTML del Node)
# - ENABLE_NODE_UI=false (si solo quieres API)
# - CORS_ORIGINS=http://localhost:5000 (para Flask local)

npm install
npm run dev  # o npm start
```

**Resultado**: Servicio en `http://localhost:4000`

### Frontend Flask
```bash
cd frontend
python -m venv venv
venv\Scripts\activate

# Copia .env.example → .env
# Edita .env:
# - API_BASE=http://localhost:4000/api

pip install -r requirements.txt
python app.py
```

**Resultado**: Servicio en `http://localhost:5000`

### Prueba comunicación
1. Abre `http://localhost:5000`
2. Intenta registrar un usuario
3. La petición va a `http://localhost:4000/api/auth/register`

---

## 4) Configuración para Despliegue (Render)

Solo cambias variables de entorno:

### Backend Node en Render
```
ENABLE_NODE_UI=false  (solo API)
CORS_ORIGINS=https://proyectoweb-frontend.onrender.com
```

### Frontend Flask en Render
```
API_BASE=https://proyectoweb-backend.onrender.com/api
```

**El código no cambia**, solo las URLs en variables.

---

## 5) Variables de Cada Servicio

### Backend Node (.env.example)
```
PORT=4000
NODE_ENV=development
ENABLE_NODE_UI=true           # Con UI = responde HTML + API
CORS_ORIGINS=http://localhost:5000

# Base de datos
MONGO_URI=mongodb://localhost:27017/miapp
PGHOST=localhost
PGPORT=5432
PGDATABASE=proyectoweb
PGUSER=postgres
PGPASSWORD=postgres
PGSSLMODE=disable

# Secretos
JWT_SECRET=tuClave123
DEFAULT_ADMIN_PASSWORD=admin123
```

### Frontend Flask (.env.example)
```
# Para dev
API_BASE=http://localhost:4000/api
FLASK_SECRET=tunaClavePara123

# Para Render
# API_BASE=https://proyectoweb-backend.onrender.com/api
```

### Backend PHP (php-api/.env.example)
```
# Si usas PostgreSQL de Render/local
DB_DRIVER=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_NAME=proyectoweb
DB_USER=postgres
DB_PASSWORD=postgres
JWT_SECRET=tuClave123
APP_ENV=production
```

---

## 6) Checklist: ¿Están separados correctamente?

✅ **Backend Node**
- [ ] `node-api/server.js` tiene `ENABLE_NODE_UI` y `CORS_ORIGINS`
- [ ] `node-api/.env.example` tiene todas las vars
- [ ] `render.yaml` define servicio `proyectoweb-backend` con `rootDir: node-api`

✅ **Frontend Flask**
- [ ] `frontend/app.py` usa variable `API_BASE`
- [ ] `frontend/.env.example` documenta `API_BASE` y `FLASK_SECRET`
- [ ] `render.yaml` define servicio `proyectoweb-frontend` con `rootDir: frontend`

✅ **PHP (opcional)**
- [ ] `php-api/index.php` configurado Router
- [ ] `php-api/.env.example` tiene vars de BD

✅ **render.yaml**
- [ ] 2 servicios definidos (backend Node + frontend Flask)
- [ ] Cada uno con su `rootDir`
- [ ] Variables `sync: false` para secretos

---

## 7) Cómo Pruebas Localmente

**Terminal 1 - Backend**
```bash
cd node-api
npm install
npm run dev
# Responde en http://localhost:4000
```

**Terminal 2 - Frontend**
```bash
cd frontend
python app.py
# Responde en http://localhost:5000
```

**Navegador**: Abre `http://localhost:5000` y prueba login/registro

---

## 8) Próximo Paso: Deploy

Cuando estés listo, solo necesitas:

1. **Push a GitHub** (con todos estos cambios)
2. **En Render**: Seleccionar repo y deployment automático desde `render.yaml`
3. **Configurar variables** en Render dashboard

¡Ya está todo separado y listo para comunicarse! 🎯
