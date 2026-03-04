#!/bin/bash

# Script de instalación para ProyectoWeb API
# Este script configura la base de datos, las dependencias y genera las claves necesarias

echo "🚀 Iniciando instalación de ProyectoWeb API"
echo "=================================================="

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Verificar que composer está instalado
echo -e "\n${YELLOW}[1/5]${NC} Verificando Composer..."
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer no está instalado${NC}"
    echo "Descárgalo desde: https://getcomposer.org/download/"
    exit 1
fi
echo -e "${GREEN}✓ Composer encontrado${NC}"

# 2. Instalar dependencias PHP
echo -e "\n${YELLOW}[2/5]${NC} Instalando dependencias PHP..."
composer install
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Dependencias instaladas${NC}"
else
    echo -e "${RED}❌ Error al instalar dependencias${NC}"
    exit 1
fi

# 3. Crear archivo .env
echo -e "\n${YELLOW}[3/5]${NC} Configurando archivo .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Archivo .env creado${NC}"
    echo -e "${YELLOW}⚠️  Por favor, edita .env con tus credenciales de PostgreSQL${NC}"
else
    echo -e "${GREEN}✓ Archivo .env ya existe${NC}"
fi

# 4. Crear base de datos (requiere que PostgreSQL esté corriendo)
echo -e "\n${YELLOW}[4/5]${NC} Configurando base de datos..."
echo "Para completar este paso manualmente, ejecuta:"
echo "  psql -U postgres"
echo "  CREATE DATABASE proyectoweb WITH ENCODING 'UTF8';"
echo "  \\c proyectoweb"
echo "  \\i sql/schema.sql"
echo ""
read -p "¿Ya creaste la base de datos? (s/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    echo -e "${GREEN}✓ Base de datos configurada${NC}"
else
    echo -e "${YELLOW}⚠️  Recuerda crear la base de datos antes de usar la API${NC}"
fi

# 5. Resumen
echo -e "\n${YELLOW}[5/5]${NC} Instalación completada"
echo ""
echo "=================================================="
echo -e "${GREEN}✅ ProyectoWeb API está listo${NC}"
echo "=================================================="
echo ""
echo "🚀 Para iniciar el servidor:"
echo "   composer serve"
echo ""
echo "📊 Para acceder al dashboard:"
echo "   http://localhost:8000/welcome.html"
echo "   o"
echo "   http://localhost:8000/dashboard/"
echo ""
echo "📚 Para más información, editá:"
echo "   - .env - Credenciales de base de datos"
echo "   - README.md - Documentación general"
echo "   - API_EXAMPLES.md - Ejemplos de API"
echo ""
