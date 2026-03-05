# ProyectoWeb

Monorepo con backend Node.js/Express y frontend Flask/Bootstrap para registro, login y roles.

## Backend (node-api)

Estructura de carpetas:

```
node-api/
├── src/
│   ├── config/      # Conexión a Mongo
│   ├── controllers/ # Lógica de request/response
│   ├── services/    # Reglas de negocio
│   ├── repositories/ # (vacío, preparado para abstracción)
│   ├── models/      # Esquemas mongoose
│   ├── middleware/  # JWT y roles
│   ├── routes/      # Enrutadores de express
│   ├── validators/  # Joi schemas
│   └── utils/       # Helpers comunes
└── server.js
```

### Configuración

1. Copia `.env` y ajusta `MONGO_URI`, `JWT_SECRET`, etc.
2. Ejecuta `npm install` dentro de `node-api`.
3. (Opcional) crea un administrador de pruebas con `npm run seed`.
4. Inicia con `npm run dev` (usa nodemon) o `npm start`.

### APIs disponibles

- `POST /api/auth/register` -> registra usuario (rol `null`).
- `POST /api/auth/login` -> recibe `identifier` (email o username) y contraseña. Devuelve token y user. Si el usuario no tiene rol asignado será 403.
- `PUT /api/admin/asignar-rol` -> body `{ userId, rol }`. Solo ADMIN con token válido.

### Cambios escalables

- Agregar rol: añadir valor a la constante `ROLES` en `src/utils/roles.js`. Los modelos y controladores lo importan automáticamente, así no hay que tocar más código.
- Cambiar motor de datos: crear repositorios que abstraigan mongoose.

## Frontend (Flask)

Templates Bootstrap 5 en `frontend/templates`.
`app.py` contiene rutas básicas de login, registro y páginas por rol.

### Setup

```bash
cd frontend
python -m venv venv
venv\Scripts\activate  # Windows
pip install -r requirements.txt
python app.py
```

Después de iniciar el backend, puedes usar el frontend en http://localhost:5000.

## Flujo de funcionamiento

1. Usuario se registra (rol null).
2. Admin asigna rol via API o interfaz.
3. Usuario inicia sesión; recibe JWT.
4. El frontend redirige según `rol`.
5. Rutas protegidas en backend con middleware `verifyToken` y `authorize`.

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
