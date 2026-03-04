# 🚀 Guía de Instalación - ProyectoWeb

Guía paso a paso para configurar y ejecutar ProyectoWeb en tu máquina local.

## 📋 Requisitos Previos

### Software Necesario
- **PHP 7.4+** - Lenguaje de backend
- **PostgreSQL 12+** - Base de datos
- **Composer** - Gestor de dependencias PHP
- **Git** - Control de versiones (opcional)

### Verificar instalaciones

**Windows (PowerShell):**
```powershell
php --version
psql --version
composer --version
```

**macOS/Linux:**
```bash
php --version
psql --version
composer --version
```

## 💻 Instalación Paso a Paso

### Paso 1: Descargar el Proyecto

```bash
# Si usas Git
git clone https://github.com/tuusuario/proyectoweb.git
cd proyectoweb/php-api

# O simplemente descomprime el ZIP
```

### Paso 2: Ejecutar el Instalador (RECOMENDADO)

#### En Windows:
```powershell
# Abre PowerShell en la carpeta php-api y ejecuta:
.\install.bat
```

#### En macOS/Linux:
```bash
chmod +x install.sh
./install.sh
```

**El instalador automáticamente:**
1. ✅ Verifica que Composer esté instalado
2. ✅ Instala todas las dependencias PHP
3. ✅ Crea el archivo `.env` si no existe
4. ✅ Guía para crear la base de datos

---

### O Paso 2 (Manual): Configuración Manual

Si prefieres hacer todo manualmente, sigue estos pasos:

#### 2A. Instalar Dependencias

```bash
cd php-api
composer install
```

#### 2B. Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar .env con tus credenciales:
```

Edita `.env` y configura:

```env
# Base de datos
DB_HOST=localhost
DB_PORT=5432
DB_NAME=proyectoweb
DB_USER=postgres
DB_PASSWORD=tu_contraseña_postgres

# JWT
JWT_SECRET=tu_clave_secreta_aqui
JWT_ALGO=HS256

# App
APP_ENV=development
APP_DEBUG=true
```

#### 2C. Crear Base de Datos

Conectarse a PostgreSQL:

```bash
psql -U postgres
```

Ejecutar en la consola de PostgreSQL:

```sql
-- Crear la base de datos
CREATE DATABASE proyectoweb WITH ENCODING 'UTF8';

-- Conectarse a la base de datos
\c proyectoweb

-- Ver disponible el valor de psql client (en Windows a veces puede variar)
-- Ejecutar el script SQL
\i sql/schema.sql

-- Verificar que se crearon las tablas
\d
```

O directamente desde la línea de comandos:

```bash
# Linux/macOS
psql -U postgres -d proyectoweb -f sql/schema.sql

# Windows (cmd)
psql -U postgres -d proyectoweb -f sql/schema.sql
```

**✅ Verifica que se created las siguientes tablas despues:**
- `usuarios`
- `categorias`
- `proveedores`
- `productos`
- `producto_proveedor`
- `inventario`
- `movimientos_inventario`
- `reporte_ventas`

---

### Paso 3: Verificar la Instalación

```bash
# Windows - Verifica que composer.json existe
dir composer.json

# Linux/Mac - Verifica que composer.json existe
ls composer.json
```

Deberías ver output como:
```
 Volume in drive C is Windows
 Directory of C:\Users\...\ProyectoWeb\php-api

04/15/2024  02:45 PM             1,234 composer.json
```

---

## 🎯 Iniciar el Proyecto

### Opción 1: Usar Composer Server (RECOMENDADO)

```bash
cd php-api
composer serve
```

### Opción 2: Usar PHP Built-in Server

```bash
cd php-api
php -S localhost:8000
```

### Opción 3: Usar Apache (Avanzado)

Configura un VirtualHost en Apache que apunte a `php-api/`.

---

## 🌐 Acceder a la Aplicación

Una vez que el servidor está corriendo en `http://localhost:8000`:

### 🎨 Página de Bienvenida
```
http://localhost:8000/welcome.html
```

### 📊 Dashboard
```
http://localhost:8000/dashboard/
```

### 🔌 API
```
http://localhost:8000/
```

---

## 🔐 Usuarios de Prueba

La base de datos viene pre-cargada con 3 usuarios de prueba:

| Email | Password | Rol | Permisos |
|-------|----------|-----|----------|
| admin@tienda.com | admin123456 | Admin | Acceso total |
| vendedor@tienda.com | admin123456 | Vendedor | Productos + Inventario |
| consultor@tienda.com | admin123456 | Consultor | Solo lectura |

**IMPORTANTE:** Cambia las contraseñas en producción

---

## 🐛 Troubleshooting

### Error: "SQLSTATE[08006]"
**Problema:** No puede conectarse a PostgreSQL

**Solución:**
1. Verifica que PostgreSQL está corriendo
2. Verifica credenciales en `.env`
3. Verifica que la base de datos existe

```bash
# Ver bases de datos existentes
psql -U postgres -l
```

### Error: "Composer not found"
**Problema:** Composer no está en el PATH

**Solución:**
- En Windows, reinstala Composer y selecciona "Add to PATH"
- En Linux/Mac, verifica que `~/.composer/vendor/bin` está en PATH

### Las tablas están vacías
**Problema:** El script schema.sql no se ejecutó

**Solución:**
```bash
cd php-api
psql -U postgres -d proyectoweb -f sql/schema.sql
```

### Error 404 en el Dashboard
**Problema:** La API no está corriendo

**Solución:**
1. Abre una terminal separada
2. Ve a la carpeta `php-api`
3. Ejecuta `composer serve`
4. Actualiza el navegador

### Error CORS
**Problema:** Cross-Origin error

**Solución:**
- Ya está configurado en la API, pero verifica que:
  - Estás accediendo desde `http://localhost:8000`
  - No desde `http://127.0.0.1:8000` o diferente origen

---

## 📁 Estructura del Proyecto

```
proyectoweb/
├── php-api/                    # Backend (REST API)
│   ├── dashboard/              # Frontend (Admin Dashboard)
│   │   ├── index.html
│   │   ├── README.md
│   │   └── assets/
│   │       ├── css/
│   │       └── js/
│   ├── src/                    # Código PHP
│   │   ├── config/             # Configuración
│   │   ├── controllers/        # Controladores
│   │   ├── models/             # Modelos
│   │   ├── repositories/       # Acceso a datos
│   │   ├── services/           # Lógica de negocio
│   │   ├── middleware/         # Middlewares
│   │   ├── utils/              # Utilidades
│   │   └── validators/         # Validadores
│   ├── sql/                    # Scripts SQL
│   │   └── schema.sql          # Esquema de BD
│   ├── public/                 # Archivos públicos
│   │   └── templates/          # HTML templates
│   ├── .env.example            # Ejemplo de variables
│   ├── composer.json           # Dependencias PHP
│   ├── install.bat             # Instalador Windows
│   ├── install.sh              # Instalador Unix
│   └── index.php               # Entrada principal
├── node-api/                   # Backend alternativo en Node.js
└── frontend/                   # Frontend alternativo en Python
```

---

## 🚀 Próximos Pasos

### Después de instalar:

1. **Explora el Dashboard**
   - Ingresa con admin@tienda.com
   - Navega entre las 4 páginas
   - Crea productos
   - Gestiona inventario

2. **Lee la Documentación**
   - [README.md](README.md) - Información general
   - [API_EXAMPLES.md](API_EXAMPLES.md) - Ejemplos de API
   - [dashboard/README.md](dashboard/README.md) - Dashboard docs

3. **Detalles Técnicos**
   - PostgreSQL schema: [sql/schema.sql](sql/schema.sql)
   - Controllers: [src/controllers/](src/controllers/)
   - Services: [src/services/](src/services/)

---

## 📞 Support

Si tienes problemas:

1. **Revisa los logs**
   ```bash
   # En la terminal donde corre el servidor
   # Busca mensajes de error
   ```

2. **Revisa la consola del navegador**
   - Presiona F12 → Console
   - Busca errores en rojo

3. **Verifica la base de datos**
   ```bash
   psql -U postgres -d proyectoweb
   \d
   SELECT * FROM usuarios;
   ```

4. **Reinicia todo**
   - Cierra el servidor
   - Cierra el navegador
   - Abre nuevamente

---

## 🎓 Aprender Más

### Documentación de Tecnologías

- [PHP Documentation](https://www.php.net/docs.php)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Bootstrap 5](https://getbootstrap.com/docs/5.0/)
- [Chart.js](https://www.chartjs.org/docs/)
- [JWT (Firebase)](https://github.com/firebase/php-jwt)

### Recursos del Proyecto

- [README.md](README.md) - Visión general
- [API_EXAMPLES.md](API_EXAMPLES.md) - 34 ejemplos de API
- [dashboard/README.md](dashboard/README.md) - Dashboard features

---

## ✅ Checklist de Instalación

- [ ] Instalé PHP 7.4+
- [ ] Instalé PostgreSQL 12+
- [ ] Instalé Composer
- [ ] Cloné/descargué el proyecto
- [ ] Ejecuté `composer install`
- [ ] Creé el archivo `.env` con mis credenciales
- [ ] Creé la base de datos `proyectoweb`
- [ ] Ejecuté `sql/schema.sql` en la base de datos
- [ ] Verifico que la API corre con `composer serve`
- [ ] Accedo a `http://localhost:8000/welcome.html`
- [ ] Me puedo loguear con admin@tienda.com
- [ ] Veo datos en el dashboard

---

**¡Listo!** 🎉 Tu proyecto ProyectoWeb está instalado y funcionando.

Para dudas o problemas, revisa:
- [README.md](README.md)
- [API_EXAMPLES.md](API_EXAMPLES.md)
- [dashboard/README.md](dashboard/README.md)
