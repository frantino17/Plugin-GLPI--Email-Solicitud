# 🎨 Guía Rápida: Visualizar el Plugin Antes de Instalarlo

Este documento te muestra **3 formas diferentes** de probar visualmente el plugin sin instalar GLPI.

---

## 🚀 Opción 1: Vista HTML Simple (MÁS RÁPIDO - 30 segundos)

**Ideal para:** Ver rápidamente cómo se ven el formulario y los emails.

### Paso a Paso:

```bash
cd "/home/pc/Documentos/Plugin GLPI/emailapproval/demo"
xdg-open preview_form.html
```

### Qué verás:
1. **Formulario completo** con todos los campos
2. Completa los datos de prueba
3. Haz clic en "📤 Vista Previa del Email"
4. Se abrirá el **email profesional** en una nueva pestaña

### ✅ Ventajas:
- No requiere instalación de nada
- Funciona en cualquier navegador
- 100% offline
- Ideal para mostrar a terceros

### ❌ Limitaciones:
- No envía emails reales
- No guarda datos en base de datos
- Los botones de aprobar/rechazar no funcionan

---

## 🌐 Opción 2: Servidor Web Local (RECOMENDADO - 2 minutos)

**Ideal para:** Probar las funcionalidades con más realismo.

### Con Python 3 (viene instalado en Linux Mint):

```bash
cd "/home/pc/Documentos/Plugin GLPI/emailapproval/demo"
python3 -m http.server 8080
```

### Con PHP (si lo tienes instalado):

```bash
cd "/home/pc/Documentos/Plugin GLPI/emailapproval/demo"
php -S localhost:8080
```

### Luego abre en tu navegador:
```
http://localhost:8080/preview_form.html
```

### ✅ Ventajas:
- Funciona mejor con los pop-ups
- Más cercano a cómo se verá en producción
- Puedes probarlo desde otros dispositivos en tu red local

---

## 🐳 Opción 3: Entorno GLPI Completo con Docker (COMPLETO - 10 minutos)

**Ideal para:** Probar TODO el plugin funcionando al 100%.

### Requisitos Previos:

Instalar Docker (si no lo tienes):
```bash
# Actualizar repositorios
sudo apt update

# Instalar Docker
sudo apt install docker.io docker-compose -y

# Agregar tu usuario al grupo docker
sudo usermod -aG docker $USER

# Reiniciar sesión o ejecutar:
newgrp docker
```

### Iniciar Entorno de Pruebas:

```bash
cd "/home/pc/Documentos/Plugin GLPI/emailapproval/demo"
./start-test-environment.sh
```

### Configuración Inicial de GLPI:

1. **Abre:** http://localhost:8080
2. **Selecciona idioma:** Español
3. **Acepta licencia:** Continuar
4. **Configuración de base de datos:**
   - Servidor MySQL: `mysql`
   - Usuario: `glpi_user`
   - Contraseña: `glpi_pass`
   - Base de datos: `glpidb`
5. **Continúa** hasta completar el asistente
6. **Login inicial:**
   - Usuario: `glpi`
   - Password: `glpi`

### Instalar el Plugin:

1. Ve a: **Configuración → Plugins**
2. Busca **"Email Approval"**
3. Haz clic en **"Instalar"**
4. Haz clic en **"Activar"**
5. Ve a: **Asistencia → Solicitud Correo Docente**

### ✅ Ventajas:
- ✅ Plugin funcionando al 100%
- ✅ Base de datos real
- ✅ Puedes enviar emails de prueba (con configuración SMTP)
- ✅ Todas las funcionalidades activas
- ✅ Puedes probar aprobaciones/rechazos
- ✅ Historial y auditoría funcionando

### ❌ Consideraciones:
- Requiere Docker instalado
- Usa ~500MB de espacio en disco
- Primera vez tarda 5-10 minutos en descargar imágenes

### Comandos Útiles Docker:

```bash
# Ver logs en tiempo real
docker-compose logs -f glpi

# Reiniciar servicios
docker-compose restart

# Detener todo
docker-compose down

# Eliminar todo (incluyendo datos)
docker-compose down -v
```

---

## 📊 Comparación de Opciones

| Característica | HTML Simple | Servidor Local | Docker GLPI |
|----------------|-------------|----------------|-------------|
| ⏱️ Tiempo setup | 30 seg | 2 min | 10 min |
| 💾 Espacio disco | 0 MB | 0 MB | ~500 MB |
| 🎨 Ver interfaz | ✅ | ✅ | ✅ |
| 📧 Ver emails | ✅ | ✅ | ✅ |
| 💾 Guardar datos | ❌ | ❌ | ✅ |
| 📤 Enviar emails | ❌ | ❌ | ✅* |
| ✅ Aprobar/Rechazar | ❌ | ❌ | ✅ |
| 📊 Historial | ❌ | ❌ | ✅ |
| 🔌 Plugins GLPI | ❌ | ❌ | ✅ |
| 🌐 Offline | ✅ | ✅ | ❌** |

*Requiere configurar SMTP  
**Necesita internet la primera vez para descargar imágenes

---

## 🎯 Recomendación por Caso de Uso

### 👀 Solo quiero ver cómo se ve:
→ **Opción 1: HTML Simple**
```bash
xdg-open "/home/pc/Documentos/Plugin GLPI/emailapproval/demo/preview_form.html"
```

### 🎨 Necesito hacer capturas de pantalla:
→ **Opción 2: Servidor Local** (mejor para capturas profesionales)

### 🧪 Quiero probar todas las funcionalidades:
→ **Opción 3: Docker GLPI** (entorno completo)

### 👥 Presentar a mi jefe/equipo:
→ **Opción 1 o 2** (más rápido, sin complicaciones)

### 🚀 Antes de instalar en producción:
→ **Opción 3: Docker GLPI** (test completo antes del deploy)

---

## 📝 Datos de Prueba

Para cualquier opción, usa estos datos de ejemplo:

```
👨‍🏫 DATOS DEL DOCENTE:
Nombre Completo: Juan Carlos Pérez
Legajo: 12345
Email Personal: juan.perez@gmail.com
Área/Departamento: Departamento de Sistemas e Informática

✉️ APROBACIÓN:
Email del Responsable: jefe.sistemas@institucion.edu.ar

📝 OBSERVACIONES:
Docente nuevo que se incorpora este semestre. 
Requiere correo institucional para acceso a aulas virtuales 
y plataforma de gestión académica.
```

---

## 🆘 Ayuda Rápida

### No se abre el HTML:
```bash
# Intenta con otro navegador
firefox "/home/pc/Documentos/Plugin GLPI/emailapproval/demo/preview_form.html"
```

### El email no se abre al hacer clic:
- Permite pop-ups en tu navegador
- O abre manualmente: `preview_email.html`

### Docker no arranca:
```bash
# Verifica que Docker esté corriendo
sudo systemctl start docker

# Verifica que estés en el grupo docker
groups
# Si no ves 'docker', ejecuta:
newgrp docker
```

### Puerto 8080 ocupado:
Edita `docker-compose.yml` y cambia:
```yaml
ports:
  - "8090:80"  # Cambia 8080 por otro puerto
```

---

## 📚 Documentación Adicional

- **README completo:** `../docs/README.md`
- **Manual de instalación:** `../docs/INSTALLATION.md`
- **Guía del formulario:** `../docs/MANUAL_REQUEST.md`
- **Guía de este demo:** `README.md` (este directorio)

---

## 💡 Próximos Pasos

Después de visualizar el plugin:

1. ✅ **Si te gusta:** Procede con la instalación en GLPI real
2. 🎨 **Si quieres cambios:** Indica qué modificar (colores, textos, etc.)
3. 🐛 **Si encuentras problemas:** Revisa `../docs/TROUBLESHOOTING.md`
4. 📖 **Si tienes dudas:** Consulta `../docs/FAQ.md`

---

**¡Disfruta explorando el plugin! 🚀**
