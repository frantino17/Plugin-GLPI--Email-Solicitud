# 🎨 DEMO VISUAL DEL PLUGIN - Email Approval

Este directorio contiene previsualizaciones HTML standalone del plugin que puedes ver en tu navegador **SIN necesidad de instalar GLPI**.

## 📋 Archivos de Demostración

### 1. `preview_form.html` - Vista del Formulario
**Qué muestra:**
- Formulario completo de solicitud de correo docente
- Interfaz moderna con gradientes y diseño responsivo
- Todos los campos: nombre, legajo, email, departamento, observaciones
- Validación de campos obligatorios
- Botones interactivos

**Cómo verlo:**
```bash
# Opción 1: Abrir directamente en el navegador
xdg-open preview_form.html

# Opción 2: Abrir con navegador específico
firefox preview_form.html
chromium preview_form.html

# Opción 3: Doble clic en el archivo desde el administrador de archivos
```

### 2. `preview_email.html` - Vista del Email
**Qué muestra:**
- Email completo que recibe el jefe de área
- Diseño profesional con gradientes y colores
- Tabla con datos del docente
- Botones grandes de APROBAR/RECHAZAR
- Diseño responsivo para móviles

**Cómo verlo:**
```bash
xdg-open preview_email.html
```

**Nota:** El email se abre automáticamente cuando haces clic en "Vista Previa del Email" en el formulario.

## 🚀 Uso Rápido

### Método 1: Vista Simple (Sin Servidor)
```bash
cd /home/pc/Documentos/Plugin\ GLPI/emailapproval/demo
xdg-open preview_form.html
```

1. Se abrirá el formulario en tu navegador
2. Completa los campos con datos de prueba
3. Haz clic en "📤 Vista Previa del Email"
4. Se abrirá una nueva pestaña mostrando el email

### Método 2: Con Servidor Local (Recomendado)
Usar un servidor local permite probar mejor las funcionalidades:

```bash
cd /home/pc/Documentos/Plugin\ GLPI/emailapproval/demo

# Si tienes Python 3
python3 -m http.server 8080

# Si tienes PHP
php -S localhost:8080

# Si tienes Node.js con http-server
npx http-server -p 8080
```

Luego abre en tu navegador:
```
http://localhost:8080/preview_form.html
```

## 🎯 Datos de Prueba Sugeridos

Para probar el formulario, usa estos datos de ejemplo:

```
Nombre Completo: Juan Carlos Pérez
Legajo: 12345
Email Personal: juan.perez@gmail.com
Área/Departamento: Departamento de Sistemas e Informática
Email del Responsable: jefe.sistemas@institucion.edu.ar
Observaciones: Docente nuevo, requiere correo para acceso a plataforma educativa
```

## 🔍 Qué Observar en la Demo

### En el Formulario (`preview_form.html`):
✅ Diseño moderno con gradientes morado/azul
✅ Campos organizados por secciones
✅ Labels claros con indicadores de obligatorios (*)
✅ Textos de ayuda debajo de cada campo
✅ Validación de campos requeridos
✅ Diseño responsivo (prueba redimensionando la ventana)
✅ Botones con efectos hover

### En el Email (`preview_email.html`):
✅ Header con gradiente y título destacado
✅ Tabla con datos del docente en recuadro con fondo degradado
✅ Observaciones en recuadro amarillo (si existen)
✅ Botones grandes APROBAR (verde) y RECHAZAR (rojo)
✅ Nota de advertencia sobre el token único
✅ Footer con información del sistema
✅ Diseño adaptable a móviles

## 📱 Prueba en Diferentes Dispositivos

### Desktop
```bash
# Abre en tamaño completo y redimensiona la ventana
# para ver cómo se adapta el diseño
```

### Móvil (Simulación)
1. Abre las herramientas de desarrollador (F12)
2. Activa el modo responsive (Ctrl+Shift+M)
3. Selecciona diferentes tamaños de dispositivo
4. Observa cómo los botones y columnas se reorganizan

## 🎨 Personalización de Colores

Si quieres ver cómo se vería con otros colores, edita los archivos HTML:

**Gradiente principal (morado/azul):**
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

**Botón aprobar (verde):**
```css
background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
```

**Botón rechazar (rojo):**
```css
background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
```

## 🐛 Troubleshooting

### El formulario no abre el email
- **Causa:** Bloqueador de pop-ups del navegador
- **Solución:** Permite pop-ups para localhost o el archivo local

### Los estilos se ven mal
- **Causa:** Algunos navegadores muy antiguos no soportan CSS Grid
- **Solución:** Usa un navegador moderno (Firefox, Chrome, Edge)

### Los botones del email no funcionan
- **Causa:** Son enlaces de demostración, no están conectados a GLPI
- **Solución:** Esto es normal, es solo una vista previa visual

## 📊 Comparación: Demo vs Plugin Real

| Característica | Demo HTML | Plugin GLPI Real |
|----------------|-----------|------------------|
| Interfaz visual | ✅ Idéntica | ✅ Idéntica |
| Validación frontend | ✅ Funciona | ✅ Funciona |
| Envío de datos | ❌ No envía | ✅ Crea registro en BD |
| Envío de email | ❌ Solo muestra | ✅ Envía email real |
| Token seguro | ❌ Demo fake | ✅ Criptográfico real |
| Aprobación/Rechazo | ❌ No funcional | ✅ Actualiza BD |
| Integración GLPI | ❌ No integrado | ✅ Totalmente integrado |
| Auditoría | ❌ No registra | ✅ Log completo |

## 🎓 Próximos Pasos

Después de revisar la demo visual:

1. **Si te gusta el diseño:** Procede con la instalación en GLPI
2. **Si quieres cambios:** Indica qué aspectos modificar (colores, textos, layout)
3. **Si necesitas más demos:** Puedo crear demos de:
   - Página de aprobación/rechazo
   - Panel de configuración
   - Vista de historial de solicitudes

## 📝 Notas Importantes

⚠️ **Esta demo es solo visual** - No realiza ninguna acción real en base de datos ni envía emails reales.

✅ **Perfecta para:**
- Mostrar a superiores cómo se verá el sistema
- Validar el diseño antes de instalar
- Hacer capturas de pantalla para documentación
- Entrenar al personal antes del deployment

❌ **No reemplaza:**
- La instalación real del plugin en GLPI
- Las pruebas funcionales con datos reales
- La configuración de emails en producción

## 🔗 Referencias

- Documentación completa: `../docs/README.md`
- Manual de instalación: `../docs/INSTALLATION.md`
- Manual del formulario: `../docs/MANUAL_REQUEST.md`
