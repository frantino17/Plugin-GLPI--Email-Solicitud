# Cómo Instalar el Plugin Email Approval en GLPI

## Problema Identificado

El plugin no aparece en GLPI porque está en una ubicación incorrecta. Los plugins de GLPI deben estar en el directorio `plugins` de la instalación de GLPI.

## Cambios Realizados

✅ **Agregado funciones requeridas en hook.php:**
- `plugin_emailapproval_install()` - Función obligatoria para instalar el plugin
- `plugin_emailapproval_uninstall()` - Función obligatoria para desinstalar el plugin

Estas funciones son **requeridas por GLPI** según la documentación oficial:
https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html#hook-php

## Solución: Dos Métodos de Instalación

### Método 1: Script Automático (Recomendado)

```bash
cd "/home/pc/Documentos/Plugin GLPI/emailapproval"
./INSTALL_PLUGIN.sh
```

El script automáticamente:
- Detecta la ubicación de GLPI
- Copia el plugin al directorio correcto
- Establece los permisos apropiados
- Verifica la estructura del plugin

### Método 2: Instalación Manual

1. **Encontrar el directorio de GLPI:**
   ```bash
   # Ubicaciones comunes:
   ls -la /var/www/html/glpi/plugins
   ls -la /var/www/glpi/plugins
   ls -la /usr/share/glpi/plugins
   ls -la /opt/glpi/plugins
   ```

2. **Copiar el plugin:**
   ```bash
   sudo cp -r "/home/pc/Documentos/Plugin GLPI/emailapproval" /ruta/a/glpi/plugins/
   ```

3. **Establecer permisos:**
   ```bash
   sudo chown -R www-data:www-data /ruta/a/glpi/plugins/emailapproval
   sudo chmod -R 755 /ruta/a/glpi/plugins/emailapproval
   ```
   
   *Nota: El usuario puede ser `www-data`, `apache`, o `nginx` dependiendo de tu configuración.*

## Activar el Plugin en GLPI

Una vez copiado el plugin:

1. Abre tu navegador web
2. Ingresa a GLPI
3. Ve a: **Configuración → Complementos → Instalado**
4. Busca **"Email Approval"** en la lista de plugins
5. Haz clic en el botón **"Instalar"**
6. Una vez instalado, haz clic en **"Activar"**

## Configurar el Plugin

Después de instalarlo y activarlo:

1. Ve a: **Configuración → Complementos → Email Approval**
2. Configura los siguientes campos:
   - **Email del aprobador**: Email del directivo que aprobará las solicitudes
   - **Horas de expiración del token**: Por defecto 48 horas
   - **Nombre del ticket**: "Solicitud de correo electrónico institucional"
   - **Estado de aprobación**: Estado al aprobar (ej: 5 - Resuelto)
   - **Estado de rechazo**: Estado al rechazar (ej: 6 - Cerrado)

## Verificar que Funciona

1. **Verificar que el plugin aparece:**
   - Configuración → Complementos → Instalado
   - Debe aparecer "Email Approval" con estado "Instalado"

2. **Crear un ticket de prueba:**
   - Crear un nuevo ticket con el nombre exacto: "Solicitud de correo electrónico institucional"
   - Verificar que se envíe un email de aprobación

3. **Ver logs:**
   ```bash
   # En el servidor GLPI
   tail -f /var/www/html/glpi/files/_log/php-errors.log
   tail -f /var/www/html/glpi/files/_log/sql-errors.log
   ```

## Estructura del Plugin (Verificación)

Archivos requeridos por GLPI:
```
emailapproval/
├── setup.php          ✅ (Configuración del plugin)
├── hook.php           ✅ (Hooks de instalación/desinstalación)
├── inc/
│   ├── approval.class.php   ✅
│   ├── crontask.class.php   ✅
│   └── menu.class.php       ✅
├── front/
│   ├── approve.php    ✅
│   ├── config.form.php ✅
│   └── request.form.php ✅
└── install/
    └── mysql/
        └── install.sql ✅
```

## Solución de Problemas

### El plugin no aparece en la lista

**Causa:** El plugin no está en el directorio correcto de GLPI.

**Solución:**
```bash
# Verificar que el plugin está en el directorio correcto
ls -la /ruta/a/glpi/plugins/emailapproval/setup.php

# Si no existe, copiar el plugin al directorio correcto
```

### Error "No se puede instalar"

**Causa:** Permisos incorrectos o falta de archivos requeridos.

**Solución:**
```bash
# Establecer permisos correctos
sudo chown -R www-data:www-data /ruta/a/glpi/plugins/emailapproval
sudo chmod -R 755 /ruta/a/glpi/plugins/emailapproval

# Verificar archivos requeridos
ls -la /ruta/a/glpi/plugins/emailapproval/setup.php
ls -la /ruta/a/glpi/plugins/emailapproval/hook.php
```

### El plugin no detecta tickets

**Causa:** Configuración incorrecta o nombre del ticket no coincide exactamente.

**Solución:**
1. Verificar configuración en: Configuración → Complementos → Email Approval
2. Asegurar que el nombre del ticket coincida EXACTAMENTE con el configurado
3. Verificar logs de PHP para errores

## Referencias

- [Documentación oficial de plugins GLPI](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html)
- [Requisitos de plugins](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/requirements.html)
- [Plugin de ejemplo](http://github.com/pluginsGLPI/example/)

## Soporte

Si tienes problemas, verifica:
1. Los logs de GLPI en `/var/www/html/glpi/files/_log/`
2. Los logs de Apache/Nginx
3. La versión de GLPI (debe ser 11.0.x)
4. Los permisos del directorio del plugin
