```
╔═══════════════════════════════════════════════════════════════════════════════╗
║                                                                               ║
║                       🚀 PROYECTOWEB - QUICK START GUIDE 🚀                  ║
║                                                                               ║
║                    Sistema de Gestión de Productos e Inventario               ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝
```

---

## ⚡ 5 Pasos para Empezar (2 minutos)

### 1️⃣ Abre una PowerShell en la carpeta `php-api`

```powershell
# Windows - Abre PowerShell aquí o cambia dirección:
cd C:\Users\bruno\AndroidStudioProjects\ProyectoWeb\php-api
```

### 2️⃣ Ejecuta el instalador automático

```powershell
.\install.bat
```

El instalador hará todo automáticamente:
- ✅ Verifica Composer
- ✅ Instala dependencias PHP
- ✅ Crea archivo `.env`
- ✅ Guía de BD

### 3️⃣ Crea la Base de Datos (PostgreSQL)

```sql
-- Abre:  psql -U postgres

CREATE DATABASE proyectoweb WITH ENCODING 'UTF8';
\c proyectoweb
\i sql/schema.sql

-- Listo! ✅
```

### 4️⃣ Inicia el Servidor API

```powershell
composer serve
```

Deberías ver:
```
Listening on http://localhost:8000
```

### 5️⃣ Abre el Dashboard

En tu navegador (en otra pestaña):

```
http://localhost:8000/welcome.html
```

O directamente al dashboard:

```
http://localhost:8000/dashboard/
```

---

## 🔐 Login con Usuarios de Prueba

Elige uno y prueba:

```
Email:    admin@tienda.com
Password: admin123456
```

O:

```
Email:    vendedor@tienda.com
Password: admin123456
```

O:

```
Email:    consultor@tienda.com
Password: admin123456
```

---

## 📚 Documentación Completa

Después de que todo funcione, lee estos archivos:

| Archivo | Contenido | Quién Lo Necesita |
|---------|-----------|-------------------|
| [SETUP.md](SETUP.md) | Instalación paso a paso | Todos |
| [README.md](README.md) | Información general | Todos |
| [API_EXAMPLES.md](API_EXAMPLES.md) | 34 ejemplos de API | Desarrolladores |
| [dashboard/README.md](dashboard/README.md) | Features del dashboard | Usuarios |
| [TECHNICAL_REFERENCE.md](TECHNICAL_REFERENCE.md) | Arquitectura técnica | Desarrolladores backend |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Llevar a producción | DevOps |

---

## 🎯 Próximos Pasos

### Si es tu primera vez:

1. ✅ Ejecuta `.\install.bat`
2. ✅ Crea la Base de Datos
3. ✅ Corre `composer serve`
4. ✅ Abre http://localhost:8000/welcome.html
5. ✅ Login con admin@tienda.com / admin123456
6. ✅ Explora el dashboard

### Si quieres aprender a usar la API:

1. Lee [API_EXAMPLES.md](API_EXAMPLES.md)
2. Usa Postman o cURL para probar endpoints
3. Lee [TECHNICAL_REFERENCE.md](TECHNICAL_REFERENCE.md)

### Si quieres llevar a producción:

1. Lee [DEPLOYMENT.md](DEPLOYMENT.md)
2. Elige plataforma (Render, Heroku, VPS)
3. Sigue los pasos específicos
4. Configura variables de producción

---

## 🐛 Troubleshooting Rápido

### "Composer not found"
```powershell
# Reinicia PowerShell o instala Composer desde:
# https://getcomposer.org/download/
```

### "Cannot connect to PostgreSQL"
```powershell
# Verifica que PostgreSQL está corriendo:
# Windows: Services (servicios) → postgresql
# O: psql -U postgres (debería conectar)
```

### "API Error" en Dashboard
```powershell
# 1. Verifica que composer serve está corriendo
# 2. Revisa que BD tiene datos: psql -U postgres -d proyectoweb -c "SELECT COUNT(*) FROM usuarios;"
# 3. Abre DevTools (F12) y revisa Console
```

### "Tabla está vacía"
```sql
-- Verifica que seeding funcionó:
psql -U postgres -d proyectoweb -c "SELECT * FROM usuarios;"

-- Si está vacío, re-ejecuta schema:
psql -U postgres -d proyectoweb -f sql/schema.sql
```

---

## 🎓 Acceso Rápido a Archivos Importantes

### Configuración

```
.env                  ← Credenciales BD y JWT (EDITAR)
.env.example          ← Plantilla de .env
composer.json         ← Dependencias PHP
```

### Backend (API PHP)

```
src/index.php                    ← Punto de entrada
src/Router.php                   ← Routing
src/config/db.php                ← Conexión BD
src/controllers/                 ← Lógica de requests
src/services/                    ← Lógica de negocio
src/repositories/                ← Acceso a BD
src/middleware/authMiddleware.php ← JWT validation
```

### Frontend (Dashboard)

```
dashboard/index.html             ← HTML principal
dashboard/assets/js/api.js       ← Cliente API (fetch)
dashboard/assets/js/app.js       ← Lógica dashboard
dashboard/assets/css/style.css   ← Estilos
```

### Base de Datos

```
sql/schema.sql        ← Esquema y seed data
sql/                  ← Otros scripts SQL
```

---

## 🎮 Funcionalidades por Rol

### 👑 Admin (admin@tienda.com)
- ✅ Ver todos los productos
- ✅ Crear/Editar/Eliminar productos
- ✅ Gestionar inventario (entrada, salida, ajuste)
- ✅ Ver y gestionar usuarios
- ✅ Ver reportes completos

### 🛒 Vendedor (vendedor@tienda.com)
- ✅ Ver productos
- ✅ Gestionar inventario (entrada, salida, reservas)
- ❌ No puede crear/eliminar productos
- ❌ No puede gestionar usuarios

### 👁️ Consultor (consultor@tienda.com)
- ✅ Ver productos
- ✅ Ver inventario
- ❌ No puede hacer cambios
- ❌ Solo consulta (read-only)

---

## 🔗 URLs Principales

```
Bienvenida:   http://localhost:8000/welcome.html
Dashboard:    http://localhost:8000/dashboard/
API Raíz:     http://localhost:8000/
API Docs:     Ver API_EXAMPLES.md
```

---

## 📞 Ayuda Rápida

| Problema | Solución |
|----------|----------|
| No encuentra BD | Verifica que PostgreSQL corre y que BD existe |
| API responde 404 | Verifica que `composer serve` corre |
| Dashboard no carga | F12 → Console → busca errores |
| No puede loguear | Verifica que seeding ejecutó en BD |
| Stock se ve vacío | Entra con admin y agrega productos |

---

## 💡 Consejos Útiles

1. **Siempre tener 2 terminales abiertas:**
   - Una para `composer serve` (API)
   - Otra para comandos

2. **Usar F12 en navegador para debug:**
   - Network → ver requests a API
   - Console → ver errores JavaScript
   - Storage → ver localStorage con token

3. **Cambiar contraseñas en .env**
   - `JWT_SECRET` debe ser fuerte
   - `DB_PASSWORD` debe ser fuerte

4. **Hacer backup de BD regularmente:**
   ```bash
   pg_dump -U postgres proyectoweb > backup.sql
   ```

---

## ✨ Features Incluidos

✅ Dashboard moderno con RealTime charts  
✅ Autenticación JWT (24 horas)  
✅ 3 Roles: Admin, Vendedor, Consultor  
✅ CRUD de Productos, Inventario, Usuarios  
✅ Búsqueda y filtros  
✅ Validación datos (cliente + servidor)  
✅ Responsive (Desktop, Tablet, Mobile)  
✅ Documentación completa  
✅ 60+ endpoints REST  
✅ Base de datos normalizada  

---

## 🚀 Listo!

```
1. ✅ Corre:     .\install.bat
2. ✅ Crea BD:   psql + schema.sql
3. ✅ Inicia:    composer serve
4. ✅ Abre:      http://localhost:8000/welcome.html
5. ✅ Login:     admin@tienda.com / admin123456
6. ✅ ¡Disfruta!
```

---

## 📞 Necesitas más ayuda?

- Documentación completa: [SETUP.md](SETUP.md)
- Ejemplos de API: [API_EXAMPLES.md](API_EXAMPLES.md)
- Arquitectura técnica: [TECHNICAL_REFERENCE.md](TECHNICAL_REFERENCE.md)
- Deployment: [DEPLOYMENT.md](DEPLOYMENT.md)

---

**Hecho con ❤️  
ProyectoWeb - Product & Inventory Management System**
