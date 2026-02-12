# Flujo de Funcionamiento del Plugin

## 📊 Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────────┐
│                     INICIO DEL PROCESO                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 1. Usuario crea ticket en GLPI                                 │
│    Nombre: "Solicitud de correo electrónico institucional"     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. Hook plugin_emailapproval_item_add_ticket()                 │
│    - Se ejecuta automáticamente (hook de GLPI)                 │
│    - Compara nombre del ticket con configuración               │
│    - Si coincide → continúa, si no → termina                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. PluginEmailapprovalApproval::createApprovalRequest()        │
│    a) Validar email del aprobador                              │
│    b) Verificar que el ticket existe                           │
│    c) Comprobar que no existe aprobación pendiente             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 4. Generar token seguro                                        │
│    - bin2hex(random_bytes(32))                                 │
│    - Resultado: 64 caracteres hexadecimales                    │
│    - Ejemplo: a3f8b9c2d1e5f4g7h8i9j0k1l2m3n4o5p6q7r8s9t0...   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 5. Guardar en base de datos                                    │
│    Tabla: glpi_plugin_emailapproval_approvals                  │
│    - tickets_id: ID del ticket                                 │
│    - token: token generado                                     │
│    - approver_email: email del directivo                       │
│    - status: 'pending'                                         │
│    - expires_at: fecha actual + 48 horas                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 6. Construir URLs de aprobación                                │
│    Base: https://glpi.example.com/plugins/emailapproval/...    │
│    Aprobar: /front/approve.php?token=XXX&action=approve        │
│    Rechazar: /front/approve.php?token=XXX&action=reject        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 7. Enviar email al directivo                                   │
│    - Clase: GLPIMailer                                         │
│    - Contenido: Info del ticket + enlaces                      │
│    - Registro en log de auditoría                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 8. Añadir seguimiento al ticket                                │
│    - ITILFollowup                                              │
│    - Privado (solo staff)                                      │
│    - Mensaje: "Solicitud enviada a [email]"                    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
        ┌───────────────────┐   ┌──────────────────┐
        │ ESCENARIO A:      │   │ ESCENARIO B:     │
        │ Directivo responde│   │ Sin respuesta    │
        │ antes de 48h      │   │ en 48 horas      │
        └───────────────────┘   └──────────────────┘
                    │                   │
                    ▼                   ▼
        ┌───────────────────┐   ┌──────────────────────────┐
        │ 9A. Clic en enlace│   │ 9B. Tarea CRON se activa │
        │    approve.php    │   │     (cada hora)          │
        └───────────────────┘   └──────────────────────────┘
                    │                   │
                    ▼                   ▼
        ┌───────────────────┐   ┌──────────────────────────┐
        │ 10A. Validaciones │   │ 10B. Buscar pendientes   │
        │  - Token existe   │   │      > 48h sin respuesta │
        │  - No expirado    │   │      reminder_sent = 0   │
        │  - Estado pending │   └──────────────────────────┘
        │  - No reutilizado │               │
        └───────────────────┘               ▼
                    │           ┌──────────────────────────┐
                    ▼           │ 11B. Enviar recordatorio │
        ┌───────────────────┐   │      por email           │
        │ 11A. Actualizar BD│   │      reminder_sent = 1   │
        │  status: approved │   └──────────────────────────┘
        │  o rejected       │               │
        │  responded_at     │               │
        │  IP + User Agent  │               │
        └───────────────────┘               │
                    │                       │
                    ▼                       ▼
        ┌───────────────────┐   ┌──────────────────────────┐
        │ 12A. Actualizar   │   │ Directivo responde tarde │
        │      ticket       │   │ (pero antes de expirar)  │
        │  - Nuevo estado   │   └──────────────────────────┘
        │  - Seguimiento    │               │
        └───────────────────┘               │
                    │                       │
                    ▼                       ▼
        ┌───────────────────┐   ┌──────────────────────────┐
        │ 13A. Registro     │   │ Volver a ESCENARIO A     │
        │      auditoría    │   │ (pasos 9A-13A)           │
        └───────────────────┘   └──────────────────────────┘
                    │
                    ▼
        ┌───────────────────┐
        │ 14A. Mostrar      │
        │      página éxito │
        │      al directivo │
        └───────────────────┘
                    │
                    ▼
        ┌───────────────────────────────────┐
        │ FIN: Proceso completado           │
        │ - Token marcado como usado        │
        │ - No puede reutilizarse           │
        │ - Ticket actualizado              │
        │ - Auditoría registrada            │
        └───────────────────────────────────┘
```

## 🔒 Validaciones de Seguridad por Fase

### Fase 1: Creación del token
- ✅ Email válido (filter_var FILTER_VALIDATE_EMAIL)
- ✅ Ticket existe en BD
- ✅ No hay aprobación pendiente duplicada
- ✅ Token criptográficamente seguro (random_bytes)

### Fase 2: Procesamiento del token
- ✅ Formato hexadecimal de 64 caracteres (regex)
- ✅ Token existe en BD
- ✅ Estado es 'pending'
- ✅ No ha expirado (timestamp actual < expires_at)
- ✅ Registro de IP y User Agent
- ✅ Log de seguridad para intentos inválidos

### Fase 3: Actualización del ticket
- ✅ Ticket existe antes de actualizar
- ✅ Cambio de estado a configurado
- ✅ Seguimiento añadido con auditoría
- ✅ Token marcado como usado (no reutilizable)

## 📈 Eventos de Auditoría Registrados

| Acción | Descripción | Cuándo se registra |
|--------|-------------|-------------------|
| `created` | Solicitud creada | Al crear aprobación |
| `email_sent` | Email enviado | Tras enviar email exitoso |
| `email_failed` | Error al enviar | Si falla el envío |
| `approve` | Solicitud aprobada | Al aprobar |
| `reject` | Solicitud rechazada | Al rechazar |
| `expired` | Token expirado | Al intentar usar token expirado |
| `reminder_sent` | Recordatorio enviado | Tras enviar recordatorio |
| `security_invalid_token_format` | Formato inválido | Token con formato incorrecto |
| `security_token_not_found` | Token no existe | Token no encontrado en BD |
| `security_token_already_used` | Token ya usado | Intento de reutilización |

## 🎯 Puntos de Extensión

### Para múltiples aprobadores
Modificar en `inc/approval.class.php`:

```php
public static function createApprovalRequest($tickets_id, $approvers_emails) {
   $approvers = explode(',', $approvers_emails);
   foreach ($approvers as $email) {
      // Crear token individual por aprobador
      // Lógica: aprobar si todos aprueban, rechazar si uno rechaza
   }
}
```

### Para notificaciones adicionales
Añadir en `inc/approval.class.php`:

```php
private static function notifyTicketCreator($ticket, $status) {
   // Enviar email al solicitante informando decisión
}
```

### Para integración con API externa
Añadir webhook en `inc/approval.class.php`:

```php
private static function callWebhook($approval_data) {
   $ch = curl_init('https://api.example.com/webhook');
   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($approval_data));
   // ...
}
```

## 🔧 Variables de Entorno Recomendadas

Crear archivo `.env` (no incluir en repositorio):

```bash
EMAILAPPROVAL_DEBUG=false
EMAILAPPROVAL_LOG_LEVEL=info
EMAILAPPROVAL_TOKEN_LENGTH=32
EMAILAPPROVAL_MAX_ATTEMPTS=5
```

## 📊 Métricas Importantes a Monitorear

1. **Tasa de respuesta**
   - % de solicitudes respondidas vs expiradas

2. **Tiempo de respuesta promedio**
   - Tiempo entre creación y respuesta

3. **Intentos de tokens inválidos**
   - Posibles intentos de ataque

4. **Tasa de recordatorios**
   - Cuántas necesitan recordatorio

5. **Tasa de aprobación vs rechazo**
   - Estadística de decisiones

## 🚨 Alertas Recomendadas

- Más de 10 intentos con token inválido en 1 hora
- Token expirado sin respuesta (notificar administrador)
- Fallo en envío de emails (verificar SMTP)
- Cola de aprobaciones pendientes > 50

## 🎓 Flujo Simplificado para Usuario Final

1. **Usuario TI** crea ticket
2. **Sistema** envía email automático
3. **Directivo** recibe email
4. **Directivo** hace clic en APROBAR o RECHAZAR
5. **Sistema** actualiza ticket automáticamente
6. **Usuario TI** ve decisión en el ticket
7. **Fin del proceso**

Sin necesidad de:
- Login del directivo
- Conocimiento de GLPI
- Pasos manuales adicionales
- Comunicación externa al sistema
