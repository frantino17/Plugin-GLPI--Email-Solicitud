# Resumen Ejecutivo del Plugin Email Approval

## 📦 Plugin: Email Approval para GLPI 11
**Versión:** 1.0.0  
**Licencia:** GPLv2+  
**Autor:** Senior PHP Developer

---

## 🎯 Objetivo

Automatizar el proceso de aprobación externa de solicitudes de correo electrónico institucional mediante enlaces únicos y seguros enviados por email a un directivo que NO tiene acceso a GLPI.

---

## ✨ Características Principales

| Característica | Descripción |
|----------------|-------------|
| 🔍 **Detección automática** | Detecta tickets con nombre específico |
| 🔐 **Tokens seguros** | Tokens criptográficos de 256 bits |
| 📧 **Emails automatizados** | Envío automático con enlaces únicos |
| ⏰ **Recordatorios** | Sistema automático a las 48 horas |
| 📊 **Auditoría completa** | Registro de todas las acciones |
| 🚫 **Un solo uso** | Prevención de reutilización |
| 🌐 **Endpoint público** | Sin necesidad de login |
| 🔄 **Actualización automática** | Cambio de estado del ticket |

---

## 📂 Estructura del Proyecto

```
emailapproval/
│
├── setup.php                          # ⚙️ Configuración principal
├── hook.php                           # 🎣 Hooks de GLPI
├── LICENSE                            # 📄 Licencia GPLv2+
│
├── inc/                               # 🧩 Clases PHP
│   ├── approval.class.php             # 💼 Lógica principal de negocio
│   └── crontask.class.php             # ⏱️ Tareas automáticas
│
├── front/                             # 🌐 Interfaces web
│   ├── approve.php                    # 🔓 Endpoint público (sin login)
│   └── config.form.php                # ⚙️ Configuración (admin)
│
├── install/                           # 📥 Scripts de instalación
│   ├── install.php                    # 🔧 Instalador PHP
│   └── mysql/
│       ├── install.sql                # 📊 SQL instalación
│       └── uninstall.sql              # 🗑️ SQL desinstalación
│
├── locales/                           # 🌍 Traducciones (futuro)
│
└── docs/                              # 📚 Documentación
    ├── README.md                      # 📖 Documentación principal
    ├── INSTALL.md                     # 🚀 Guía de instalación rápida
    ├── WORKFLOW.md                    # 📊 Flujo de funcionamiento
    ├── SECURITY.md                    # 🔒 Seguridad y buenas prácticas
    └── EXAMPLES.md                    # 🧪 Casos de prueba
```

---

## 🗄️ Base de Datos

### Tablas Creadas

#### 1. `glpi_plugin_emailapproval_approvals`
**Propósito:** Almacenar solicitudes de aprobación y tokens

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único autoincremental |
| tickets_id | INT | Referencia al ticket |
| token | VARCHAR(128) | Token único de 64 caracteres |
| approver_email | VARCHAR(255) | Email del aprobador |
| status | ENUM | pending/approved/rejected/expired |
| created_at | TIMESTAMP | Fecha de creación |
| expires_at | TIMESTAMP | Fecha de expiración (48h) |
| responded_at | TIMESTAMP | Fecha de respuesta |
| reminder_sent | TINYINT | ¿Recordatorio enviado? |
| reminder_sent_at | TIMESTAMP | Fecha de recordatorio |
| ip_address | VARCHAR(45) | IP del aprobador |
| user_agent | VARCHAR(255) | Navegador usado |

#### 2. `glpi_plugin_emailapproval_logs`
**Propósito:** Auditoría completa de acciones

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| approvals_id | INT | Referencia a aprobación |
| tickets_id | INT | Referencia al ticket |
| action | VARCHAR(50) | Tipo de acción |
| message | TEXT | Descripción detallada |
| ip_address | VARCHAR(45) | IP del cliente |
| user_agent | VARCHAR(255) | User Agent |
| created_at | TIMESTAMP | Fecha de registro |

---

## 🔄 Flujo de Funcionamiento

```
┌──────────────────────────────────────────────────────┐
│ 1. Usuario crea ticket específico                   │
└──────────────┬───────────────────────────────────────┘
               ▼
┌──────────────────────────────────────────────────────┐
│ 2. Plugin detecta ticket automáticamente            │
│    (hook: plugin_emailapproval_item_add_ticket)     │
└──────────────┬───────────────────────────────────────┘
               ▼
┌──────────────────────────────────────────────────────┐
│ 3. Genera token seguro de 256 bits                  │
│    Token = bin2hex(random_bytes(32))                │
└──────────────┬───────────────────────────────────────┘
               ▼
┌──────────────────────────────────────────────────────┐
│ 4. Guarda en BD con expiración 48h                  │
└──────────────┬───────────────────────────────────────┘
               ▼
┌──────────────────────────────────────────────────────┐
│ 5. Envía email con enlaces APROBAR/RECHAZAR         │
└──────────────┬───────────────────────────────────────┘
               ▼
         ┌─────┴─────┐
         ▼           ▼
┌──────────────┐  ┌─────────────────────────┐
│ Responde     │  │ No responde en 48h      │
│ antes 48h    │  │ → Envía recordatorio    │
└──────┬───────┘  └─────────┬───────────────┘
       ▼                    ▼
┌──────────────────────────────────────────────────────┐
│ 6. Clic en enlace → Valida token                    │
└──────────────┬───────────────────────────────────────┘
               ▼
┌──────────────────────────────────────────────────────┐
│ 7. Actualiza ticket y registra auditoría            │
└──────────────┬───────────────────────────────────────┘
               ▼
┌──────────────────────────────────────────────────────┐
│ 8. FIN - Token marcado como usado                   │
└──────────────────────────────────────────────────────┘
```

---

## 🔐 Seguridad Implementada

### Generación de Tokens
```php
$token = bin2hex(random_bytes(32)); // 64 chars hex = 256 bits
```

### Validaciones
✅ Formato: Regex `/^[a-f0-9]{64}$/i`  
✅ Existencia: Query en BD  
✅ Estado: Debe ser 'pending'  
✅ Expiración: Verifica timestamp  
✅ Un solo uso: Cambia estado tras uso  
✅ Auditoría: Registra IP, User Agent, timestamp  

### Prevención de Ataques
- **Enumeración:** Imposible (2^256 combinaciones)
- **Replay:** Token de un solo uso
- **SQL Injection:** Prepared statements
- **XSS:** htmlspecialchars en outputs
- **CSRF:** Token único no predecible
- **MITM:** HTTPS obligatorio (configuración)

---

## 📋 Configuración Requerida

### En GLPI:
```
Configuración → General → Email Approval:
  - approver_email: director@example.com
  - token_expiry_hours: 48
  - reminder_hours: 48
  - ticket_name_match: "Solicitud de correo electrónico institucional"
  - approved_status: 5
  - rejected_status: 6
```

### Cron del sistema:
```bash
*/5 * * * * php /var/www/html/glpi/front/cron.php &>/dev/null
```

### Servidor web:
- HTTPS habilitado
- HSTS recomendado
- Rate limiting recomendado

---

## 📊 Métricas y Auditoría

### Datos registrados:
- ✅ Quién: Email del aprobador
- ✅ Qué: Acción (aprobar/rechazar)
- ✅ Cuándo: Timestamps exactos
- ✅ Desde dónde: IP + User Agent
- ✅ Resultado: Estado final

### Consultas útiles:
```sql
-- Ver aprobaciones pendientes
SELECT * FROM glpi_plugin_emailapproval_approvals WHERE status='pending';

-- Ver auditoría de un ticket
SELECT * FROM glpi_plugin_emailapproval_logs WHERE tickets_id=123;

-- Estadísticas
SELECT status, COUNT(*) FROM glpi_plugin_emailapproval_approvals GROUP BY status;
```

---

## 🚀 Instalación Rápida

```bash
# 1. Copiar plugin
cd /var/www/html/glpi/plugins/
git clone [repo] emailapproval

# 2. Permisos
chown -R www-data:www-data emailapproval

# 3. Instalar desde GLPI
# Configuración → Plugins → Email Approval → Instalar → Activar

# 4. Configurar email del aprobador
# Configuración → General → Email Approval

# 5. Configurar cron
crontab -e -u www-data
# Añadir: */5 * * * * php /var/www/html/glpi/front/cron.php
```

---

## ✅ Checklist de Deployment

### Pre-producción
- [ ] HTTPS configurado y forzado
- [ ] SMTP configurado en GLPI
- [ ] Email del aprobador validado
- [ ] Cron del sistema configurado
- [ ] Backup de BD configurado
- [ ] Pruebas end-to-end completadas

### Post-producción
- [ ] Monitoreo de logs activo
- [ ] Alertas configuradas
- [ ] Documentación entregada
- [ ] Capacitación a usuarios
- [ ] Plan de soporte definido

---

## 🐛 Troubleshooting Rápido

| Problema | Solución |
|----------|----------|
| No detecta tickets | Verificar nombre EXACTO del ticket |
| No envía emails | Verificar SMTP en GLPI |
| No envía recordatorios | Verificar tarea cron activa |
| Token inválido | Puede estar expirado o ya usado |
| Error 500 | Revisar logs PHP en files/_log/ |

---

## 📚 Documentación Completa

- **README.md**: Documentación principal y características
- **INSTALL.md**: Guía de instalación paso a paso
- **WORKFLOW.md**: Flujo detallado con diagramas
- **SECURITY.md**: Seguridad y buenas prácticas
- **EXAMPLES.md**: Casos de prueba y ejemplos

---

## 📞 Soporte

- 📧 Email: soporte@example.com
- 🐛 Issues: GitHub Issues
- 📚 Docs: Wiki del proyecto

---

## 📈 Roadmap Futuro

### v1.1.0
- [ ] Múltiples aprobadores
- [ ] Aprobación escalonada (jerarquía)
- [ ] Notificaciones al solicitante
- [ ] Dashboard de estadísticas

### v1.2.0
- [ ] API REST para integración
- [ ] Webhooks configurables
- [ ] Firma digital de emails
- [ ] Autenticación 2FA opcional

### v2.0.0
- [ ] Aprobaciones con comentarios
- [ ] Plantillas de email personalizables
- [ ] Integración con Active Directory
- [ ] Geolocalización de IP

---

## 🏆 Buenas Prácticas Aplicadas

✅ **Código limpio:** PSR-12, comentarios, nomenclatura clara  
✅ **Seguridad:** Tokens seguros, validaciones estrictas, auditoría  
✅ **Arquitectura:** Modular, extensible, mantenible  
✅ **Documentación:** Completa, ejemplos, diagramas  
✅ **Testing:** Casos de prueba definidos  
✅ **Performance:** Consultas optimizadas, índices en BD  
✅ **UX:** Interfaces amigables, mensajes claros  
✅ **DevOps:** Scripts de instalación, logs, monitoreo  

---

## 🎓 Conclusión

Este plugin implementa un **sistema profesional, seguro y automatizado** para la aprobación externa de solicitudes mediante enlaces únicos. Cumple con:

- ✅ Todos los requisitos funcionales
- ✅ Estándares de seguridad (OWASP)
- ✅ Buenas prácticas de desarrollo
- ✅ Documentación completa
- ✅ Facilidad de instalación y mantenimiento
- ✅ Trazabilidad y auditoría completa

**¡Listo para producción!** 🚀
