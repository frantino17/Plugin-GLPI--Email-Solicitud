# Plugin Email Approval para GLPI 11

Plugin profesional para GLPI 11 que automatiza el proceso de aprobación externa de solicitudes de correo electrónico institucional mediante enlaces únicos y seguros enviados por email.

## 🎯 Características Principales

- ✅ **Detección automática** de tickets con nombre específico
- ✅ **Formulario manual** para técnicos L1 (NUEVO v1.1)
- ✅ **Email HTML profesional** con diseño moderno (NUEVO v1.1)
- ✅ **Datos del docente** almacenados en BD (NUEVO v1.1)
- 🔐 **Tokens criptográficamente seguros** (256 bits)
- 📧 **Emails automatizados** con enlaces únicos
- ⏰ **Sistema de recordatorios** automáticos a las 48 horas
- 📊 **Auditoría completa** de todas las acciones
- 🚫 **Prevención de reutilización** de tokens
- 🌐 **Endpoint público seguro** sin necesidad de login
- 🔄 **Actualización automática** del estado del ticket
- 📝 **Registro en el historial** del ticket

## 🆕 Novedades en v1.1

### 📝 Formulario Manual de Solicitud
Ahora los técnicos L1 pueden crear solicitudes manualmente a través de un formulario web completo:

- **Campos del docente**: nombre, legajo, email deseado
- **Departamento/Área** responsable
- **Email del responsable** aprobador (editable)
- **Observaciones** opcionales
- **Interfaz moderna** con validación en tiempo real
- **Creación automática** de ticket en GLPI

**Acceso:** Asistencia → Solicitud Correo Docente → Nueva Solicitud

### 📧 Email HTML Bonito y Profesional
El email enviado al responsable ahora es una plantilla HTML moderna:

- ✨ Diseño con gradientes de colores institucionales
- 📋 Tabla organizada con datos del docente
- 🟢 Botón verde grande "APROBAR"
- 🔴 Botón rojo grande "RECHAZAR"
- 📱 Responsive (funciona perfectamente en móviles)
- ⚠️ Sección destacada con información importante
- 📄 Versión texto plano incluida (fallback)

Ver documentación completa en: **[MANUAL_REQUEST.md](MANUAL_REQUEST.md)**

## 📋 Requisitos

- GLPI >= 11.0.0
- PHP >= 7.4 con extensión `random_bytes`
- Servidor SMTP configurado en GLPI
- MySQL/MariaDB

## 🚀 Instalación

### 1. Descargar e instalar el plugin

```bash
cd /var/www/html/glpi/plugins/
git clone https://github.com/yourrepo/emailapproval.git
# O copiar manualmente la carpeta emailapproval
```

### 2. Activar el plugin desde GLPI

1. Acceder a **Configuración → Plugins**
2. Buscar "Email Approval"
3. Hacer clic en **Instalar**
4. Hacer clic en **Activar**

### 3. Configurar el plugin

1. Ir a **Configuración → General → Email Approval**
2. Configurar los siguientes parámetros:

```php
- approver_email: email del directivo externo
- token_expiry_hours: 48 (horas antes de expirar)
- reminder_hours: 48 (horas antes de enviar recordatorio)
- ticket_name_match: "Solicitud de correo electrónico institucional"
- approved_status: 5 (ID del estado "Resuelto")
- rejected_status: 6 (ID del estado "Cerrado")
```

### 4. Configurar tarea automática (Cron)

1. Ir a **Configuración → Acciones automáticas**
2. Buscar "SendReminders"
3. Configurar para ejecutarse cada hora
4. Activar la tarea

## 📖 Uso

### Método 1: Formulario Manual (NUEVO v1.1 - Recomendado)

#### Para solicitudes de correo institucional docente:

```
1. Login en GLPI como técnico L1
2. Ir a: Asistencia → Solicitud Correo Docente
3. Completar formulario:
   - Datos del docente (nombre, legajo, email deseado)
   - Departamento/Área responsable
   - Email del responsable aprobador
   - Observaciones (opcional)
4. Clic en "📤 Enviar Solicitud"
5. Sistema crea ticket y envía email automáticamente
```

**Ventajas:**
- ✅ Control total de los datos antes de enviar
- ✅ Email del responsable editable por solicitud
- ✅ Información del docente almacenada en BD
- ✅ Email HTML profesional y bonito
- ✅ Validación de campos en tiempo real

Ver guía completa: **[MANUAL_REQUEST.md](MANUAL_REQUEST.md)**

---

### Método 2: Detección Automática (Original v1.0)

#### Flujo de trabajo completo:

```
1. Usuario crea ticket "Solicitud de correo electrónico institucional"
   ↓
2. Plugin detecta el ticket automáticamente
   ↓
3. Se genera token seguro único de 64 caracteres
   ↓
4. Se guarda en base de datos con fecha de expiración (48h)
   ↓
5. Se envía email al directivo con:
   - Información del ticket
   - Enlace de APROBAR
   - Enlace de RECHAZAR
   ↓
6. Directivo hace clic en uno de los enlaces
   ↓
7. Sistema valida:
   - Token existe
   - No está expirado
   - No fue usado previamente
   ↓
8. Se actualiza estado del ticket automáticamente
   ↓
9. Se registra en auditoría (IP, fecha, email, decisión)
   ↓
10. Se añade seguimiento al ticket con la decisión
```

### Si no hay respuesta en 48 horas:

```
1. Tarea cron se ejecuta cada hora
   ↓
2. Detecta solicitudes pendientes > 48h
   ↓
3. Envía email de recordatorio automático
   ↓
4. Marca recordatorio como enviado
   ↓
5. Token sigue siendo válido hasta expiración
```

## 🏗️ Estructura del Plugin

```
emailapproval/
├── setup.php                          # Configuración principal del plugin
├── hook.php                           # Hooks para detectar tickets
├── install/
│   └── install.php                    # Script de instalación/desinstalación
├── inc/
│   ├── approval.class.php             # Clase principal de lógica de negocio
│   └── crontask.class.php             # Gestión de tareas automáticas
├── front/
│   └── approve.php                    # Endpoint público de aprobación
├── locales/
│   └── es_ES.po                       # Traducciones (opcional)
└── README.md                          # Esta documentación
```

## 🗄️ Estructura de Base de Datos

### Tabla: glpi_plugin_emailapproval_approvals

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| tickets_id | INT | ID del ticket asociado |
| token | VARCHAR(128) | Token único de 64 caracteres |
| approver_email | VARCHAR(255) | Email del aprobador |
| status | ENUM | pending/approved/rejected/expired |
| created_at | TIMESTAMP | Fecha de creación |
| expires_at | TIMESTAMP | Fecha de expiración |
| responded_at | TIMESTAMP | Fecha de respuesta |
| reminder_sent | TINYINT | ¿Recordatorio enviado? |
| reminder_sent_at | TIMESTAMP | Fecha de envío de recordatorio |
| ip_address | VARCHAR(45) | IP desde donde se respondió |
| user_agent | VARCHAR(255) | User Agent del navegador |

### Tabla: glpi_plugin_emailapproval_logs

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| approvals_id | INT | ID de la aprobación |
| tickets_id | INT | ID del ticket |
| action | VARCHAR(50) | Tipo de acción |
| message | TEXT | Descripción de la acción |
| ip_address | VARCHAR(45) | IP del cliente |
| user_agent | VARCHAR(255) | User Agent |
| created_at | TIMESTAMP | Fecha del registro |

## 🔒 Seguridad Implementada

### Generación de Tokens Seguros

```php
// Token de 256 bits (64 caracteres hexadecimales)
$token = bin2hex(random_bytes(32));
```

### Validaciones Implementadas

1. **Formato del token**: Debe ser hexadecimal de 64 caracteres
2. **Existencia**: El token debe existir en la base de datos
3. **Estado**: Debe estar en estado "pending"
4. **Expiración**: No debe haber pasado la fecha de expiración
5. **Un solo uso**: Una vez usado, no puede reutilizarse
6. **Registro de IP**: Se guarda la IP y User Agent de quien responde

### Prevención de Ataques

- ✅ Tokens no predecibles (criptográficamente seguros)
- ✅ No hay enumeración posible (tokens aleatorios)
- ✅ Validación estricta de formato
- ✅ Expiración temporal (48 horas)
- ✅ Un solo uso por token
- ✅ Auditoría completa de intentos
- ✅ Rate limiting recomendado en servidor web

## 📧 Ejemplo de Email Enviado

```
Asunto: [GLPI] Aprobación requerida: Solicitud de correo institucional

Estimado/a Director/a,

Se requiere su aprobación para la siguiente solicitud:

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TICKET #123
Título: Solicitud de correo electrónico institucional
Solicitante: Juan Pérez
Fecha: 12/02/2026 10:30
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Descripción:
Solicito correo institucional para nuevo empleado...

Por favor, indique su decisión haciendo clic en uno de los siguientes enlaces:

✓ APROBAR: https://glpi.example.com/plugins/emailapproval/front/approve.php?token=abc...&action=approve

✗ RECHAZAR: https://glpi.example.com/plugins/emailapproval/front/approve.php?token=abc...&action=reject

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
IMPORTANTE:
- Este enlace es único y de un solo uso
- Expira en 48 horas
- No comparta este enlace con terceros
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## 🎨 Interfaz de Usuario

Al hacer clic en el enlace, el directivo verá una página moderna y profesional con:

- **Página de éxito** (verde): Confirmación de aprobación/rechazo
- **Página de error** (roja): Token inválido, expirado o ya usado
- **Diseño responsive**: Compatible con móviles y tablets
- **Sin necesidad de login**: Acceso directo mediante token

## 🔧 Configuración Avanzada

### Personalizar estados del ticket

Editar en la configuración del plugin:

```php
'approved_status' => 5,  // Cambiar al ID del estado deseado
'rejected_status' => 6,  // Cambiar al ID del estado deseado
```

### Personalizar tiempos de expiración

```php
'token_expiry_hours' => 72,    // 3 días en lugar de 2
'reminder_hours' => 24,        // Recordatorio a las 24h
```

### Múltiples directivos

Para implementar aprobación con múltiples directivos, modificar:

```php
// En setup.php, cambiar:
'approver_email' => 'director1@example.com,director2@example.com'

// Y en approval.class.php, modificar createApprovalRequest para:
$emails = explode(',', $approver_email);
foreach ($emails as $email) {
   // Crear una aprobación por cada email
}
```

## 🐛 Debugging

### Activar logs de debug

```php
// En setup.php, añadir:
define('PLUGIN_EMAILAPPROVAL_DEBUG', true);
```

### Ver logs de auditoría

```sql
SELECT * FROM glpi_plugin_emailapproval_logs 
ORDER BY created_at DESC 
LIMIT 100;
```

### Verificar tokens pendientes

```sql
SELECT a.*, t.name 
FROM glpi_plugin_emailapproval_approvals a
JOIN glpi_tickets t ON t.id = a.tickets_id
WHERE a.status = 'pending'
AND a.expires_at > NOW();
```

## 🆘 Solución de Problemas

### El plugin no detecta los tickets

1. Verificar que el nombre del ticket coincida EXACTAMENTE
2. Revisar la configuración en `ticket_name_match`
3. Verificar logs: `tail -f /var/www/html/glpi/files/_log/php-errors.log`

### No se envían los emails

1. Verificar configuración SMTP en GLPI: **Configuración → Notificaciones**
2. Probar envío manual desde GLPI
3. Revisar cola de emails: **Administración → Colas de correo**
4. Verificar firewall del servidor

### Los recordatorios no se envían

1. Verificar que la tarea cron está activa
2. Ejecutar manualmente: **Configuración → Acciones automáticas → SendReminders → Ejecutar**
3. Verificar que el cron del sistema está configurado:

```bash
# Añadir al crontab del usuario web
*/5 * * * * php /var/www/html/glpi/front/cron.php &>/dev/null
```

### Token no válido

- El token puede haber expirado (48 horas)
- El token puede haber sido usado previamente
- El enlace puede estar incompleto (verificar que no se cortó)

## 📊 Estadísticas y Reportes

### Ver todas las aprobaciones

```sql
SELECT 
   t.id AS ticket_id,
   t.name AS ticket_name,
   a.approver_email,
   a.status,
   a.created_at,
   a.responded_at,
   TIMESTAMPDIFF(HOUR, a.created_at, a.responded_at) AS response_time_hours
FROM glpi_plugin_emailapproval_approvals a
JOIN glpi_tickets t ON t.id = a.tickets_id
ORDER BY a.created_at DESC;
```

### Tasa de aprobación

```sql
SELECT 
   status,
   COUNT(*) AS total,
   ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM glpi_plugin_emailapproval_approvals), 2) AS percentage
FROM glpi_plugin_emailapproval_approvals
GROUP BY status;
```

## 🤝 Contribución

Para contribuir al desarrollo:

1. Fork el repositorio
2. Crear una rama: `git checkout -b feature/nueva-caracteristica`
3. Commit cambios: `git commit -m 'Añadir nueva característica'`
4. Push: `git push origin feature/nueva-caracteristica`
5. Abrir Pull Request

## 📄 Licencia

Este plugin está licenciado bajo GPLv2+

## 👨‍💻 Autor

Senior PHP Developer - Especialista en GLPI 11

## 📞 Soporte

Para reportar bugs o solicitar características:
- GitHub Issues: https://github.com/yourrepo/emailapproval/issues
- Email: soporte@example.com

## 🔄 Changelog

### Versión 1.0.0 (2026-02-12)
- ✨ Lanzamiento inicial
- 🔐 Sistema de tokens seguros
- 📧 Emails automatizados
- ⏰ Sistema de recordatorios
- 📊 Auditoría completa
- 🌐 Endpoint público seguro
