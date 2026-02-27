# GitHub & Render Deployment Checklist

## Paso 1: Preparar el Repositorio Local

```bash
# Asegúrate de estar en la raíz del proyecto
cd C:\Users\angel\OneDrive\Escritorio\ProyectoWeb

# Inicializa git (si no está inicializado)
git init

# Añade todos los archivos (excepto .env y node_modules, están en .gitignore)
git add .

# Commit inicial
git commit -m "Initial commit: Role-based Auth System with Register, Login, Admin Panel"

# Añade el remote
git remote add origin https://github.com/Tenshishis/proyectoweb.git

# Push a main (o master, según tu default branch en GitHub)
git branch -M main
git push -u origin main
```

## Paso 2: Configurar Render

1. Ve a [Render Dashboard](https://dashboard.render.com)
2. Conecta tu GitHub (si no lo has hecho)
3. Opción A: **Blueprint Deploy** (automático)
   - Render debe detectar `render.yaml` automáticamente
   - Haz clic en Deploy
4. Opción B: **Manual** (si Blueprint no aparece)
   - Sigue los pasos en `DEPLOYMENT.md`

## Paso 3: Configurar Secretos en Render

Antes de desplegar, asegúrate de tener:

1. **MongoDB Atlas**
   - Crea un clúster gratis
   - Obtén la connection string con user/pass

2. **En Render Environment Variables**:
   ```
   MONGO_URI=mongodb+srv://user:pass@cluster.mongodb.net/dbname
   JWT_SECRET=algo_super_seguro_y_aleatorio
   FLASK_SECRET=otra_clave_segura_y_aleatoria
   ```

## Paso 4: Tests Finales

Después de que Render desplegó ambos servicios (5-10 min):

1. Abre la URL del frontend
2. Registra un usuario
3. Login como `admin` (si existe) o crea via Postman
4. Asigna rol y verifica todo funciona

## Archivos Importantes para GitHub

✅ Incluidos en git:
- `node-api/package.json` - dependencias Node
- `frontend/requirements.txt` - dependencias Python
- `render.yaml` - configuración Render
- `DEPLOYMENT.md` - instrucciones deploy
- `README.md` - documentación

❌ Excluidos en git (en `.gitignore`):
- `.env` - variables de ambiente
- `node_modules/` - dependencias instaladas
- `__pycache__/` - caché Python
- `.venv/` - virtual environment

## Reiniciar/Redeploy en Render

Si necesitas redeplegar sin cambios de código:
```
Dashboard → tu servicio → Manual Deploy → Deploy latest commit
```

## Ver Logs en Render

```
Dashboard → tu servicio → Logs (en tiempo real)
```

## Troubleshooting

Si algo falla:
1. Revisa los logs en Render (Dashboard → Logs)
2. Verifica que `MONGO_URI` es correcto
3. Asegúrate que MongoDB Atlas tiene IP whitelisted
4. Haz `git status` localmente para confirmar que todo está commiteado

¡Listo! Push a GitHub → Blueprint en Render → Deploy automático ⚡
