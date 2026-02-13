#!/bin/bash
#
# Script de diagnóstico para el plugin Email Approval
# Verifica la estructura y configuración del plugin
#

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}=== Diagnóstico del Plugin Email Approval ===${NC}\n"

# Función para verificar archivo
check_file() {
    local file=$1
    local desc=$2
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✓${NC} $desc"
        return 0
    else
        echo -e "  ${RED}✗${NC} $desc - ${RED}FALTANTE${NC}"
        return 1
    fi
}

# Función para verificar directorio
check_dir() {
    local dir=$1
    local desc=$2
    if [ -d "$dir" ]; then
        echo -e "  ${GREEN}✓${NC} $desc"
        return 0
    else
        echo -e "  ${RED}✗${NC} $desc - ${RED}FALTANTE${NC}"
        return 1
    fi
}

# 1. Verificar ubicación actual
echo -e "${YELLOW}1. Ubicación del plugin:${NC}"
CURRENT_DIR="$(pwd)"
echo "   $CURRENT_DIR"

if [[ "$CURRENT_DIR" == *"/plugins/emailapproval" ]]; then
    echo -e "   ${GREEN}✓ El plugin está en el directorio de plugins de GLPI${NC}"
    IN_GLPI=true
else
    echo -e "   ${YELLOW}⚠ El plugin NO está en el directorio de plugins de GLPI${NC}"
    echo -e "   ${YELLOW}  Debe estar en: /ruta/a/glpi/plugins/emailapproval${NC}"
    IN_GLPI=false
fi
echo ""

# 2. Verificar estructura de archivos
echo -e "${YELLOW}2. Estructura de archivos requeridos:${NC}"
ERRORS=0

# Archivos obligatorios
check_file "setup.php" "setup.php (obligatorio)" || ((ERRORS++))
check_file "hook.php" "hook.php (obligatorio)" || ((ERRORS++))

# Directorios
check_dir "inc" "inc/ (clases)" || ((ERRORS++))
check_dir "front" "front/ (páginas)" || ((ERRORS++))
check_dir "install" "install/ (SQL)" || ((ERRORS++))

# Clases
check_file "inc/approval.class.php" "inc/approval.class.php" || ((ERRORS++))
check_file "inc/crontask.class.php" "inc/crontask.class.php" || ((ERRORS++))
check_file "inc/menu.class.php" "inc/menu.class.php" || ((ERRORS++))

# Front files
check_file "front/approve.php" "front/approve.php" || ((ERRORS++))
check_file "front/config.form.php" "front/config.form.php" || ((ERRORS++))
check_file "front/request.form.php" "front/request.form.php" || ((ERRORS++))

# Install files
check_file "install/mysql/install.sql" "install/mysql/install.sql" || ((ERRORS++))
echo ""

# 3. Verificar funciones requeridas en setup.php
echo -e "${YELLOW}3. Funciones requeridas en setup.php:${NC}"
if [ -f "setup.php" ]; then
    grep -q "function plugin_init_emailapproval" setup.php && \
        echo -e "  ${GREEN}✓${NC} plugin_init_emailapproval()" || \
        { echo -e "  ${RED}✗${NC} plugin_init_emailapproval() - FALTANTE"; ((ERRORS++)); }
    
    grep -q "function plugin_version_emailapproval" setup.php && \
        echo -e "  ${GREEN}✓${NC} plugin_version_emailapproval()" || \
        { echo -e "  ${RED}✗${NC} plugin_version_emailapproval() - FALTANTE"; ((ERRORS++)); }
    
    grep -q "function plugin_emailapproval_check_prerequisites" setup.php && \
        echo -e "  ${GREEN}✓${NC} plugin_emailapproval_check_prerequisites()" || \
        { echo -e "  ${RED}✗${NC} plugin_emailapproval_check_prerequisites() - FALTANTE"; ((ERRORS++)); }
    
    grep -q "function plugin_emailapproval_check_config" setup.php && \
        echo -e "  ${GREEN}✓${NC} plugin_emailapproval_check_config()" || \
        { echo -e "  ${RED}✗${NC} plugin_emailapproval_check_config() - FALTANTE"; ((ERRORS++)); }
else
    echo -e "  ${RED}✗ No se puede verificar - setup.php no existe${NC}"
fi
echo ""

# 4. Verificar funciones requeridas en hook.php
echo -e "${YELLOW}4. Funciones requeridas en hook.php:${NC}"
if [ -f "hook.php" ]; then
    grep -q "function plugin_emailapproval_install" hook.php && \
        echo -e "  ${GREEN}✓${NC} plugin_emailapproval_install()" || \
        { echo -e "  ${RED}✗${NC} plugin_emailapproval_install() - FALTANTE"; ((ERRORS++)); }
    
    grep -q "function plugin_emailapproval_uninstall" hook.php && \
        echo -e "  ${GREEN}✓${NC} plugin_emailapproval_uninstall()" || \
        { echo -e "  ${RED}✗${NC} plugin_emailapproval_uninstall() - FALTANTE"; ((ERRORS++)); }
else
    echo -e "  ${RED}✗ No se puede verificar - hook.php no existe${NC}"
fi
echo ""

# 5. Verificar permisos (solo si está en GLPI)
if [ "$IN_GLPI" = true ]; then
    echo -e "${YELLOW}5. Permisos de archivos:${NC}"
    
    # Verificar propietario
    OWNER=$(stat -c '%U' setup.php 2>/dev/null)
    if [ "$OWNER" = "www-data" ] || [ "$OWNER" = "apache" ] || [ "$OWNER" = "nginx" ]; then
        echo -e "  ${GREEN}✓${NC} Propietario correcto: $OWNER"
    else
        echo -e "  ${YELLOW}⚠${NC} Propietario: $OWNER (debería ser www-data, apache o nginx)"
        ((ERRORS++))
    fi
    
    # Verificar permisos de lectura
    if [ -r "setup.php" ]; then
        echo -e "  ${GREEN}✓${NC} Archivos legibles"
    else
        echo -e "  ${RED}✗${NC} Sin permisos de lectura"
        ((ERRORS++))
    fi
    echo ""
fi

# 6. Buscar instalación de GLPI (si no está en plugins)
if [ "$IN_GLPI" = false ]; then
    echo -e "${YELLOW}6. Buscando instalación de GLPI:${NC}"
    
    GLPI_FOUND=false
    GLPI_PATHS=(
        "/var/www/html/glpi"
        "/var/www/glpi"
        "/usr/share/glpi"
        "/opt/glpi"
        "/srv/www/glpi"
    )
    
    for path in "${GLPI_PATHS[@]}"; do
        if [ -d "$path/plugins" ]; then
            echo -e "  ${GREEN}✓${NC} GLPI encontrado en: $path"
            GLPI_FOUND=true
            break
        fi
    done
    
    if [ "$GLPI_FOUND" = false ]; then
        echo -e "  ${YELLOW}⚠${NC} No se encontró GLPI en ubicaciones comunes"
        echo -e "     Busque manualmente el directorio que contiene 'index.php' y 'plugins/'"
    fi
    echo ""
fi

# Resumen final
echo -e "${BLUE}========================================${NC}"
if [ $ERRORS -eq 0 ] && [ "$IN_GLPI" = true ]; then
    echo -e "${GREEN}✓ TODO CORRECTO - El plugin está listo${NC}"
    echo -e "${GREEN}========================================${NC}"
    echo ""
    echo "El plugin debería aparecer en:"
    echo "  GLPI → Configuración → Complementos → Instalado"
    echo ""
    echo "Si no aparece, intente:"
    echo "  1. Limpiar caché de GLPI"
    echo "  2. Reiniciar servidor web (apache2/nginx)"
    echo "  3. Verificar logs de GLPI en /var/www/html/glpi/files/_log/"
elif [ "$IN_GLPI" = false ]; then
    echo -e "${YELLOW}⚠ ACCIÓN REQUERIDA${NC}"
    echo -e "${YELLOW}========================================${NC}"
    echo ""
    echo "El plugin debe copiarse al directorio de plugins de GLPI."
    echo ""
    echo "Ejecute el script de instalación:"
    echo -e "  ${GREEN}./INSTALL_PLUGIN.sh${NC}"
    echo ""
    echo "O copie manualmente:"
    echo "  sudo cp -r $(pwd) /ruta/a/glpi/plugins/"
    echo "  sudo chown -R www-data:www-data /ruta/a/glpi/plugins/emailapproval"
    echo "  sudo chmod -R 755 /ruta/a/glpi/plugins/emailapproval"
else
    echo -e "${RED}✗ ERRORES ENCONTRADOS: $ERRORS${NC}"
    echo -e "${RED}========================================${NC}"
    echo ""
    echo "Corrija los errores indicados arriba antes de instalar el plugin."
fi
echo ""
