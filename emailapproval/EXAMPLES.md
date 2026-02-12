# Ejemplos de Uso y Casos de Prueba

## 🧪 Caso de Prueba 1: Flujo Normal - Aprobación

### Paso 1: Crear ticket
```
1. Login en GLPI como técnico
2. Ir a: Asistencia → Tickets → Crear ticket
3. Llenar formulario:
   - Título: "Solicitud de correo electrónico institucional"
   - Tipo: Solicitud
   - Categoría: (según configuración)
   - Descripción: "Necesito correo para nuevo empleado Juan Pérez"
4. Guardar
```

### Paso 2: Verificar procesamiento automático
```
✓ En el ticket aparece seguimiento automático:
  "Se ha enviado solicitud de aprobación a director@example.com"

✓ En base de datos:
  SELECT * FROM glpi_plugin_emailapproval_approvals 
  WHERE tickets_id = [ID del ticket];
  
  Resultado:
  - status: pending
  - token: [64 caracteres hex]
  - expires_at: [fecha actual + 48h]
```

### Paso 3: Directivo recibe email
```
Para: director@example.com
Asunto: [GLPI] Aprobación requerida: Solicitud de correo institucional
Contenido: [Ver ejemplo completo en README.md]
Enlaces:
  - APROBAR: https://glpi.example.com/plugins/emailapproval/...&action=approve
  - RECHAZAR: https://glpi.example.com/plugins/emailapproval/...&action=reject
```

### Paso 4: Directivo aprueba
```
1. Directivo hace clic en "APROBAR"
2. Navegador abre página de confirmación
3. Mensaje mostrado:
   ✓ Solicitud Aprobada
   "Solicitud aprobada correctamente"
   
   Información del ticket:
   - Ticket #123
   - Aprobador: director@example.com
   - Fecha: 12/02/2026 15:30
```

### Paso 5: Verificar actualización en GLPI
```
1. Volver al ticket en GLPI
2. Verificar:
   ✓ Estado cambiado a "Resuelto" (o según configuración)
   ✓ Nuevo seguimiento:
     "✓ SOLICITUD APROBADA
     El directivo ha aprobado esta solicitud.
     Aprobado por: director@example.com
     Fecha: 2026-02-12 15:30:00"
```

### Paso 6: Verificar auditoría
```sql
SELECT * FROM glpi_plugin_emailapproval_logs 
WHERE tickets_id = 123 
ORDER BY created_at;

Resultados esperados:
1. action: created | Solicitud de aprobación creada
2. action: email_sent | Email de aprobación enviado
3. action: approve | Solicitud approved por director@example.com
```

---

## 🧪 Caso de Prueba 2: Flujo con Recordatorio

### Escenario: Directivo no responde en 48 horas

### Paso 1-2: Igual que Caso 1

### Paso 3: Esperar 48 horas (o simular)
```sql
-- Para simular sin esperar, modificar fecha de creación
UPDATE glpi_plugin_emailapproval_approvals
SET created_at = DATE_SUB(NOW(), INTERVAL 49 HOUR)
WHERE id = [ID de la aprobación];
```

### Paso 4: Ejecutar tarea cron manualmente
```
1. Ir a: Configuración → Acciones automáticas
2. Buscar: "SendReminders"
3. Clic en "Ejecutar"
```

### Paso 5: Verificar recordatorio enviado
```
✓ Email de recordatorio recibido:
  Asunto: [GLPI] RECORDATORIO: Aprobación pendiente
  Contenido: "⚠️ Expira en: X horas"

✓ Base de datos actualizada:
  SELECT reminder_sent, reminder_sent_at 
  FROM glpi_plugin_emailapproval_approvals 
  WHERE id = [ID];
  
  reminder_sent: 1
  reminder_sent_at: [timestamp actual]
```

### Paso 6: Directivo responde después del recordatorio
```
- Sigue Caso 1 desde Paso 4
- Token sigue siendo válido si no ha expirado
```

---

## 🧪 Caso de Prueba 3: Token Expirado

### Escenario: Directivo intenta usar token después de 48 horas

### Paso 1: Crear ticket y esperar expiración
```sql
-- Simular expiración
UPDATE glpi_plugin_emailapproval_approvals
SET created_at = DATE_SUB(NOW(), INTERVAL 50 HOUR),
    expires_at = DATE_SUB(NOW(), INTERVAL 2 HOUR)
WHERE id = [ID];
```

### Paso 2: Directivo hace clic en enlace
```
Resultado:
⚠️ No se pudo procesar la solicitud
"Este enlace ha expirado (más de 48 horas)"

Código de error: 403

Posibles causas:
- El enlace ya fue utilizado anteriormente
- El enlace ha expirado (más de 48 horas)
- El enlace está incompleto o fue modificado
- La solicitud ya fue procesada por otro medio
```

### Paso 3: Verificar base de datos
```sql
SELECT status FROM glpi_plugin_emailapproval_approvals 
WHERE id = [ID];

Resultado: expired
```

### Paso 4: Verificar log de auditoría
```sql
SELECT action, message FROM glpi_plugin_emailapproval_logs 
WHERE approvals_id = [ID] 
ORDER BY created_at DESC LIMIT 1;

Resultado:
action: expired
message: "Token expirado al intentar usarlo"
```

---

## 🧪 Caso de Prueba 4: Rechazo de Solicitud

### Similar a Caso 1, pero directivo hace clic en "RECHAZAR"

### Resultado esperado:
```
✗ Solicitud Rechazada
"Solicitud rechazada correctamente"

Ticket actualizado:
- Estado: Cerrado (o según configuración)
- Seguimiento:
  "✗ SOLICITUD RECHAZADA
  El directivo ha rechazado esta solicitud.
  Rechazado por: director@example.com
  Fecha: 2026-02-12 16:00:00"
```

---

## 🧪 Caso de Prueba 5: Intento de Reutilización

### Escenario: Directivo intenta usar el mismo enlace dos veces

### Paso 1: Aprobar normalmente (Caso 1)

### Paso 2: Hacer clic en el mismo enlace nuevamente
```
Resultado:
⚠️ No se pudo procesar la solicitud
"Este enlace ya ha sido utilizado anteriormente"

Código de error: 403
```

### Paso 3: Verificar log de seguridad
```sql
SELECT action, message FROM glpi_plugin_emailapproval_logs 
WHERE action LIKE 'security_%' 
ORDER BY created_at DESC LIMIT 1;

Resultado:
action: security_token_already_used
message: "Estado actual: approved"
```

---

## 🧪 Caso de Prueba 6: Token Inválido

### Escenario: Alguien intenta acceder con token modificado

### Paso 1: Acceder con token inválido
```
URL: https://glpi.example.com/plugins/emailapproval/front/approve.php
     ?token=XXXINVALIDOXXX&action=approve
```

### Paso 2: Ver respuesta
```
⚠️ No se pudo procesar la solicitud
"Token no válido"
```

### Paso 3: Verificar log de seguridad
```sql
SELECT * FROM glpi_plugin_emailapproval_logs 
WHERE action = 'security_invalid_token_format'
ORDER BY created_at DESC LIMIT 1;

Resultado registra:
- IP del atacante
- User Agent
- Token intentado
- Timestamp
```

---

## 🧪 Caso de Prueba 7: Ticket con Nombre Diferente

### Escenario: Verificar que solo se procesen tickets con nombre exacto

### Paso 1: Crear ticket con nombre similar pero diferente
```
Títulos que NO deben activar el plugin:
❌ "solicitud de correo electrónico institucional" (minúscula)
❌ "Solicitud de correo electronico institucional" (sin tilde)
❌ "Solicitud de correo institucional" (incompleto)
❌ "Solicitud de correo electrónico" (incompleto)

Título que SÍ debe activar:
✓ "Solicitud de correo electrónico institucional" (exacto)
```

### Paso 2: Verificar base de datos
```sql
-- No debe haber registro para los tickets incorrectos
SELECT COUNT(*) FROM glpi_plugin_emailapproval_approvals 
WHERE tickets_id IN ([IDs de tickets incorrectos]);

Resultado: 0
```

---

## 📊 Consultas SQL Útiles para Pruebas

### Ver todas las aprobaciones pendientes
```sql
SELECT 
   a.id,
   a.tickets_id,
   t.name AS ticket_name,
   a.approver_email,
   a.created_at,
   a.expires_at,
   TIMESTAMPDIFF(HOUR, NOW(), a.expires_at) AS hours_remaining
FROM glpi_plugin_emailapproval_approvals a
JOIN glpi_tickets t ON t.id = a.tickets_id
WHERE a.status = 'pending'
ORDER BY a.expires_at;
```

### Ver historial completo de un ticket
```sql
SELECT 
   l.created_at,
   l.action,
   l.message,
   l.ip_address
FROM glpi_plugin_emailapproval_logs l
WHERE l.tickets_id = 123
ORDER BY l.created_at;
```

### Estadísticas de tiempo de respuesta
```sql
SELECT 
   AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) AS avg_hours,
   MIN(TIMESTAMPDIFF(HOUR, created_at, responded_at)) AS min_hours,
   MAX(TIMESTAMPDIFF(HOUR, created_at, responded_at)) AS max_hours
FROM glpi_plugin_emailapproval_approvals
WHERE status IN ('approved', 'rejected');
```

### Identificar posibles ataques
```sql
SELECT 
   ip_address,
   COUNT(*) AS attempts,
   MAX(created_at) AS last_attempt
FROM glpi_plugin_emailapproval_logs
WHERE action LIKE 'security_%'
GROUP BY ip_address
HAVING attempts > 5
ORDER BY attempts DESC;
```

---

## 🐛 Debug: Activar Modo Verbose

### En setup.php, añadir:
```php
define('PLUGIN_EMAILAPPROVAL_DEBUG', true);

// Luego en approval.class.php, añadir logs:
if (defined('PLUGIN_EMAILAPPROVAL_DEBUG') && PLUGIN_EMAILAPPROVAL_DEBUG) {
   error_log("Email Approval: Token generated: " . $token);
   error_log("Email Approval: Email sent to: " . $approver_email);
}
```

### Ver logs en tiempo real:
```bash
tail -f /var/www/html/glpi/files/_log/php-errors.log | grep "Email Approval"
```

---

## 🎯 Checklist de Pruebas Completas

- [ ] Ticket con nombre exacto crea aprobación
- [ ] Ticket con nombre diferente NO crea aprobación
- [ ] Email se envía correctamente
- [ ] Enlaces en email funcionan
- [ ] Aprobación actualiza ticket correctamente
- [ ] Rechazo actualiza ticket correctamente
- [ ] Token no puede reutilizarse
- [ ] Token expira después de 48 horas
- [ ] Recordatorio se envía a las 48 horas
- [ ] Token inválido se rechaza
- [ ] Auditoría registra todas las acciones
- [ ] IP y User Agent se registran
- [ ] Múltiples aprobaciones no se duplican
- [ ] Tarea cron funciona automáticamente
- [ ] Configuración se puede modificar
- [ ] Estadísticas se muestran correctamente
- [ ] Desinstalación limpia todas las tablas

---

## 📞 Reporte de Resultados de Prueba

### Formato de reporte:
```
PRUEBA: [Nombre del caso de prueba]
FECHA: [Fecha de ejecución]
RESULTADO: ✓ PASS / ✗ FAIL
DETALLES: [Observaciones]
EVIDENCIA: [Screenshots, logs, SQL queries]
```

### Ejemplo:
```
PRUEBA: Caso 1 - Flujo Normal Aprobación
FECHA: 12/02/2026 14:30
RESULTADO: ✓ PASS
DETALLES: 
  - Ticket #123 creado correctamente
  - Email enviado a director@example.com
  - Token generado: 8a3f2b... (64 chars)
  - Aprobación completada en 15 minutos
  - Ticket actualizado a estado Resuelto
EVIDENCIA:
  - Screenshot: ticket_123_before.png
  - Screenshot: email_received.png
  - Screenshot: approval_page.png
  - Screenshot: ticket_123_after.png
  - SQL: SELECT * FROM logs WHERE tickets_id=123
```
