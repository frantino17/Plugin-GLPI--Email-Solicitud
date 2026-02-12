# Buenas Prácticas y Seguridad

## 🔒 Seguridad Implementada

### 1. Generación de Tokens Seguros

```php
// ✅ CORRECTO - Criptográficamente seguro
$token = bin2hex(random_bytes(32)); // 256 bits

// ❌ INCORRECTO - NO usar
$token = md5(time()); // Predecible
$token = uniqid(); // No seguro
```

**Características del token:**
- 64 caracteres hexadecimales
- 256 bits de entropía
- Imposible de adivinar o generar por fuerza bruta
- Único garantizado por random_bytes()

### 2. Validación Estricta de Tokens

```php
// Validar formato
if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
   // Token inválido - registrar intento sospechoso
   return false;
}

// Validar existencia
$exists = DB::request(['FROM' => 'approvals', 'WHERE' => ['token' => $token]]);

// Validar estado
if ($approval['status'] !== 'pending') {
   // Ya fue usado - registrar intento de reutilización
   return false;
}

// Validar expiración
if (strtotime($approval['expires_at']) < time()) {
   // Expirado - marcar y rechazar
   return false;
}
```

### 3. Prevención de Reutilización

**Estrategia de un solo uso:**

```php
// Al usar el token, cambiar estado inmediatamente
UPDATE approvals 
SET status = 'approved', 
    responded_at = NOW()
WHERE id = X;

// Segundo intento fallará porque status != 'pending'
```

### 4. Registro de Auditoría Completo

**Información registrada:**
- IP del cliente (considerando proxies)
- User Agent del navegador
- Timestamp exacto de cada acción
- Resultado de la operación
- Intentos fallidos con detalles

```php
// Obtener IP real detrás de proxies
function getClientIP() {
   if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
   } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
      $ip = $_SERVER['HTTP_X_REAL_IP'];
   } else {
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
   }
   return trim($ip);
}
```

### 5. Expiración Temporal

**Por qué 48 horas:**
- Balance entre urgencia y disponibilidad
- Reduce ventana de exposición
- Evita tokens eternos olvidados

**Consideraciones:**
- Ajustable según política organizacional
- Recomendado: 24-72 horas
- No más de 1 semana

### 6. Protección del Endpoint Público

**Medidas implementadas:**
```php
// No requiere autenticación pero tiene validaciones
define('GLPI_ROOT', ...);
$SECURITY_STRATEGY = 'no_check';

// Validar parámetros obligatorios
if (empty($token) || empty($action)) {
   return error(400);
}

// Validar acción permitida
if (!in_array($action, ['approve', 'reject'])) {
   return error(400);
}

// Todas las validaciones de token
// Registro de auditoría completo
```

**Medidas adicionales recomendadas:**

```apache
# En .htaccess o configuración Apache
<Location "/plugins/emailapproval/front/approve.php">
   # Rate limiting
   Require all granted
   
   # Limitar a 10 peticiones por minuto desde misma IP
   # (requiere mod_ratelimit o mod_evasive)
</Location>
```

```nginx
# En configuración Nginx
location /plugins/emailapproval/front/approve.php {
   limit_req zone=approvals burst=10 nodelay;
}
```

## 🛡️ Prevención de Ataques

### Ataque: Enumeración de Tokens

**Vector:** Probar tokens aleatorios hasta encontrar uno válido

**Mitigación:**
- ✅ Tokens de 256 bits (2^256 combinaciones)
- ✅ Imposible enumerar en tiempo razonable
- ✅ Registro de intentos fallidos
- ✅ Rate limiting recomendado

**Cálculo:**
```
Tokens posibles: 2^256 = 1.15 × 10^77
Intentos por segundo: 1,000,000 (muy optimista)
Tiempo para probar todos: 3.65 × 10^63 años

Edad del universo: 1.38 × 10^10 años

Conclusión: IMPOSIBLE por fuerza bruta
```

### Ataque: Replay Attack

**Vector:** Reutilizar token capturado

**Mitigación:**
- ✅ Token cambia a estado 'approved'/'rejected' tras uso
- ✅ Segundo intento falla por validación de estado
- ✅ Registro de intento de reutilización
- ❌ No hay protección contra captura antes del primer uso (usar HTTPS)

### Ataque: Man-in-the-Middle

**Vector:** Interceptar email o conexión

**Mitigación:**
- ✅ Forzar HTTPS en servidor web
- ✅ HSTS (HTTP Strict Transport Security)
- ❌ Email sin cifrar (limitación de SMTP estándar)
- ℹ️ Considerar: S/MIME o PGP para emails críticos

```apache
# Forzar HTTPS
<VirtualHost *:80>
   Redirect permanent / https://glpi.example.com/
</VirtualHost>

# Configurar HSTS
<VirtualHost *:443>
   Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</VirtualHost>
```

### Ataque: SQL Injection

**Vector:** Inyectar SQL en parámetros

**Mitigación:**
- ✅ Uso de prepared statements (GLPI DB API)
- ✅ Validación de formato de token (regex)
- ✅ No construcción manual de queries
- ✅ Escapado automático de valores

```php
// ✅ CORRECTO - Query parametrizada
$DB->request([
   'FROM' => 'table',
   'WHERE' => ['token' => $token] // Escapado automático
]);

// ❌ INCORRECTO - NO usar
$query = "SELECT * FROM table WHERE token = '$token'";
```

### Ataque: XSS (Cross-Site Scripting)

**Vector:** Inyectar JavaScript en respuestas

**Mitigación:**
- ✅ htmlspecialchars() en todos los outputs
- ✅ Content-Security-Policy header recomendado
- ✅ No eval() de datos de usuario

```php
// ✅ CORRECTO
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// ❌ INCORRECTO
echo $user_input;
```

### Ataque: CSRF (Cross-Site Request Forgery)

**Vector:** Engañar al usuario para ejecutar acción no deseada

**Mitigación:**
- ✅ Token es secreto no predecible
- ✅ Acción explícita (clic en enlace)
- ℹ️ CSRF menos relevante para endpoint sin sesión

### Ataque: Timing Attack

**Vector:** Medir tiempo de respuesta para inferir información

**Mitigación:**
- ✅ Comparación de strings en tiempo constante (hash_equals)
- ℹ️ En este caso, token único hace timing attack poco útil

```php
// ✅ CORRECTO para comparar secrets
if (hash_equals($expected_token, $provided_token)) {
   // Válido
}

// ❌ INCORRECTO - vulnerable a timing
if ($expected_token === $provided_token) {
   // Vulnerable
}
```

## 📋 Checklist de Seguridad

### Pre-producción

- [ ] HTTPS forzado en servidor
- [ ] HSTS habilitado
- [ ] Rate limiting configurado
- [ ] Logs de auditoría funcionando
- [ ] Backup de base de datos configurado
- [ ] Monitoreo de intentos fallidos
- [ ] Política de expiración definida
- [ ] Email del aprobador validado

### Post-despliegue

- [ ] Probar flujo completo end-to-end
- [ ] Verificar emails llegan correctamente
- [ ] Validar tokens expiran correctamente
- [ ] Confirmar recordatorios se envían
- [ ] Revisar logs de auditoría
- [ ] Monitorear intentos de ataque
- [ ] Verificar performance bajo carga

## 🔍 Auditoría y Compliance

### Información registrada para auditoría

```sql
-- Quién: approver_email
-- Qué: action (approve/reject)
-- Cuándo: responded_at
-- Desde dónde: ip_address
-- Cómo: user_agent
-- Resultado: status

SELECT 
   tickets_id,
   approver_email,
   action,
   responded_at,
   ip_address,
   message
FROM glpi_plugin_emailapproval_logs
WHERE tickets_id = 123
ORDER BY created_at;
```

### Retención de logs

**Recomendación:** Mantener logs al menos 1 año

```sql
-- Limpiar logs antiguos (ejecutar mensualmente)
DELETE FROM glpi_plugin_emailapproval_logs
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Exportar auditoría para compliance

```sql
-- Exportar aprobaciones del último mes
SELECT 
   t.id AS ticket_id,
   t.name AS ticket_name,
   a.approver_email,
   a.status,
   a.created_at,
   a.responded_at,
   TIMESTAMPDIFF(HOUR, a.created_at, a.responded_at) AS response_time_hours,
   a.ip_address
FROM glpi_plugin_emailapproval_approvals a
JOIN glpi_tickets t ON t.id = a.tickets_id
WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
ORDER BY a.created_at DESC
INTO OUTFILE '/tmp/approvals_audit.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

## 🎯 Mejoras Futuras de Seguridad

### 1. Autenticación de dos factores para enlaces

```php
// Enviar código adicional por SMS
// Requiere ingresar código + clic en enlace
```

### 2. Geolocalización de IP

```php
// Alertar si IP está en país no esperado
// Usar servicios como MaxMind GeoIP
```

### 3. Firma digital de emails

```php
// Usar DKIM/SPF/DMARC
// Prevenir spoofing de emails
```

### 4. Webhooks seguros

```php
// Notificar a sistemas externos con firma HMAC
$signature = hash_hmac('sha256', $payload, $secret);
```

### 5. Logs centralizados

```php
// Enviar logs a Syslog, ELK Stack, o Splunk
// Para análisis forense avanzado
```

## 📖 Referencias y Estándares

- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **NIST Cryptography**: https://csrc.nist.gov/projects/cryptographic-standards-and-guidelines
- **CWE/SANS Top 25**: https://www.sans.org/top25-software-errors/
- **PHP Security Guide**: https://www.php.net/manual/en/security.php
- **GLPI Documentation**: https://glpi-project.org/documentation/

## 🚨 Contacto de Seguridad

Para reportar vulnerabilidades de seguridad:
- Email: security@example.com
- PGP Key: [fingerprint]
- Respuesta garantizada en 48 horas

**Responsible Disclosure Policy:**
- Reportar privadamente primero
- No divulgar públicamente hasta patch disponible
- Reconocimiento en Hall of Fame
