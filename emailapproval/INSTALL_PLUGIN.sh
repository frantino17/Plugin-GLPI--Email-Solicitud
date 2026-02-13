#!/bin/bash
#
# Script de instalación del plugin Email Approval para GLPI
# 
# Este script copia el plugin al directorio correcto de GLPI
# y establece los permisos apropiados.
#

set -e  # Salir si hay algún error

# Colores para mensajes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Instalador del Plugin Email Approval para GLPI ===${NC}\n"

# 1. Detectar ubicación de GLPI
echo "Buscando instalación de GLPI..."

GLPI_PATHS=(
    "/var/www/html/glpi"
    "/var/www/glpi"
    "/usr/share/glpi"
    "/opt/glpi"
    "/srv/www/glpi"
    "$HOME/glpi"
)

GLPI_DIR=""
for path in "${GLPI_PATHS[@]}"; do
    if [ -d "$path/plugins" ] && [ -f "$path/index.php" ]; then
        GLPI_DIR="$path"
        echo -e "${GREEN}✓ GLPI encontrado en: $GLPI_DIR${NC}"
        break
    fi
done

# Si no se encontró, preguntar al usuario
if [ -z "$GLPI_DIR" ]; then
    echo -e "${YELLOW}No se encontró GLPI automáticamente.${NC}"
    read -p "Por favor, ingrese la ruta completa de su instalación GLPI: " GLPI_DIR
    
    if [ ! -d "$GLPI_DIR/plugins" ]; then
        echo -e "${RED}Error: No se encontró el directorio 'plugins' en $GLPI_DIR${NC}"
        exit 1
    fi
fi

PLUGINS_DIR="$GLPI_DIR/plugins"
TARGET_DIR="$PLUGINS_DIR/emailapproval"

echo ""
echo "Configuración:"
echo "  - GLPI Dir: $GLPI_DIR"
echo "  - Plugins Dir: $PLUGINS_DIR"
echo "  - Target Dir: $TARGET_DIR"
echo ""

# 2. Verificar si el plugin ya existe
if [ -d "$TARGET_DIR" ]; then
    echo -e "${YELLOW}⚠ El plugin ya existe en $TARGET_DIR${NC}"
    read -p "¿Desea sobrescribirlo? (s/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[SsYy]$ ]]; then
        echo "Instalación cancelada."
        exit 0
    fi
    echo "Eliminando versión anterior..."
    sudo rm -rf "$TARGET_DIR"
fi

# 3. Copiar plugin
echo "Copiando plugin a $TARGET_DIR..."
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if sudo cp -r "$SCRIPT_DIR" "$TARGET_DIR"; then
    echo -e "${GREEN}✓ Plugin copiado correctamente${NC}"
else
    echo -e "${RED}✗ Error al copiar el plugin${NC}"
    exit 1
fi

# 4. Establecer permisos correctos
echo "Estableciendo permisos..."

# Detectar usuario web
WEB_USER="www-data"
if id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
fi

echo "  - Usuario web detectado: $WEB_USER"

if sudo chown -R "$WEB_USER:$WEB_USER" "$TARGET_DIR"; then
    echo -e "${GREEN}✓ Propietario establecido${NC}"
else
    echo -e "${YELLOW}⚠ No se pudo cambiar el propietario (puede requerir permisos)${NC}"
fi

if sudo chmod -R 755 "$TARGET_DIR"; then
    echo -e "${GREEN}✓ Permisos establecidos${NC}"
else
    echo -e "${YELLOW}⚠ No se pudieron cambiar los permisos${NC}"
fi

# 5. Verificar estructura del plugin
echo ""
echo "Verificando estructura del plugin..."

REQUIRED_FILES=("setup.php" "hook.php" "inc/approval.class.php" "front/approve.php")
ALL_OK=true

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$TARGET_DIR/$file" ]; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file (faltante)"
        ALL_OK=false
    fi
done

echo ""
if [ "$ALL_OK" = true ]; then
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}✓ Instalación completada exitosamente!${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo "Próximos pasos:"
    echo "1. Abra su navegador e ingrese a GLPI"
    echo "2. Vaya a: Configuración > Complementos > Instalado"
    echo "3. Busque 'Email Approval' en la lista"
    echo "4. Haga clic en 'Instalar' para activar el plugin"
    echo "5. Configure el email del aprobador en Configuración > Complementos > Email Approval"
    echo ""
    echo "Ubicación del plugin: $TARGET_DIR"
else
    echo -e "${RED}⚠ Advertencia: Faltan algunos archivos necesarios${NC}"
    exit 1
fi
