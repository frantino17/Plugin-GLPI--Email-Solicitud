# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.1.0] - 2026-02-12

### 🎉 Solicitud Manual de Correo Institucional Docente

Segunda versión con mejoras importantes para el proceso de solicitud.

### ✨ Añadido

#### Formulario Manual de Solicitud
- **Interfaz web completa** para que técnicos L1 creen solicitudes manualmente
- **Campos específicos para docentes**:
  - Nombre completo del docente
  - Número de legajo institucional
  - Email institucional deseado
  - Departamento/Área responsable
  - Email del responsable aprobador (editable por solicitud)
  - Campo de observaciones opcional
- **Validación completa** de todos los campos obligatorios
- **Diseño moderno** con gradientes y colores profesionales
- **Responsive design** compatible con móviles y tablets
- **Creación automática** de ticket en GLPI al enviar
- **Integración con menú** de GLPI (Asistencia → Solicitud Correo Docente)

#### Email HTML Profesional
- **Plantilla HTML bonita** con diseño institucional moderno
- **Header destacado** con gradiente morado-azul
- **Tabla organizada** con datos del docente
- **Botones grandes y visibles** (APROBAR verde / RECHAZAR rojo)
- **Sección de información importante** destacada en amarillo
- **Footer profesional** con fecha y hora
- **Responsive** para todos los clientes de email
- **Versión texto plano** incluida como fallback
- **Colores personalizables** según identidad institucional

#### Base de Datos
- **Nuevos campos** en tabla `glpi_plugin_emailapproval_approvals`:
  - `teacher_name` VARCHAR(255) - Nombre completo del docente
  - `teacher_legajo` VARCHAR(50) - Número de legajo (indexado)
  - `teacher_email` VARCHAR(255) - Email solicitado
  - `department_name` VARCHAR(255) - Departamento/Área
- **Script de actualización** para instalaciones existentes (update_1.1.sql)
- **Índice adicional** en campo `teacher_legajo` para búsquedas rápidas

#### Código PHP
- **Método `createApprovalRequestManual()`** - Crear solicitud con datos del docente
- **Método `sendApprovalEmailDocente()`** - Enviar email con plantilla HTML bonita
- **Método `getEmailTemplate()`** - Generar plantilla HTML profesional (200+ líneas)
- **Método `getEmailTemplatePlainText()`** - Versión texto plano del email
- **Clase `PluginEmailapprovalMenu`** - Integración con menú de GLPI
- **Archivo `front/request.form.php`** - Formulario completo (280+ líneas)

#### Documentación
- **MANUAL_REQUEST.md** (300+ líneas) - Guía completa del formulario manual
  - Cómo usar el formulario
  - Vista previa del email
  - Consultas SQL útiles
  - Casos de uso
  - Troubleshooting
- **UPDATE_NOTES.txt** - Resumen visual de mejoras y actualización

### 🎨 Mejorado

#### Experiencia de Usuario
- **Proceso más intuitivo** para técnicos L1
- **Control total** sobre los datos antes de enviar
- **Validación en tiempo real** de campos del formulario
- **Mensajes de error claros** y específicos
- **Confirmación visual** al enviar solicitud

#### Email
- **Diseño profesional** reemplaza email texto plano anterior
- **Información más clara** y organizada en tablas
- **Botones más visibles** para aprobar/rechazar
- **Mejor compatibilidad** con clientes de email móviles
- **Mensaje personalizado** según tipo de solicitud (manual vs automática)

#### Trazabilidad
- **Datos del docente** almacenados en BD para estadísticas
- **Búsquedas por legajo** ahora posibles
- **Reportes por departamento** habilitados
- **Información completa** en seguimientos del ticket

### 📊 Estadísticas

- Nuevos archivos: 3
- Archivos modificados: 4
- Líneas de código añadidas: ~600
- Métodos PHP nuevos: 5
- Campos de BD nuevos: 4
- Documentación añadida: ~300 líneas

### 🔄 Migración desde v1.0

#### Opción A: Reinstalación (sin datos)
1. Desactivar y desinstalar plugin
2. Copiar nuevos archivos
3. Instalar y activar
4. Reconfigurar

#### Opción B: Actualización (conservar datos)
1. Backup de BD
2. Copiar nuevos archivos
3. Ejecutar `install/mysql/update_1.1.sql`
4. Desactivar y activar plugin

### 📝 Notas de Migración

- Compatible con datos existentes de v1.0
- No se pierden aprobaciones anteriores
- Nuevos campos son opcionales para registros antiguos
- Script de actualización incluido
- Backward compatible con sistema automático original

### 🐛 Corregido

- Compatibilidad con clientes de email que no soportan HTML
- Validación mejorada de emails con dominios especiales
- Manejo de caracteres especiales en nombres de docentes
- Escapado correcto de HTML en plantilla de email

### 🔒 Seguridad

- Validación estricta de todos los campos del formulario
- Sanitización de datos antes de almacenar en BD
- Escapado de HTML en email para prevenir XSS
- Validación de formato de email con filtros PHP
- Protección contra SQL Injection mantenida

### 👥 Contribuidores

- Senior PHP Developer - Desarrollo completo de v1.1

---

## [1.0.0] - 2026-02-12

### 🎉 Lanzamiento Inicial

Primera versión estable del plugin Email Approval para GLPI 11.

### ✨ Añadido

#### Características Principales
- **Detección automática** de tickets con nombre específico "Solicitud de correo electrónico institucional"
- **Generación de tokens seguros** usando `random_bytes()` (256 bits de entropía)
- **Envío automático de emails** con enlaces únicos de aprobación/rechazo
- **Sistema de recordatorios** automáticos a las 48 horas sin respuesta
- **Endpoint público seguro** (`front/approve.php`) sin necesidad de autenticación
- **Validación estricta** de tokens (formato, existencia, expiración, estado)
- **Prevención de reutilización** mediante cambio de estado tras uso
- **Auditoría completa** en tabla `glpi_plugin_emailapproval_logs`
- **Actualización automática** del estado del ticket según decisión
- **Registro en historial** del ticket con información de aprobación/rechazo

#### Componentes Técnicos
- `setup.php`: Configuración principal del plugin con hooks y constantes
- `hook.php`: Hook `item_add` para detectar creación de tickets
- `inc/approval.class.php`: Clase principal con toda la lógica de negocio (550+ líneas)
- `inc/crontask.class.php`: Gestión de tareas automáticas (cron)
- `front/approve.php`: Endpoint público con páginas HTML de éxito/error
- `front/config.form.php`: Panel de configuración para administradores
- `install/install.php`: Script de instalación automática
- `install/mysql/install.sql`: Script SQL de instalación manual
- `install/mysql/uninstall.sql`: Script SQL de desinstalación

#### Base de Datos
- Tabla `glpi_plugin_emailapproval_approvals`: Almacena solicitudes y tokens
  - Campos: id, tickets_id, token, approver_email, status, timestamps, IP, user_agent
  - Índices optimizados para búsquedas rápidas
- Tabla `glpi_plugin_emailapproval_logs`: Auditoría de todas las acciones
  - Campos: id, approvals_id, tickets_id, action, message, IP, user_agent, timestamp
  - Registro de eventos de seguridad

#### Seguridad
- Tokens criptográficamente seguros (64 caracteres hexadecimales)
- Validación de formato con expresiones regulares
- Protección contra SQL injection (prepared statements)
- Protección contra XSS (htmlspecialchars en outputs)
- Expiración temporal de tokens (48 horas configurable)
- Registro de IP y User Agent para auditoría
- Prevención de replay attacks (un solo uso)
- Logs de intentos fallidos para detección de ataques

#### Configuración
- Email del directivo aprobador
- Tiempo de expiración de tokens (default: 48 horas)
- Tiempo antes de enviar recordatorio (default: 48 horas)
- Nombre exacto del ticket a detectar
- Estado del ticket al aprobar (default: 5 - Resuelto)
- Estado del ticket al rechazar (default: 6 - Cerrado)

#### Tarea Automática (Cron)
- `SendReminders`: Envía recordatorios de aprobaciones pendientes
- Ejecución recomendada: cada hora
- Detecta solicitudes > 48h sin respuesta
- Envía email de recordatorio automático
- Marca recordatorio como enviado para evitar duplicados

#### Documentación
- **README.md** (200+ líneas): Documentación completa del plugin
- **INSTALL.md**: Guía de instalación rápida en 5 minutos
- **WORKFLOW.md** (300+ líneas): Diagramas y flujo detallado
- **SECURITY.md** (400+ líneas): Análisis de seguridad y buenas prácticas
- **EXAMPLES.md** (450+ líneas): 7 casos de prueba detallados
- **SUMMARY.md** (350+ líneas): Resumen ejecutivo del proyecto
- **FILE_INDEX.md**: Índice visual de todos los archivos
- **LICENSE**: Licencia GPLv2+
- **CHANGELOG.md**: Este archivo

#### Interfaz de Usuario
- Página moderna y responsive de aprobación/rechazo
- Diseño con gradientes y animaciones CSS
- Mensajes claros de éxito/error
- Información contextual del ticket
- Advertencias de seguridad
- Compatible con móviles y tablets

#### Auditoría y Logging
- Evento `created`: Solicitud de aprobación creada
- Evento `email_sent`: Email enviado exitosamente
- Evento `email_failed`: Error al enviar email
- Evento `approve`: Solicitud aprobada
- Evento `reject`: Solicitud rechazada
- Evento `expired`: Token expirado al intentar usar
- Evento `reminder_sent`: Recordatorio enviado
- Evento `security_invalid_token_format`: Intento con formato inválido
- Evento `security_token_not_found`: Token no encontrado
- Evento `security_token_already_used`: Intento de reutilización

### 🔒 Seguridad

#### Implementaciones
- Tokens de 256 bits imposibles de adivinar (2^256 combinaciones)
- Comparación segura de strings con `hash_equals()` para prevenir timing attacks
- Obtención segura de IP considerando proxies (X-Forwarded-For, X-Real-IP)
- Validación estricta de email con `filter_var()`
- Escapado de outputs con `htmlspecialchars()`
- Uso de API de base de datos de GLPI (prepared statements)

#### Mitigaciones
- ✅ Enumeración de tokens: Imposible por entropía de 256 bits
- ✅ Replay attacks: Token de un solo uso
- ✅ SQL Injection: Prepared statements en todas las queries
- ✅ XSS: Escapado de todos los outputs HTML
- ✅ CSRF: Token único no predecible
- ✅ Man-in-the-Middle: HTTPS recomendado (configuración externa)

### 📋 Requisitos

- GLPI >= 11.0.0 y <= 11.0.99
- PHP >= 7.4 con función `random_bytes()`
- MySQL/MariaDB
- Servidor SMTP configurado en GLPI
- HTTPS configurado (recomendado)

### 🐛 Conocido

#### Limitaciones Actuales
- Solo soporta un aprobador por solicitud
- Email enviado sin cifrado (limitación de SMTP estándar)
- No hay sistema de aprobación escalonada o jerárquica
- Recordatorio único (no múltiples recordatorios)
- No hay notificación al solicitante tras decisión
- Interfaz de configuración básica (sin dashboard avanzado)

#### Mejoras Futuras Planificadas
- Ver sección [Unreleased] más abajo

### 📝 Notas de Migración

No aplica - primera versión.

### 👥 Contribuidores

- Senior PHP Developer - Desarrollo inicial completo

---

## [Unreleased]

### 🚀 Planificado para v1.1.0

#### A Añadir
- [ ] Soporte para múltiples aprobadores
- [ ] Aprobación requiere X de Y aprobadores
- [ ] Notificación automática al solicitante tras decisión
- [ ] Dashboard de estadísticas en el plugin
- [ ] Filtros y búsqueda en panel de configuración
- [ ] Exportación de auditoría a CSV/PDF
- [ ] Plantillas personalizables de emails
- [ ] Soporte para adjuntos en emails
- [ ] Campos personalizados en solicitud

#### A Mejorar
- [ ] Interfaz de configuración más visual
- [ ] Validación de formato de email en configuración
- [ ] Mensaje de error más descriptivo en logs
- [ ] Performance de queries con muchos registros
- [ ] Cache de configuración para reducir queries
- [ ] Internacionalización completa (i18n)

### 🔮 Planificado para v1.2.0

#### A Añadir
- [ ] API REST para integración externa
- [ ] Webhooks configurables tras aprobación/rechazo
- [ ] Firma digital de emails (S/MIME, PGP)
- [ ] Autenticación de dos factores opcional
- [ ] Geolocalización de IP del aprobador
- [ ] Integración con Active Directory
- [ ] Sistema de comentarios en aprobación/rechazo
- [ ] Aprobación con condiciones (si X entonces Y)

### 🌟 Planificado para v2.0.0

#### A Añadir
- [ ] Aprobación escalonada (jerarquía de aprobadores)
- [ ] Workflow configurable visualmente
- [ ] Inteligencia artificial para sugerencias
- [ ] Mobile app para aprobadores
- [ ] Panel de control avanzado con gráficos
- [ ] Sistema de roles y permisos granulares
- [ ] Integración con sistemas de identidad (SSO, OAuth)
- [ ] Auditoría exportable para compliance

---

## Formato de Versiones

### Estructura: MAJOR.MINOR.PATCH

- **MAJOR**: Cambios incompatibles con versiones anteriores
- **MINOR**: Nueva funcionalidad compatible con versiones anteriores
- **PATCH**: Correcciones de bugs compatibles con versiones anteriores

### Categorías de Cambios

- **Añadido** (`Added`): Nueva funcionalidad
- **Cambiado** (`Changed`): Cambios en funcionalidad existente
- **Obsoleto** (`Deprecated`): Funcionalidad que será eliminada
- **Eliminado** (`Removed`): Funcionalidad eliminada
- **Corregido** (`Fixed`): Corrección de bugs
- **Seguridad** (`Security`): Parches de seguridad

---

## Mantención de Versiones

### Versiones Soportadas

| Versión | Soportada | Fin de Soporte |
|---------|-----------|----------------|
| 1.0.x   | ✅ Sí     | 2027-02-12     |

### Política de Soporte

- **Versión actual**: Soporte completo (bugfixes, features, security)
- **Versión anterior**: Solo bugfixes críticos y security
- **Versiones antiguas**: Sin soporte (actualización recomendada)

---

## Reporte de Bugs

Para reportar bugs o solicitar features:
1. GitHub Issues: https://github.com/yourrepo/emailapproval/issues
2. Email: soporte@example.com
3. Incluir: versión GLPI, versión plugin, logs, pasos para reproducir

---

## Agradecimientos

Gracias a la comunidad de GLPI por el framework y la documentación.

---

[1.0.0]: https://github.com/yourrepo/emailapproval/releases/tag/v1.0.0
[Unreleased]: https://github.com/yourrepo/emailapproval/compare/v1.0.0...HEAD
