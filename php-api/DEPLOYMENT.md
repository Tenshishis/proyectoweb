# 🚀 Deployment a Producción - ProyectoWeb

Guía para desplegar ProyectoWeb en un servidor de producción (Render, Heroku, VPS, etc.)

## 📋 Pre-Deployment Checklist

- [ ] Cambié todas las contraseñas por defecto
- [ ] Configuré `.env` con variables de producción
- [ ] Cambié `APP_ENV=production` en `.env`
- [ ] Probé localmente antes de deployar
- [ ] Hice backup de la BD
- [ ] Configuré HTTPS/SSL
- [ ] Verifiqué permisos de carpetas
- [ ] Revisé logs de errores
- [ ] Actualicé todas las dependencias
- [ ] Cambié `JWT_SECRET` por valor fuerte

---

## 🌐 Opción 1: Render.com (Recomendado)

### Paso 1: Crear Cuenta en Render

1. Ve a https://render.com
2. Sign up con GitHub o email
3. Verifica tu correo

### Paso 2: Conectar Repositorio GitHub

1. Autoriza acceso a tu repositorio
2. Selecciona el repositorio ProyectoWeb

### Paso 3: Crear PostgreSQL Database

1. Dashboard → Databases
2. Click "New +"
3. Selecciona PostgreSQL
4. Nombre: `proyectoweb-db`
5. Region: Tu región más cercana
6. Plan: Free o Starter
7. **Copia la conexión URI** (necesaria después)

### Paso 4: Configurar y Deploy

1. Dashboard → Web Services
2. Click "New +"
3. Selecciona "Deploy an existing repo"
4. Selecciona tu repositorio
5. Configura:
   - Name: `proyectoweb-api`
   - Environment: `Docker` o `PHP`
   - Build Command: `composer install; php sql/seed.php`
   - Start Command: `composer serve --port 10000`

### Paso 5: Variables de Entorno

1. En las settings de tu servicio, ve a "Environment"
2. Añade estas variables:

```env
DB_HOST=<DATABASE_HOST_desde_Render>
DB_PORT=5432
DB_NAME=<DATABASE_NAME>
DB_USER=<DATABASE_USER>
DB_PASSWORD=<DATABASE_PASSWORD>
JWT_SECRET=generador_de_claves_fuerte_aleatorio
APP_ENV=production
APP_DEBUG=false
CORS_ORIGIN=https://proyectoweb-api.onrender.com
```

### Paso 6: Deploy

1. Render detecta cambios automáticamente
2. Verifica el deployment en Dashboard → Events
3. Una vez complete, tu API estará en:
   ```
   https://proyectoweb-api.onrender.com
   ```

---

## 🌐 Opción 2: Heroku (Alternativo)

### Paso 1: Instalar CLI de Heroku

```bash
# Windows
choco install heroku-cli

# macOS
brew tap heroku/brew && brew install heroku

# Linux
curl https://cli-assets.heroku.com/install.sh | sh
```

### Paso 2: Login

```bash
heroku login
```

### Paso 3: Crear App

```bash
heroku create proyectoweb-api
heroku addons:create heroku-postgresql:hobby-dev -a proyectoweb-api
```

### Paso 4: Configurar Variables

```bash
heroku config:set DB_HOST=... -a proyectoweb-api
heroku config:set DB_NAME=... -a proyectoweb-api
heroku config:set DB_USER=... -a proyectoweb-api
heroku config:set DB_PASSWORD=... -a proyectoweb-api
heroku config:set JWT_SECRET=... -a proyectoweb-api
heroku config:set APP_ENV=production -a proyectoweb-api
```

### Paso 5: Crear Procfile (en raíz del proyecto)

```
web: composer serve --port $PORT
```

### Paso 6: Deploy

```bash
git push heroku main
heroku logs --tail -a proyectoweb-api
```

---

## 💻 Opción 3: VPS Linux (DigitalOcean, Linode, AWS)

### Paso 1: SSH en tu Servidor

```bash
ssh root@tu_servidor_ip
```

### Paso 2: Instalar Dependencias

```bash
# Actualizar sistema
sudo apt-get update
sudo apt-get upgrade -y

# Instalar PHP
sudo apt-get install php7.4 php7.4-cli php7.4-fpm php7.4-curl php7.4-xml php7.4-pgsql -y

# Instalar PostgreSQL
sudo apt-get install postgresql postgresql-contrib -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Nginx (o Apache)
sudo apt-get install nginx -y
```

### Paso 3: Clonar Repositorio

```bash
cd /var/www
git clone https://github.com/tuusuario/proyectoweb.git
cd proyectoweb/php-api
```

### Paso 4: Configurar BD

```bash
# Como usuario postgres
sudo -u postgres psql

# En la consola PostgreSQL:
CREATE DATABASE proyectoweb;
\c proyectoweb
\i sql/schema.sql
\q
```

### Paso 5: Instalar Dependencias PHP

```bash
cd /var/www/proyectoweb/php-api
composer install
```

### Paso 6: Configurar .env

```bash
cp .env.example .env
nano .env

# Edita con credenciales reales:
DB_HOST=localhost
DB_NAME=proyectoweb
DB_USER=postgres
DB_PASSWORD=tu_contraseña
JWT_SECRET=algo_muy_secreto_y_largo
APP_ENV=production
```

### Paso 7: Configurar Nginx

Edita `/etc/nginx/sites-available/proyectoweb`:

```nginx
server {
    listen 80;
    server_name tu_dominio.com www.tu_dominio.com;
    root /var/www/proyectoweb/php-api;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Headers de producción
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### Paso 8: Habilitar Sitio

```bash
sudo ln -s /etc/nginx/sites-available/proyectoweb /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Paso 9: SSL/HTTPS con Let's Encrypt

```bash
sudo apt-get install certbot python3-certbot-nginx -y
sudo certbot --nginx -d tu_dominio.com -d www.tu_dominio.com
```

### Paso 10: Permisos

```bash
sudo chown -R www-data:www-data /var/www/proyectoweb
sudo chmod -R 755 /var/www/proyectoweb
sudo chmod -R 775 /var/www/proyectoweb/php-api
```

---

## 🔐 Configuración de Seguridad en Producción

### 1. Variables de Entorno en Producción

```env
# Cambiar estos valores
JWT_SECRET=generador_fuerte_de_al_menos_32_caracteres_aleatorios
DB_PASSWORD=contraseña_muy_fuerte_y_compleja
ADMIN_EMAIL=admin@tu_dominio_real.com

# Desactivar debugging
APP_ENV=production
APP_DEBUG=false

# Actualizar URL de CORS
CORS_ORIGIN=https://tu_dominio.com
```

### 2. Headers HTTP Segura

Agrega a `.htaccess` o config de Nginx:

```apache
# .htaccess
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "no-referrer-when-downgrade"
Header always set Content-Security-Policy "default-src 'self'"
```

### 3. SQL Injection Prevention

✅ Ya implementado: SQLPreparedStatements

```php
// BIEN - Usar prepared statements
$stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

// MAL - Concatenación (NUNCA HACER)
$query = "SELECT * FROM usuarios WHERE email = '$email'";
```

### 4. CSRF Protection

```php
// Agregar token CSRF en formularios
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validar en POST
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token inválido');
}
```

### 5. Rate Limiting

```php
// Limitar requests por IP
$ip = $_SERVER['REMOTE_ADDR'];
$cacheKey = "rate_limit_$ip";
$count = apcu_fetch($cacheKey) ?: 0;

if ($count > 100) { // 100 requests por minuto
    http_response_code(429);
    exit('Too Many Requests');
}

apcu_store($cacheKey, $count + 1, 60);
```

### 6. HTTPS/SSL Obligatorio

```php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

---

## 📊 Monitoreo en Producción

### Logs

```bash
# Nginx
sudo tail -f /var/log/nginx/error.log

# PHP-FPM
sudo tail -f /var/log/php7.4-fpm.log

# PostgreSQL
sudo tail -f /var/log/postgresql/postgresql.log
```

### Uptime Monitoring

Servicios recomendados:
- [UptimeRobot](https://uptimerobot.com) - Gratuito
- [StatusPage](https://www.atlassian.com/software/statuspage) - Dashboards
- [Newrelic](https://newrelic.com) - Performance monitoring

### Backups

```bash
# Backup automático diario
0 2 * * * pg_dump -U postgres proyectoweb > /backups/db_$(date +\%Y\%m\%d).sql

# Backup a S3
0 3 * * * aws s3 cp /backups/ s3://mi-bucket-backup/
```

---

## 🚨 Troubleshooting Post-Deploy

### Errores Comunes

**Error: "Connection refused"**
```
Causa: BD no está corriendo
Solución: sudo service postgresql start
```

**Error: "Permission denied"**
```
Causa: Permisos de archivos incorrectos
Solución: sudo chown -R www-data:www-data /var/www/...
```

**Error: "404 Not Found"**
```
Causa: Rewrite rules no configuradas
Solución: Verifica .htaccess o nginx conf
```

**Lentitud**
```
Causa: Query N+1, indexes faltantes
Solución:
1. Agregar indexes: CREATE INDEX idx_email ON usuarios(email);
2. Revisar consultas con EXPLAIN
3. Usar caching (Redis)
```

---

## ✅ Post-Deployment Checklist

- [ ] API responde en dominio de producción
- [ ] HTTPS/SSL funcionando
- [ ] Login con usuario admin funciona
- [ ] Dashboard carga datos
- [ ] Créa producto de prueba y verifica
- [ ] BD tiene backups configurados
- [ ] Logs monitoreados
- [ ] Alertas de error configuradas
- [ ] Performance dentro de límites
- [ ] Documentación actualizada

---

## 🎯 Monitoreo Continuo

### Métricas importantes

1. **Disponibilidad**: Debe estar >99.5%
2. **Latencia**: <200ms promedio
3. **Errores**: <0.1% de requests
4. **DB Size**: Crecer <10% por mes
5. **Usuarios Activos**: Rastrear tendencias

### Tools recomendados

- **New Relic** - APM (Application Performance Monitoring)
- **Datadog** - Infrastructure monitoring
- **Sentry** - Error tracking
- **UptimeRobot** - Uptime monitoring
- **Auth0** - Identity management avanzado

---

## 📱 Actualizaciones y Mantenimiento

### Actualizar dependencias

```bash
composer update
php vendor/bin/composer-audit
```

### Migrar Schema BD

```bash
# Crear nuevo cambio
php sql/migrations/001_add_column.sql

# Ejecutar
psql -d proyectoweb -f sql/migrations/001_add_column.sql
```

### Zero-downtime Deploy

1. Deploya nuevamente sin cambios
2. Ejecuta migraciones en segundo plano
3. Actualiza código
4. Reinicia servicios

---

**Happy Deploying! 🚀**

Para más info, consulta:
- [Render Documentation](https://render.com/docs)
- [Heroku Documentation](https://devcenter.heroku.com)
- [DigitalOcean Tutorials](https://www.digitalocean.com/community/tutorials)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
