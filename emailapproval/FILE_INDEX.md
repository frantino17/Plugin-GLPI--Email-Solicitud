# Índice de Archivos del Plugin Email Approval

## 📁 Estructura Completa

```
emailapproval/
│
├── 📄 .gitignore                      # Archivos ignorados por Git
├── 📄 LICENSE                         # Licencia GPLv2+
├── 📄 setup.php                       # ⚙️ CORE: Configuración principal del plugin
├── 📄 hook.php                        # 🎣 CORE: Hooks de GLPI
│
├── 📂 inc/                            # Clases PHP del plugin
│   ├── 📄 approval.class.php          # 💼 CLASE PRINCIPAL (500+ líneas)
│   │                                  #    - Generación de tokens
│   │                                  #    - Envío de emails
│   │                                  #    - Validación de aprobaciones
│   │                                  #    - Auditoría
│   │                                  #    - Tarea cron de recordatorios
│   │
│   └── 📄 crontask.class.php          # ⏱️ Gestión de tareas automáticas
│                                      #    - Instalación de cron
│                                      #    - Desinstalación de cron
│
├── 📂 front/                          # Interfaces web públicas y admin
│   ├── 📄 approve.php                 # 🌐 ENDPOINT PÚBLICO (sin login)
│   │                                  #    - Procesa tokens de aprobación/rechazo
│   │                                  #    - Página HTML de éxito/error
│   │                                  #    - Diseño responsive moderno
│   │
│   └── 📄 config.form.php             # ⚙️ Panel de configuración (admin)
│                                      #    - Configurar email del aprobador
│                                      #    - Tiempos de expiración
│                                      #    - Estados personalizados
│                                      #    - Estadísticas visuales
│
├── 📂 install/                        # Scripts de instalación
│   ├── 📄 install.php                 # 🔧 Instalador principal
│   │                                  #    - Crea tablas en BD
│   │                                  #    - Configura valores por defecto
│   │                                  #    - Registra tareas cron
│   │
│   └── 📂 mysql/                      # Scripts SQL
│       ├── 📄 install.sql             # 📊 SQL de instalación manual
│       │                              #    - Tabla approvals
│       │                              #    - Tabla logs
│       │                              #    - Configuración inicial
│       │
│       └── 📄 uninstall.sql           # 🗑️ SQL de desinstalación
│                                      #    - Eliminar tablas
│                                      #    - Limpiar configuración
│                                      #    - Eliminar tareas cron
│
├── 📂 locales/                        # Traducciones (futuro)
│   └── (vacío - preparado para i18n)
│
└── 📂 docs/ (archivos raíz)           # Documentación completa
    │
    ├── 📘 README.md                   # 📖 Documentación principal (200+ líneas)
    │                                  #    - Características
    │                                  #    - Instalación
    │                                  #    - Uso
    │                                  #    - Estructura
    │                                  #    - Configuración
    │                                  #    - Troubleshooting
    │
    ├── 📗 INSTALL.md                  # 🚀 Guía de instalación rápida
    │                                  #    - Instalación en 5 minutos
    │                                  #    - Configuración SMTP
    │                                  #    - Verificación
    │                                  #    - Solución rápida
    │
    ├── 📊 WORKFLOW.md                 # 📊 Flujo de funcionamiento (300+ líneas)
    │                                  #    - Diagrama completo
    │                                  #    - Fases del proceso
    │                                  #    - Validaciones por fase
    │                                  #    - Eventos de auditoría
    │                                  #    - Puntos de extensión
    │
    ├── 🔒 SECURITY.md                 # 🔒 Seguridad (400+ líneas)
    │                                  #    - Generación de tokens
    │                                  #    - Validaciones estrictas
    │                                  #    - Prevención de ataques
    │                                  #    - Auditoría y compliance
    │                                  #    - Checklist de seguridad
    │                                  #    - Referencias OWASP
    │
    ├── 🧪 EXAMPLES.md                 # 🧪 Casos de prueba (450+ líneas)
    │                                  #    - 7 casos de prueba completos
    │                                  #    - Consultas SQL útiles
    │                                  #    - Debug y logs
    │                                  #    - Checklist de pruebas
    │
    └── 📋 SUMMARY.md                  # 📋 Resumen ejecutivo (350+ líneas)
                                       #    - Visión general
                                       #    - Estructura resumida
                                       #    - Base de datos
                                       #    - Seguridad
                                       #    - Configuración
                                       #    - Instalación rápida
                                       #    - Roadmap
```

---

## 📊 Estadísticas del Proyecto

### Archivos por tipo:
```
PHP Files:      6 archivos
SQL Files:      2 archivos
Documentation:  6 archivos (Markdown)
Config Files:   2 archivos (.gitignore, LICENSE)
TOTAL:          16 archivos
```

### Líneas de código (aproximado):
```
PHP Code:           ~1,200 líneas
SQL Scripts:        ~80 líneas
Documentation:      ~2,000 líneas
TOTAL:              ~3,280 líneas
```

### Tamaño de archivos:
```
setup.php:              ~80 líneas
hook.php:               ~70 líneas
approval.class.php:     ~550 líneas  ⭐ ARCHIVO PRINCIPAL
crontask.class.php:     ~35 líneas
approve.php:            ~220 líneas
config.form.php:        ~90 líneas
install.php:            ~70 líneas
```

---

## 🎯 Archivos Clave por Función

### Para entender el funcionamiento:
1. **setup.php** - Punto de entrada del plugin
2. **hook.php** - Detección de tickets
3. **approval.class.php** - Toda la lógica de negocio
4. **approve.php** - Endpoint público de aprobación

### Para instalar:
1. **install/install.php** - Instalador automático
2. **install/mysql/install.sql** - Instalación manual alternativa

### Para configurar:
1. **front/config.form.php** - Panel de configuración
2. **setup.php** (constantes) - Valores por defecto

### Para mantener:
1. **WORKFLOW.md** - Entender el flujo
2. **SECURITY.md** - Conocer seguridad
3. **EXAMPLES.md** - Casos de prueba

---

## 🔍 Mapa de Dependencias

```
setup.php
    ↓ define constantes
    ↓ registra hooks
    ↓
hook.php ←──────────────────┐
    ↓                       │
    └→ plugin_emailapproval_item_add_ticket()
            ↓
            ↓ detecta ticket específico
            ↓
            ↓
inc/approval.class.php ←────┤
    ↓                       │
    ├→ generateSecureToken()│
    ├→ createApprovalRequest()
    ├→ sendApprovalEmail()  │
    ├→ processApproval() ←──┤
    ├→ updateTicketStatus() │
    ├→ cronSendReminders()  │
    └→ logAction()          │
            ↓               │
            ↓               │
front/approve.php ──────────┘
    ↓
    └→ displaySuccessPage() / displayErrorPage()

inc/crontask.class.php
    ↓
    └→ Registra tarea en GLPI cron

install/install.php
    ↓
    ├→ Crea tablas SQL
    └→ Registra configuración
```

---

## 📚 Guía de Lectura Recomendada

### Para desarrolladores nuevos:
1. **SUMMARY.md** - Visión general rápida
2. **README.md** - Características y uso
3. **WORKFLOW.md** - Entender el flujo
4. **setup.php** + **hook.php** - Punto de entrada
5. **approval.class.php** - Lógica principal

### Para administradores:
1. **INSTALL.md** - Instalación paso a paso
2. **README.md** - Sección de configuración
3. **EXAMPLES.md** - Casos de prueba
4. **front/config.form.php** - Panel de configuración

### Para auditores de seguridad:
1. **SECURITY.md** - Análisis completo de seguridad
2. **approval.class.php** - Validaciones implementadas
3. **approve.php** - Endpoint público
4. **install/mysql/install.sql** - Estructura de BD

### Para testers:
1. **EXAMPLES.md** - Casos de prueba detallados
2. **README.md** - Sección troubleshooting
3. **WORKFLOW.md** - Escenarios completos

---

## 🛠️ Comandos Útiles

### Contar líneas de código:
```bash
find . -name "*.php" | xargs wc -l
```

### Buscar función específica:
```bash
grep -r "function createApprovalRequest" .
```

### Ver estructura del proyecto:
```bash
find . -type f -name "*.php" -o -name "*.md" | sort
```

### Validar sintaxis PHP:
```bash
find . -name "*.php" -exec php -l {} \;
```

### Buscar TODOs:
```bash
grep -r "TODO\|FIXME\|XXX" --include="*.php" .
```

---

## 📞 Archivo a Revisar Según Problema

| Problema | Archivo a revisar |
|----------|-------------------|
| No detecta tickets | `hook.php` línea 27-35 |
| No genera tokens | `inc/approval.class.php` línea 17-20 |
| No envía emails | `inc/approval.class.php` línea 115-185 |
| Validación falla | `inc/approval.class.php` línea 189-280 |
| Token inválido | `inc/approval.class.php` línea 206-215 |
| No actualiza ticket | `inc/approval.class.php` línea 284-325 |
| Recordatorios no funcionan | `inc/approval.class.php` línea 329-360 |
| Error en instalación | `install/install.php` línea 10-70 |
| Página de error | `front/approve.php` línea 140-235 |

---

## ✅ Checklist de Archivos Necesarios

### Archivos obligatorios para funcionamiento:
- [x] setup.php
- [x] hook.php
- [x] inc/approval.class.php
- [x] inc/crontask.class.php
- [x] front/approve.php
- [x] install/install.php

### Archivos opcionales pero recomendados:
- [x] front/config.form.php (panel de configuración)
- [x] install/mysql/install.sql (instalación manual)
- [x] README.md (documentación)

### Archivos auxiliares:
- [x] LICENSE
- [x] .gitignore
- [x] INSTALL.md, WORKFLOW.md, SECURITY.md, EXAMPLES.md, SUMMARY.md

---

## 🎓 Conclusión

El plugin está **completamente estructurado** con:
- ✅ Código funcional y comentado
- ✅ Documentación exhaustiva
- ✅ Scripts de instalación
- ✅ Casos de prueba
- ✅ Guías de seguridad
- ✅ Ejemplos de uso

**¡Listo para instalar y usar en GLPI 11!** 🚀
