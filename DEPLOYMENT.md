# Deployment Guide - Render

Este proyecto está configurado para desplegar en **Render**, una plataforma cloud moderna y sencilla.

## Arquitectura

El proyecto es un **monorepo** con dos servicios independientes:
- **Backend (node-api)**: Express.js + Node.js + MongoDB
- **Frontend (frontend)**: Flask + Python

Ambos se despliegan como servicios separados en Render.

## Prerequisitos

1. Cuenta en [Render.com](https://render.com)
2. Repositorio en GitHub (este proyecto ya está configurado)
3. MongoDB Atlas (base de datos en la nube)

## Paso 1: Preparar MongoDB Atlas

1. Crea una cuenta en [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. Crea un clúster (la versión free está bien)
3. Crea un usuario de base de datos y anota las credenciales
4. Whitelist la IP o permite conexiones desde cualquier lugar (0.0.0.0/0)
5. Obtén la **connection string**:
   ```
   mongodb+srv://<username>:<password>@<cluster>.mongodb.net/<database>?retryWrites=true&w=majority
   ```

## Paso 2: Desplegar Backend en Render

### Opción A: Usando Blueprint (recomendado)

1. En [Render Dashboard](https://dashboard.render.com), ve a **Blueprints**
2. Conecta tu repositorio de GitHub
3. Render automáticamente detectará el `render.yaml` en la raíz
4. Haz clic en **Deploy**

### Opción B: Despliegue Manual

1. En [Render Dashboard](https://dashboard.render.com), crea un nuevo **Web Service**
2. Conecta tu repositorio de GitHub
3. Configura:
   - **Name**: `proyectoweb-backend`
   - **Root Directory**: `node-api`
   - **Runtime**: Node
   - **Build Command**: `npm install`
   - **Start Command**: `npm start`
4. En **Environment** añade:
   - `PORT`: `4000`
   - `MONGO_URI`: Tu connection string de MongoDB Atlas
   - `JWT_SECRET`: Una contraseña segura (genera una aleatoria)
5. Haz clic en **Create Web Service**

**Nota**: Render te asignará una URL como `https://proyectoweb-backend.onrender.com`

## Paso 3: Desplegar Frontend en Render

1. En Render, crea otro **Web Service**
2. Configura:
   - **Name**: `proyectoweb-frontend`
   - **Root Directory**: `frontend`
   - **Runtime**: Python
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `gunicorn app:app`
   - **Port**: `10000` (Render lo configura automáticamente)
3. En **Environment** añade:
   - `API_BASE`: `https://proyectoweb-backend.onrender.com/api`
   - `FLASK_SECRET`: Una contraseña segura para sesiones Flask
4. Haz clic en **Create Web Service**

## Paso 4: Verificar Despliegue

1. Espera 5-10 minutos a que ambos servicios se inicien (en el plan free de Render tarda)
2. Abre la URL del frontend (algo como `https://proyectoweb-frontend.onrender.com`)
3. Prueba el flujo completo:
   - Registra un usuario
   - Server seed: el admin ya debería estar disponible, u obtén el token manualmente
   - Login > Assign roles > Delete users

## Variables de Ambiente Necesarias

### Backend (node-api)

| Variable | Ejemplo | Descripción |
|----------|---------|-------------|
| `PORT` | `4000` | Puerto del servidor Node |
| `MONGO_URI` | `mongodb+srv://...` | Connection string MongoDB Atlas |
| `JWT_SECRET` | `supersecret123` | Contraseña para firmar JWTs |

### Frontend (frontend)

| Variable | Ejemplo | Descripción |
|----------|---------|-------------|
| `API_BASE` | `https://backend.onrender.com/api` | URL del backend |
| `FLASK_SECRET` | `flasksecret456` | Clave secreta para sesiones Flask |

## Troubleshooting

### El frontend no conecta al backend
- Verifica que `API_BASE` en Render apunta a la URL correcta del backend
- En browser DevTools → Network, chequea si ves errores CORS
- El backend debe tener CORS habilitado (ya está configurado en `server.js`)

### MongoDB connection error
- Verifica que `MONGO_URI` está correcta
- Chequea que MongoDB Atlas tiene whitelisted la IP de Render (o 0.0.0.0/0)
- En Render, mira los logs: Dashboard → tu servicio → Logs

### Servicio tarda mucho en iniciar
- En el plan **free** de Render, los servidores se duermen si no reciben tráfico
- Primera carga puede tardar 30+ segundos
- Considera upgradear a un plan pago si quieres performance consistente

## Build & Logs

Para ver logs en tiempo real:
```
Dashboard → tu servicio → Logs
```

Para regenerar el deployment:
```
Dashboard → tu servicio → Manual Deploy
```

## Actualizar el Código

Simplemente haz `git push` a tu rama default en GitHub. Render automáticamente detecta cambios y redeploya.

## Seguridad en Producción

⚠️ **Antes de lanzar a producción:**

1. Cambia `FLASK_SECRET` a algo seguro
2. Cambia `JWT_SECRET` a algo seguro
3. Asegúrate que tu `MONGO_URI` está bajo credenciales seguras
4. Revisa que la base de datos está con autenticación habilitada
5. Usa HTTPS siempre (Render lo proporciona por defecto)

## URLs Finales Ejemplo

- **Backend**: `https://proyectoweb-backend.onrender.com`
- **Frontend**: `https://proyectoweb-frontend.onrender.com`

¡Listo para producción! 🚀
