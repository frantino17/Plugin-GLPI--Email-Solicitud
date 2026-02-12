#!/bin/bash

# 🐳 GLPI Testing Environment with Docker
# Este script configura un entorno GLPI completo para probar el plugin

set -e

echo "=========================================="
echo "🐳 GLPI Plugin Testing Environment"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Verificar Docker
echo -e "${BLUE}[1/6]${NC} Verificando Docker..."
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Error: Docker no está instalado${NC}"
    echo "Instala Docker desde: https://docs.docker.com/engine/install/"
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker Compose no está instalado${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Docker está disponible${NC}"

# Verificar que estamos en el directorio correcto
echo -e "${BLUE}[2/6]${NC} Verificando estructura del plugin..."
if [ ! -f "../setup.php" ]; then
    echo -e "${RED}❌ Error: No se encuentra setup.php${NC}"
    echo "Este script debe ejecutarse desde el directorio 'demo' del plugin"
    exit 1
fi
echo -e "${GREEN}✅ Estructura del plugin correcta${NC}"

# Detener contenedores existentes si los hay
echo -e "${BLUE}[3/6]${NC} Limpiando contenedores anteriores..."
docker-compose down 2>/dev/null || true
echo -e "${GREEN}✅ Limpieza completada${NC}"

# Iniciar servicios
echo -e "${BLUE}[4/6]${NC} Iniciando servicios Docker..."
echo "   - MySQL 8.0"
echo "   - GLPI (última versión)"
echo ""
docker-compose up -d

# Esperar a que MySQL esté listo
echo -e "${BLUE}[5/6]${NC} Esperando a que MySQL esté listo..."
for i in {1..30}; do
    if docker-compose exec -T mysql mysqladmin ping -h localhost --silent 2>/dev/null; then
        echo -e "${GREEN}✅ MySQL está listo${NC}"
        break
    fi
    echo -n "."
    sleep 2
done

# Esperar a que GLPI esté listo
echo -e "${BLUE}[6/6]${NC} Esperando a que GLPI esté listo..."
for i in {1..30}; do
    if curl -sf http://localhost:8080 > /dev/null 2>&1; then
        echo -e "${GREEN}✅ GLPI está listo${NC}"
        break
    fi
    echo -n "."
    sleep 2
done

echo ""
echo "=========================================="
echo -e "${GREEN}✅ Entorno de pruebas iniciado${NC}"
echo "=========================================="
echo ""
echo -e "${YELLOW}📋 INFORMACIÓN DE ACCESO:${NC}"
echo ""
echo -e "  🌐 URL de GLPI:"
echo -e "     ${BLUE}http://localhost:8080${NC}"
echo ""
echo -e "  👤 Credenciales por defecto:"
echo -e "     Usuario: ${GREEN}glpi${NC}"
echo -e "     Password: ${GREEN}glpi${NC}"
echo ""
echo -e "  🔧 Usuario Admin:"
echo -e "     Usuario: ${GREEN}admin${NC}"
echo -e "     Password: ${GREEN}admin${NC}"
echo ""
echo -e "${YELLOW}📝 PASOS SIGUIENTES:${NC}"
echo ""
echo "  1. Abre http://localhost:8080 en tu navegador"
echo "  2. Completa el asistente de instalación de GLPI"
echo "  3. Inicia sesión con las credenciales de admin"
echo "  4. Ve a: Configuración > Plugins"
echo "  5. Busca 'Email Approval' e instálalo"
echo "  6. Activa el plugin"
echo "  7. Ve a: Asistencia > Solicitud Correo Docente"
echo ""
echo -e "${YELLOW}🛠️  COMANDOS ÚTILES:${NC}"
echo ""
echo "  Ver logs de GLPI:"
echo -e "    ${BLUE}docker-compose logs -f glpi${NC}"
echo ""
echo "  Ver logs de MySQL:"
echo -e "    ${BLUE}docker-compose logs -f mysql${NC}"
echo ""
echo "  Reiniciar servicios:"
echo -e "    ${BLUE}docker-compose restart${NC}"
echo ""
echo "  Detener servicios:"
echo -e "    ${BLUE}docker-compose down${NC}"
echo ""
echo "  Eliminar todo (incluyendo datos):"
echo -e "    ${BLUE}docker-compose down -v${NC}"
echo ""
echo -e "${YELLOW}🐛 TROUBLESHOOTING:${NC}"
echo ""
echo "  Si el plugin no aparece:"
echo "    1. Verifica que el directorio emailapproval esté montado"
echo "    2. Ejecuta: docker-compose restart glpi"
echo ""
echo "  Si GLPI no carga:"
echo "    1. Espera 1-2 minutos más (primera vez tarda más)"
echo "    2. Verifica logs: docker-compose logs glpi"
echo ""
echo "=========================================="
echo ""
