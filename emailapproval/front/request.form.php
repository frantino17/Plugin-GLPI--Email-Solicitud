<?php
/**
 * Formulario manual para crear solicitudes de aprobación
 * 
 * Permite a un técnico L1 crear una solicitud de correo institucional
 * para un docente, editando la información antes de enviar.
 */

include('../../../inc/includes.php');

Session::checkRight("ticket", CREATE);

Html::header('Solicitud de Correo Institucional Docente', $_SERVER['PHP_SELF'], "helpdesk", "ticket");

// Obtener configuración
$config = Config::getConfigurationValues('plugin:emailapproval');
$approver_email = $config['approver_email'] ?? '';

// Procesar formulario
if (isset($_POST['create_request'])) {
   
   // Validar campos obligatorios
   $errors = [];
   
   if (empty($_POST['teacher_name'])) {
      $errors[] = "El nombre del docente es obligatorio";
   }
   
   if (empty($_POST['teacher_legajo'])) {
      $errors[] = "El número de legajo es obligatorio";
   }
   
   if (empty($_POST['department_name'])) {
      $errors[] = "El nombre del departamento/área es obligatorio";
   }
   
   if (empty($_POST['approver_email'])) {
      $errors[] = "El email del responsable es obligatorio";
   } elseif (!filter_var($_POST['approver_email'], FILTER_VALIDATE_EMAIL)) {
      $errors[] = "El email del responsable no es válido";
   }
   
   if (empty($_POST['teacher_email_desired'])) {
      $errors[] = "El email deseado es obligatorio";
   } elseif (!filter_var($_POST['teacher_email_desired'], FILTER_VALIDATE_EMAIL)) {
      $errors[] = "El email deseado no es válido";
   }
   
   if (empty($errors)) {
      // Crear ticket automáticamente
      $ticket = new Ticket();
      
      $description = "DATOS DEL DOCENTE\n";
      $description .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $description .= "Nombre completo: " . $_POST['teacher_name'] . "\n";
      $description .= "Nro. de Legajo: " . $_POST['teacher_legajo'] . "\n";
      $description .= "Email solicitado: " . $_POST['teacher_email_desired'] . "\n";
      $description .= "Departamento/Área: " . $_POST['department_name'] . "\n\n";
      
      if (!empty($_POST['observations'])) {
         $description .= "OBSERVACIONES\n";
         $description .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
         $description .= $_POST['observations'] . "\n\n";
      }
      
      $description .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $description .= "Solicitud creada por: " . getUserName(Session::getLoginUserID()) . "\n";
      $description .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
      
      $ticket_id = $ticket->add([
         'name' => 'Solicitud de correo electrónico institucional',
         'content' => $description,
         'users_id_recipient' => Session::getLoginUserID(),
         'type' => 1, // Incidente
         'itilcategories_id' => 0,
         'urgency' => 3,
         'impact' => 3,
         'priority' => 3,
         'status' => 2, // En curso (asignado)
         'date' => date('Y-m-d H:i:s')
      ]);
      
      if ($ticket_id) {
         // Crear solicitud de aprobación con datos personalizados
         $approval_id = PluginEmailapprovalApproval::createApprovalRequestManual(
            $ticket_id,
            $_POST['approver_email'],
            [
               'teacher_name' => $_POST['teacher_name'],
               'teacher_legajo' => $_POST['teacher_legajo'],
               'teacher_email' => $_POST['teacher_email_desired'],
               'department_name' => $_POST['department_name'],
               'observations' => $_POST['observations'] ?? ''
            ]
         );
         
         if ($approval_id) {
            Session::addMessageAfterRedirect(
               "Solicitud creada exitosamente. Ticket #$ticket_id. Email enviado al responsable.",
               false,
               INFO
            );
            Html::redirect($CFG_GLPI['root_doc'] . "/front/ticket.form.php?id=$ticket_id");
         } else {
            Session::addMessageAfterRedirect(
               "Ticket creado pero hubo un error al enviar el email de aprobación.",
               false,
               ERROR
            );
         }
      } else {
         Session::addMessageAfterRedirect("Error al crear el ticket", false, ERROR);
      }
   } else {
      foreach ($errors as $error) {
         Session::addMessageAfterRedirect($error, false, ERROR);
      }
   }
}

// Mostrar formulario
echo "<div class='center' style='max-width: 900px; margin: 40px auto;'>";
echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";

// Título principal
echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
             color: white; padding: 30px; border-radius: 12px 12px 0 0; text-align: center;'>";
echo "<h1 style='margin: 0; font-size: 28px;'>📧 Solicitud de Correo Institucional Docente</h1>";
echo "<p style='margin: 10px 0 0 0; opacity: 0.9;'>Complete el formulario para enviar la solicitud de aprobación</p>";
echo "</div>";

echo "<div style='background: white; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>";

// Sección: Datos del Docente
echo "<fieldset style='border: 2px solid #667eea; border-radius: 8px; padding: 20px; margin-bottom: 25px;'>";
echo "<legend style='color: #667eea; font-weight: bold; font-size: 18px; padding: 0 10px;'>👤 Datos del Docente</legend>";

echo "<table class='tab_cadre' style='width: 100%;'>";

echo "<tr>";
echo "<td style='padding: 12px; width: 30%;'><strong>Nombre completo del docente: <span style='color:red;'>*</span></strong></td>";
echo "<td style='padding: 12px;'>";
echo "<input type='text' name='teacher_name' value='".(isset($_POST['teacher_name']) ? htmlspecialchars($_POST['teacher_name']) : '')."' 
       size='60' required placeholder='Ej: Juan Pérez García' 
       style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 12px;'><strong>Nro. de Legajo: <span style='color:red;'>*</span></strong></td>";
echo "<td style='padding: 12px;'>";
echo "<input type='text' name='teacher_legajo' value='".(isset($_POST['teacher_legajo']) ? htmlspecialchars($_POST['teacher_legajo']) : '')."' 
       size='30' required placeholder='Ej: 12345' 
       style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='padding: 12px;'><strong>Email institucional deseado: <span style='color:red;'>*</span></strong></td>";
echo "<td style='padding: 12px;'>";
echo "<input type='email' name='teacher_email_desired' value='".(isset($_POST['teacher_email_desired']) ? htmlspecialchars($_POST['teacher_email_desired']) : '')."' 
       size='60' required placeholder='Ej: juan.perez@institucion.edu.ar' 
       style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</fieldset>";

// Sección: Datos del Departamento/Área
echo "<fieldset style='border: 2px solid #764ba2; border-radius: 8px; padding: 20px; margin-bottom: 25px;'>";
echo "<legend style='color: #764ba2; font-weight: bold; font-size: 18px; padding: 0 10px;'>🏢 Departamento / Área</legend>";

echo "<table class='tab_cadre' style='width: 100%;'>";

echo "<tr>";
echo "<td style='padding: 12px; width: 30%;'><strong>Nombre del Departamento/Área: <span style='color:red;'>*</span></strong></td>";
echo "<td style='padding: 12px;'>";
echo "<input type='text' name='department_name' value='".(isset($_POST['department_name']) ? htmlspecialchars($_POST['department_name']) : '')."' 
       size='60' required placeholder='Ej: Departamento de Informática' 
       style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</fieldset>";

// Sección: Responsable Aprobador
echo "<fieldset style='border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin-bottom: 25px;'>";
echo "<legend style='color: #28a745; font-weight: bold; font-size: 18px; padding: 0 10px;'>✅ Responsable Aprobador</legend>";

echo "<table class='tab_cadre' style='width: 100%;'>";

echo "<tr>";
echo "<td style='padding: 12px; width: 30%;'><strong>Email del Responsable: <span style='color:red;'>*</span></strong></td>";
echo "<td style='padding: 12px;'>";
echo "<input type='email' name='approver_email' value='".(isset($_POST['approver_email']) ? htmlspecialchars($_POST['approver_email']) : $approver_email)."' 
       size='60' required placeholder='Ej: director@institucion.edu.ar' 
       style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "<div style='font-size: 12px; color: #666; margin-top: 5px;'>";
echo "💡 Este email recibirá la solicitud de aprobación";
echo "</div>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</fieldset>";

// Sección: Observaciones (opcional)
echo "<fieldset style='border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 25px;'>";
echo "<legend style='color: #f57c00; font-weight: bold; font-size: 18px; padding: 0 10px;'>📝 Observaciones (Opcional)</legend>";

echo "<table class='tab_cadre' style='width: 100%;'>";

echo "<tr>";
echo "<td style='padding: 12px;'>";
echo "<textarea name='observations' rows='5' cols='80' placeholder='Información adicional relevante para la solicitud...' 
       style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;'>"
       .(isset($_POST['observations']) ? htmlspecialchars($_POST['observations']) : '')."</textarea>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</fieldset>";

// Nota informativa
echo "<div style='background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 25px; border-radius: 4px;'>";
echo "<strong style='color: #1976d2;'>ℹ️ Información:</strong><br>";
echo "<ul style='margin: 10px 0 0 20px; color: #555;'>";
echo "<li>Se creará automáticamente un ticket en GLPI</li>";
echo "<li>Se enviará un email al responsable con enlaces de aprobación/rechazo</li>";
echo "<li>El responsable tiene 48 horas para responder</li>";
echo "<li>Si no responde, se enviará un recordatorio automático</li>";
echo "</ul>";
echo "</div>";

// Botones
echo "<div style='text-align: center; padding-top: 10px;'>";
echo "<input type='submit' name='create_request' value='📤 Enviar Solicitud' 
       style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
              color: white; padding: 15px 40px; font-size: 16px; font-weight: bold; 
              border: none; border-radius: 8px; cursor: pointer; margin-right: 10px;
              box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);'>";
echo "<input type='button' value='❌ Cancelar' onclick='history.back();' 
       style='background: #6c757d; color: white; padding: 15px 40px; font-size: 16px; 
              border: none; border-radius: 8px; cursor: pointer;'>";
echo "</div>";

echo "</div>"; // Cierre del contenedor blanco
echo "</div>"; // Cierre del contenedor principal

Html::closeForm();
echo "</div>";

Html::footer();
