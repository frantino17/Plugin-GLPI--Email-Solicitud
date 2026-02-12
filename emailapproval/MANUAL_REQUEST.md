# 📧 Solicitud Manual de Correo Institucional Docente

## Nueva Funcionalidad v1.1

Esta versión incluye un **formulario manual** que permite a los técnicos L1 crear solicitudes de correo institucional para docentes de manera controlada, editando toda la información antes de enviar el email de aprobación.

---

## 🎯 Características

### Formulario Completo con:

✅ **Datos del Docente**
- Nombre completo
- Número de legajo
- Email institucional deseado

✅ **Datos del Departamento/Área**
- Nombre del departamento o área responsable

✅ **Responsable Aprobador**
- Email del director/responsable que debe aprobar
- Se puede modificar por solicitud

✅ **Observaciones** (opcional)
- Campo libre para información adicional

---

## 📧 Email Profesional Mejorado

El email enviado ahora incluye:

### ✨ Diseño HTML Moderno
- Gradientes y colores profesionales
- Responsive (se adapta a móviles)
- Botones grandes y visibles
- Formato tabular claro

### 📋 Información Completa
```
• Nombre del docente
• Número de legajo
• Email solicitado
• Departamento/Área
• Enlace directo al ticket
```

### 🎨 Vista Previa del Email

```
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║   📧 Solicitud de Correo Institucional                      ║
║      Aprobación Requerida - Ticket #123                     ║
║                                                              ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  Estimado/a Responsable de Departamento de Informática,     ║
║                                                              ║
║  ┌────────────────────────────────────────────────────────┐ ║
║  │ 👤 Datos del Docente                                   │ ║
║  ├────────────────────────────────────────────────────────┤ ║
║  │ Nombre completo:    Juan Pérez García                  │ ║
║  │ Nro. de Legajo:     12345                              │ ║
║  │ Email solicitado:   juan.perez@institucion.edu.ar      │ ║
║  │ Departamento/Área:  Departamento de Informática        │ ║
║  └────────────────────────────────────────────────────────┘ ║
║                                                              ║
║  Como responsable, debe ACEPTAR o RECHAZAR esta solicitud.  ║
║                                                              ║
║  ┌──────────────────┐        ┌──────────────────┐          ║
║  │ ✅ APROBAR       │        │ ❌ RECHAZAR      │          ║
║  │   SOLICITUD      │        │    SOLICITUD     │          ║
║  └──────────────────┘        └──────────────────┘          ║
║                                                              ║
║  ⚠️ Información Importante:                                 ║
║  • Este enlace es único y de un solo uso                    ║
║  • Expira en 48 horas                                       ║
║  • No comparta este enlace con terceros                     ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
```

---

## 🚀 Cómo Usar

### Paso 1: Acceder al Formulario

Hay dos formas de acceder:

#### Opción A: Desde el menú de GLPI
```
1. Login en GLPI como L1 o técnico
2. Ir a: Asistencia → Solicitud Correo Docente
3. Clic en "Nueva Solicitud"
```

#### Opción B: URL directa
```
https://tu-glpi.com/plugins/emailapproval/front/request.form.php
```

### Paso 2: Completar el Formulario

#### Datos del Docente (obligatorios)
```
Nombre completo:        Juan Pérez García
Nro. de Legajo:         12345
Email deseado:          juan.perez@institucion.edu.ar
```

#### Departamento/Área (obligatorio)
```
Departamento:           Departamento de Informática
```

#### Responsable Aprobador (obligatorio)
```
Email del responsable:  director.informatica@institucion.edu.ar
```

#### Observaciones (opcional)
```
Nuevo docente contratado para el ciclo lectivo 2026.
Requiere acceso urgente para gestión de materias.
```

### Paso 3: Enviar Solicitud

1. Revisar todos los datos
2. Clic en "📤 Enviar Solicitud"
3. El sistema:
   - Crea un ticket automáticamente
   - Genera token seguro
   - Envía email al responsable
   - Registra en auditoría

---

## 🔄 Flujo Completo

```
L1 llena formulario
       ↓
Sistema crea ticket en GLPI
       ↓
Genera token seguro (256 bits)
       ↓
Envía email bonito al responsable
       ↓
Responsable recibe email con botones
       ↓
Clic en APROBAR o RECHAZAR
       ↓
Sistema valida token
       ↓
Actualiza ticket automáticamente
       ↓
Registra decisión en auditoría
       ↓
FIN (L1 ve resultado en el ticket)
```

---

## 📊 Ventajas del Formulario Manual

### ✅ Control Total
- L1 revisa datos antes de enviar
- Puede corregir errores de tipeo
- Puede añadir contexto adicional

### ✅ Flexibilidad
- Email del responsable personalizable por solicitud
- Útil cuando hay múltiples responsables
- Útil para casos especiales

### ✅ Trazabilidad
- Ticket creado automáticamente
- Registro de quién creó la solicitud
- Fecha y hora exacta
- Datos completos del docente

### ✅ Profesionalismo
- Email con diseño institucional
- Información clara y organizada
- Botones grandes y visibles
- Funciona en móviles

---

## 🗄️ Campos Almacenados en Base de Datos

La tabla `glpi_plugin_emailapproval_approvals` ahora incluye:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| teacher_name | Nombre completo del docente | Juan Pérez García |
| teacher_legajo | Número de legajo | 12345 |
| teacher_email | Email solicitado | juan.perez@institucion.edu.ar |
| department_name | Departamento/Área | Dpto. de Informática |

---

## 🔍 Consultas SQL Útiles

### Ver solicitudes por departamento
```sql
SELECT 
   teacher_name,
   teacher_legajo,
   teacher_email,
   department_name,
   status,
   created_at
FROM glpi_plugin_emailapproval_approvals
WHERE department_name = 'Departamento de Informática'
ORDER BY created_at DESC;
```

### Buscar por legajo
```sql
SELECT * FROM glpi_plugin_emailapproval_approvals
WHERE teacher_legajo = '12345';
```

### Estadísticas por departamento
```sql
SELECT 
   department_name,
   COUNT(*) as total,
   SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as aprobadas,
   SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rechazadas,
   SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pendientes
FROM glpi_plugin_emailapproval_approvals
GROUP BY department_name;
```

---

## 🎨 Personalización del Email

### Cambiar colores
Editar en `inc/approval.class.php`, método `getEmailTemplate()`:

```php
// Header gradient
background: linear-gradient(135deg, #TU_COLOR_1 0%, #TU_COLOR_2 100%);

// Botón aprobar
background: linear-gradient(135deg, #28a745 0%, #20c997 100%);

// Botón rechazar
background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
```

### Añadir logo institucional
```php
<tr>
   <td style="text-align: center; padding: 20px;">
      <img src="https://tu-institucion.edu.ar/logo.png" 
           alt="Logo" width="150" style="max-width: 100%;">
   </td>
</tr>
```

---

## ✅ Actualización desde Versión Anterior

Si ya tenías el plugin instalado:

### Opción A: Reinstalar
```
1. Configuración → Plugins
2. Email Approval → Desactivar → Desinstalar
3. Instalar → Activar
```

### Opción B: Solo actualizar BD
```sql
-- Ejecutar script de actualización
source install/mysql/update_1.1.sql;
```

---

## 🐛 Troubleshooting

### El formulario no aparece en el menú
- Verificar permisos: Usuario debe tener permiso "Crear ticket"
- Verificar plugin está activado
- Limpiar caché del navegador

### Email no se ve bonito
- Cliente de email no soporta HTML (raro)
- Se enviará versión texto plano automáticamente
- Probar con Gmail, Outlook, Apple Mail

### Error al enviar formulario
- Verificar todos los campos obligatorios
- Verificar formato de emails (@ y dominio)
- Revisar logs: `/var/www/html/glpi/files/_log/php-errors.log`

---

## 📚 Documentación Relacionada

- **README.md** - Documentación general del plugin
- **INSTALL.md** - Instalación y configuración
- **EXAMPLES.md** - Casos de prueba
- **WORKFLOW.md** - Flujo detallado del proceso

---

## 🎓 Mejores Prácticas

### Para L1:
1. ✅ Verificar datos del docente antes de enviar
2. ✅ Usar el email corporativo del docente
3. ✅ Seleccionar el responsable correcto del departamento
4. ✅ Añadir observaciones si hay casos especiales
5. ✅ Verificar en el ticket que el email se envió

### Para Responsables:
1. ✅ Revisar datos del docente cuidadosamente
2. ✅ Aprobar solo si la solicitud es válida
3. ✅ Responder dentro de las 48 horas
4. ✅ No compartir el enlace de aprobación

---

## 🚀 Roadmap Futuro

### v1.2 (Próximamente)
- [ ] Validación automática contra base de legajos
- [ ] Autocompletado de datos desde LDAP
- [ ] Adjuntar documentación (DNI, contrato)
- [ ] Notificación al docente cuando se aprueba
- [ ] Templates predefinidos por departamento

### v2.0 (Futuro)
- [ ] Aprobación multinivel (jefe → director)
- [ ] Dashboard de solicitudes pendientes
- [ ] Reportes estadísticos visuales
- [ ] Integración con Active Directory
- [ ] App móvil para aprobadores

---

## 📞 Soporte

¿Problemas o sugerencias?
- 📧 Email: soporte@institucion.edu.ar
- 🐛 GitHub Issues
- 📚 Wiki del proyecto

---

**Versión:** 1.1.0  
**Fecha:** 12 de febrero de 2026  
**Autor:** Senior PHP Developer
