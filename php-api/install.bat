@echo off
REM Script de instalación para ProyectoWeb API (Windows)
REM Este script configura la base de datos, las dependencias y genera las claves necesarias

echo.
echo =========================================================
echo.  ProyectoWeb API - Script de Instalacion
echo =========================================================
echo.

REM 1. Verificar que Composer está instalado
echo [1/5] Verificando Composer...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Composer no está instalado
    echo Descárgalo desde: https://getcomposer.org/download/
    echo.
    pause
    exit /b 1
)
echo [OK] Composer encontrado
echo.

REM 2. Instalar dependencias PHP
echo [2/5] Instalando dependencias PHP...
echo.
call composer install
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Error al instalar dependencias
    echo.
    pause
    exit /b 1
)
echo.
echo [OK] Dependencias instaladas
echo.

REM 3. Crear archivo .env
echo [3/5] Configurando archivo .env...
if not exist .env (
    copy .env.example .env >nul
    echo [OK] Archivo .env creado
    echo.
    echo [IMPORTANTE] Por favor, edita .env con tus credenciales de PostgreSQL
    echo             y luego vuelve a ejecutar este script
    echo.
    pause
) else (
    echo [OK] Archivo .env ya existe
    echo.
)

REM 4. Crear base de datos
echo [4/5] Configurando base de datos...
echo.
echo Para completar este paso manualmente, ejecuta en PostgreSQL:
echo.
echo   CREATE DATABASE proyectoweb WITH ENCODING 'UTF8';
echo   \c proyectoweb
echo   \i sql/schema.sql
echo.
set /p db_ready="¿Ya creaste la base de datos? (s/n): "
if /i "%db_ready%"=="s" (
    echo [OK] Base de datos configurada
    echo.
) else (
    echo [ADVERTENCIA] Recuerda crear la base de datos antes de usar la API
    echo.
)

REM 5. Resumen final
cls
echo =========================================================
echo.
echo    INSTALACION COMPLETADA - ProyectoWeb API
echo.
echo =========================================================
echo.
echo 🚀 Para iniciar el servidor:
echo    composer serve
echo.
echo 📊 Para acceder al dashboard:
echo    http://localhost:8000/welcome.html
echo    o
echo    http://localhost:8000/dashboard/
echo.
echo 📚 Para más información, edita:
echo    - .env - Credenciales de base de datos
echo    - README.md - Documentación general
echo    - API_EXAMPLES.md - Ejemplos de API
echo    - dashboard/README.md - Documentación del Dashboard
echo.
echo =========================================================
echo.
pause
