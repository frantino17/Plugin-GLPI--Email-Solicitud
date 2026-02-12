# Manual de Instalación y Configuración Rápida

## Instalación en 5 Minutos

### Paso 1: Copiar archivos
```bash
cd /var/www/html/glpi/plugins/
cp -r /path/to/emailapproval ./
chown -R www-data:www-data emailapproval
```

### Paso 2: Instalar desde GLPI
1. Login como administrador
2. Ir a: **Configuración → Plugins**
3. Buscar "Email Approval"
4. Clic en **Instalar** → **Activar**

### Paso 3: Configurar email del aprobador
1. Ir a: **Configuración → General → Email Approval**
2. Introducir email: `director@example.com`
3. Guardar

### Paso 4: Configurar cron
```bash
# Editar crontab del usuario web
crontab -e -u www-data

# Añadir línea:
*/5 * * * * php /var/www/html/glpi/front/cron.php &>/dev/null
```

### Paso 5: Probar
1. Crear ticket con nombre: **"Solicitud de correo electrónico institucional"**
2. Verificar que se envía email al directivo
3. Revisar logs en `/var/www/html/glpi/files/_log/`

## Configuración SMTP

Si los emails no se envían, verificar configuración SMTP:

1. **Configuración → Notificaciones → Configuración de seguimiento por correo**
2. Configurar:
   - Servidor SMTP
   - Puerto (587 para TLS, 465 para SSL)
   - Usuario y contraseña
   - Habilitar autenticación

## Probar envío de email

```php
// En GLPI: Configuración → Notificaciones → Email
// Botón "Enviar email de prueba"
```

## Estados de Ticket en GLPI 11

Estados predeterminados (pueden variar según configuración):

| ID | Estado |
|----|--------|
| 1  | Nuevo |
| 2  | Asignado (en curso) |
| 3  | Planificado |
| 4  | En espera |
| 5  | Resuelto |
| 6  | Cerrado |

Configurar en el plugin según necesidades.

## Verificación Rápida

### Verificar instalación
```sql
SHOW TABLES LIKE 'glpi_plugin_emailapproval%';
```

### Ver configuración
```sql
SELECT * FROM glpi_configs WHERE context = 'plugin:emailapproval';
```

### Ver aprobaciones pendientes
```sql
SELECT * FROM glpi_plugin_emailapproval_approvals WHERE status = 'pending';
```

## Solución Rápida de Problemas

### No detecta tickets
✅ Verificar nombre EXACTO del ticket (mayúsculas, tildes, espacios)

### No envía emails
✅ Probar email desde Notificaciones de GLPI
✅ Revicar cola de correos en GLPI

### Recordatorios no funcionan
✅ Verificar cron está ejecutándose
✅ Activar tarea "SendReminders" en Acciones automáticas

## Desinstalación

1. **Configuración → Plugins → Email Approval**
2. Clic en **Desactivar** → **Desinstalar**
3. Eliminar carpeta: `rm -rf /var/www/html/glpi/plugins/emailapproval`

## Soporte Rápido

- 📧 Email: soporte@example.com
- 🐛 Issues: https://github.com/yourrepo/emailapproval/issues
- 📚 Docs: https://github.com/yourrepo/emailapproval/wiki
