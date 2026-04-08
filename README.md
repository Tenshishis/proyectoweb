# ProyectoWeb

Monorepo con backend Node.js/Express y frontend Flask/Bootstrap para registro, login, roles, productos y reportes.

## Backend (node-api)

Estructura de carpetas:

```
node-api/
├── src/
│   ├── config/      # Conexión a bases de datos
│   ├── controllers/ # Lógica de request/response
│   ├── services/    # Reglas de negocio
│   ├── repositories/ # Acceso a PostgreSQL y MongoDB
│   ├── models/      # Modelos MongoDB (productos)
│   ├── middleware/  # JWT y roles
│   ├── routes/      # Enrutadores de express
│   ├── validators/  # Joi schemas
│   └── utils/       # Helpers comunes
└── server.js
```

### Configuración

1. Copia `.env` y ajusta `MONGO_URI`, `DATABASE_URL`/`PG*`, `JWT_SECRET`, etc.
2. Ejecuta `npm install` dentro de `node-api`.
3. Configura `DEFAULT_ADMIN_PASSWORD` si quieres cambiar la contraseña inicial automática.
4. Inicia con `npm run dev` (usa nodemon) o `npm start`.

Requisito para deploy limpio:

- `node-api/package.json` ya declara `cookie-parser`, que sí usa el servidor en producción.

Al arrancar por primera vez:

- PostgreSQL crea automáticamente tablas e índices si no existen.
- Se crea un admin inicial si la tabla `users` está vacía.
- MongoDB carga el catálogo base si la colección `productos` está vacía.

### APIs disponibles

- `POST /api/auth/register` -> registra usuario con rol inicial `PENDIENTE`.
- `POST /api/auth/login` -> recibe `identifier` (email o username) y contraseña. Devuelve token y user. Si el usuario no tiene rol asignado será 403.
- `PUT /api/admin/asignar-rol` -> body `{ userId, nuevoRol }`. Solo ADMIN con token válido.

### Cambios escalables

- Agregar rol: añadir el valor en `src/utils/roles.js` y registrarlo también en la tabla `roles` de PostgreSQL.
- Arquitectura actual: Productos en MongoDB; usuarios, roles, ventas y reportes en PostgreSQL.

## Frontend (Flask)

Templates Bootstrap 5 en `frontend/templates`.
`app.py` contiene rutas básicas de login, registro y páginas por rol.
Es un frontend alternativo; para el despliegue más simple en Render puedes usar solo el backend Node, que ya sirve sus propias páginas.

### Setup

```bash
cd frontend
python -m venv venv
venv\Scripts\activate  # Windows
pip install -r requirements.txt
python app.py
```

Después de iniciar el backend, puedes usar el frontend en http://localhost:5000.

Detalles importantes del frontend Flask:

- Usa `API_BASE` para apuntar al backend correcto.
- Ya maneja el caso `PENDIENTE` redirigiendo a `/espera-rol`.
- La plantilla de admin ya no tiene URLs hardcodeadas a `localhost:4000`.

## Despliegue recomendado

- Opción recomendada: desplegar ambos servicios en el mismo proyecto de Render usando `render.yaml`:
	- `proyectoweb-backend` (Node API + middleware + lógica de negocio)
	- `proyectoweb-frontend` (Flask UI)
- Ambos quedan en el mismo repo y el blueprint ya los conecta por defecto con:
	- `CORS_ORIGINS=https://proyectoweb-frontend.onrender.com` en backend
	- `API_BASE=https://proyectoweb-backend.onrender.com/api` en frontend

## Flujo de funcionamiento

1. Usuario se registra (rol `PENDIENTE` en PostgreSQL).
2. Admin asigna rol via API o interfaz.
3. Usuario inicia sesión; recibe JWT.
4. El frontend redirige según `rol`.
5. Rutas protegidas en backend con middleware `verifyToken` y `authorize`.

## Modelo de datos

- MongoDB:
	- `productos`
- PostgreSQL:
	- `roles`
	- `users`
	- `ventas`
	- `detalle_ventas`
	- `reporte_ventas`

## Bootstrap automático

- Admin inicial por defecto:
  - email: `admin@local`
  - usuario: `admin`
  - contraseña: `admin123` si no defines `DEFAULT_ADMIN_PASSWORD`
- Recomendado: cambiar `DEFAULT_ADMIN_PASSWORD` en Render antes del primer deploy.

## Pruebas rápidas

| Caso                        | Resultado esperado |
|----------------------------|--------------------|
| Login sin rol              | 403                |
| Login con contraseña mal   | 401                |
| Usuario sin token accede a `/api/admin` | 401    |
| Usuario no ADMIN accede a `/api/admin` | 403    |

## Escalabilidad

- Agregar más microservicios, roles, validaciones o motor de base de datos es sencillo gracias a la separación en capas (SRP/SOLID).

¡Listo para crecer!
