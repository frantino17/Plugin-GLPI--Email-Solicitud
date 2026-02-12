<?php
/**
 * Clase principal de gestión de aprobaciones
 * 
 * Gestiona tokens, validación, aprobaciones/rechazos y auditoría
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginEmailapprovalApproval extends CommonDBTM {
   
   static $rightname = 'ticket';
   
   /**
    * Generar token criptográficamente seguro
    * 
    * @return string Token hexadecimal de 64 caracteres (256 bits)
    */
   public static function generateSecureToken() {
      return bin2hex(random_bytes(32));
   }
   
   /**
    * Crear solicitud de aprobación y enviar email
    * 
    * @param int $tickets_id ID del ticket
    * @param string $approver_email Email del directivo
    * @return bool|int ID de la aprobación creada o false
    */
   public static function createApprovalRequest($tickets_id, $approver_email) {
      global $DB, $CFG_GLPI;
      
      // Validar email
      if (!filter_var($approver_email, FILTER_VALIDATE_EMAIL)) {
         trigger_error("Email del aprobador no válido: $approver_email", E_USER_WARNING);
         return false;
      }
      
      // Verificar que el ticket existe
      $ticket = new Ticket();
      if (!$ticket->getFromDB($tickets_id)) {
         trigger_error("Ticket $tickets_id no encontrado", E_USER_WARNING);
         return false;
      }
      
      // Verificar si ya existe una aprobación pendiente para este ticket
      $existing = $DB->request([
         'FROM' => 'glpi_plugin_emailapproval_approvals',
         'WHERE' => [
            'tickets_id' => $tickets_id,
            'status' => 'pending'
         ]
      ]);
      
      if (count($existing) > 0) {
         trigger_error("Ya existe una aprobación pendiente para el ticket $tickets_id", E_USER_NOTICE);
         return false;
      }
      
      // Generar token seguro
      $token = self::generateSecureToken();
      
      // Calcular fecha de expiración
      $config = Config::getConfigurationValues('plugin:emailapproval');
      $expiry_hours = $config['token_expiry_hours'] ?? 48;
      $expires_at = date('Y-m-d H:i:s', time() + ($expiry_hours * 3600));
      
      // Insertar en base de datos
      $result = $DB->insert('glpi_plugin_emailapproval_approvals', [
         'tickets_id' => $tickets_id,
         'token' => $token,
         'approver_email' => $approver_email,
         'status' => 'pending',
         'expires_at' => $expires_at,
         'created_at' => date('Y-m-d H:i:s')
      ]);
      
      if (!$result) {
         trigger_error("Error al crear registro de aprobación", E_USER_WARNING);
         return false;
      }
      
      $approval_id = $DB->insertId();
      
      // Registrar en auditoría
      self::logAction($approval_id, $tickets_id, 'created', 
         "Solicitud de aprobación creada para $approver_email");
      
      // Enviar email
      if (!self::sendApprovalEmail($approval_id, $ticket, $token, $approver_email)) {
         trigger_error("Error al enviar email de aprobación", E_USER_WARNING);
         // No retornamos false porque el registro ya fue creado
      }
      
      // Añadir seguimiento al ticket
      $ticketFollowup = new ITILFollowup();
      $ticketFollowup->add([
         'itemtype' => 'Ticket',
         'items_id' => $tickets_id,
         'users_id' => 0,
         'content' => sprintf(
            "Se ha enviado solicitud de aprobación a %s.\n\nToken: %s\nExpira: %s",
            $approver_email,
            substr($token, 0, 8) . '...',
            $expires_at
         ),
         'is_private' => 1,
         'date' => date('Y-m-d H:i:s')
      ]);
      
      return $approval_id;
   }
   
   /**
    * Crear solicitud de aprobación manual con datos del docente
    * 
    * @param int $tickets_id ID del ticket
    * @param string $approver_email Email del responsable
    * @param array $teacher_data Datos del docente
    * @return bool|int ID de la aprobación creada o false
    */
   public static function createApprovalRequestManual($tickets_id, $approver_email, $teacher_data) {
      global $DB, $CFG_GLPI;
      
      // Validar email
      if (!filter_var($approver_email, FILTER_VALIDATE_EMAIL)) {
         trigger_error("Email del aprobador no válido: $approver_email", E_USER_WARNING);
         return false;
      }
      
      // Verificar que el ticket existe
      $ticket = new Ticket();
      if (!$ticket->getFromDB($tickets_id)) {
         trigger_error("Ticket $tickets_id no encontrado", E_USER_WARNING);
         return false;
      }
      
      // Generar token seguro
      $token = self::generateSecureToken();
      
      // Calcular fecha de expiración
      $config = Config::getConfigurationValues('plugin:emailapproval');
      $expiry_hours = $config['token_expiry_hours'] ?? 48;
      $expires_at = date('Y-m-d H:i:s', time() + ($expiry_hours * 3600));
      
      // Insertar en base de datos con datos adicionales
      $result = $DB->insert('glpi_plugin_emailapproval_approvals', [
         'tickets_id' => $tickets_id,
         'token' => $token,
         'approver_email' => $approver_email,
         'status' => 'pending',
         'expires_at' => $expires_at,
         'created_at' => date('Y-m-d H:i:s'),
         'teacher_name' => $teacher_data['teacher_name'] ?? '',
         'teacher_legajo' => $teacher_data['teacher_legajo'] ?? '',
         'teacher_email' => $teacher_data['teacher_email'] ?? '',
         'department_name' => $teacher_data['department_name'] ?? ''
      ]);
      
      if (!$result) {
         trigger_error("Error al crear registro de aprobación", E_USER_WARNING);
         return false;
      }
      
      $approval_id = $DB->insertId();
      
      // Registrar en auditoría
      self::logAction($approval_id, $tickets_id, 'created', 
         "Solicitud manual creada para docente: {$teacher_data['teacher_name']}");
      
      // Enviar email con datos del docente
      if (!self::sendApprovalEmailDocente($approval_id, $ticket, $token, $approver_email, $teacher_data)) {
         trigger_error("Error al enviar email de aprobación", E_USER_WARNING);
      }
      
      // Añadir seguimiento al ticket
      $ticketFollowup = new ITILFollowup();
      $ticketFollowup->add([
         'itemtype' => 'Ticket',
         'items_id' => $tickets_id,
         'users_id' => 0,
         'content' => sprintf(
            "📧 Solicitud de aprobación enviada\n\n" .
            "Docente: %s\n" .
            "Legajo: %s\n" .
            "Email solicitado: %s\n" .
            "Departamento: %s\n\n" .
            "Enviado a: %s\n" .
            "Token: %s\n" .
            "Expira: %s",
            $teacher_data['teacher_name'],
            $teacher_data['teacher_legajo'],
            $teacher_data['teacher_email'],
            $teacher_data['department_name'],
            $approver_email,
            substr($token, 0, 8) . '...',
            $expires_at
         ),
         'is_private' => 1,
         'date' => date('Y-m-d H:i:s')
      ]);
      
      return $approval_id;
   }
   
   /**
    * Enviar email de aprobación con plantilla HTML bonita para docentes
    * 
    * @param int $approval_id
    * @param Ticket $ticket
    * @param string $token
    * @param string $approver_email
    * @param array $teacher_data
    * @return bool
    */
   private static function sendApprovalEmailDocente($approval_id, $ticket, $token, $approver_email, $teacher_data) {
      global $CFG_GLPI;
      
      // Construir URLs de aprobación/rechazo
      $base_url = rtrim($CFG_GLPI['url_base'], '/');
      $approve_url = "$base_url/plugins/emailapproval/front/approve.php?token=$token&action=approve";
      $reject_url = "$base_url/plugins/emailapproval/front/approve.php?token=$token&action=reject";
      $ticket_url = "$base_url/front/ticket.form.php?id=" . $ticket->getID();
      
      $subject = "📧 Solicitud de Correo Institucional Docente - Aprobación Requerida";
      
      // Crear email HTML bonito
      $body_html = self::getEmailTemplate($teacher_data, $approve_url, $reject_url, $ticket_url, $ticket->getID());
      
      // Versión texto plano (fallback)
      $body_text = self::getEmailTemplatePlainText($teacher_data, $approve_url, $reject_url, $ticket_url, $ticket->getID());
      
      // Enviar email usando la API de GLPI
      $mmail = new GLPIMailer();
      $mmail->AddAddress($approver_email);
      $mmail->Subject = $subject;
      $mmail->Body = $body_html;
      $mmail->AltBody = $body_text;
      $mmail->isHTML(true);
      
      $sent = $mmail->send();
      
      if ($sent) {
         self::logAction($approval_id, $ticket->getID(), 'email_sent', 
            "Email de aprobación enviado a $approver_email para docente {$teacher_data['teacher_name']}");
      } else {
         self::logAction($approval_id, $ticket->getID(), 'email_failed', 
            "Error al enviar email a $approver_email: " . $mmail->ErrorInfo);
      }
      
      return $sent;
   }
   
   /**
    * Obtener plantilla HTML del email (bonita y profesional)
    */
   private static function getEmailTemplate($data, $approve_url, $reject_url, $ticket_url, $ticket_id) {
      $html = '<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Solicitud de Aprobación</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
   <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
      <tr>
         <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
               
               <!-- Header -->
               <tr>
                  <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                     <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                        📧 Solicitud de Correo Institucional
                     </h1>
                     <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">
                        Aprobación Requerida - Ticket #' . $ticket_id . '
                     </p>
                  </td>
               </tr>
               
               <!-- Saludo -->
               <tr>
                  <td style="padding: 30px 40px 20px 40px;">
                     <p style="margin: 0; font-size: 16px; color: #333333; line-height: 1.6;">
                        Estimado/a <strong>Responsable de ' . htmlspecialchars($data['department_name']) . '</strong>,
                     </p>
                     <p style="margin: 15px 0 0 0; font-size: 15px; color: #555555; line-height: 1.6;">
                        Se requiere su aprobación para la siguiente solicitud de creación de correo electrónico institucional:
                     </p>
                  </td>
               </tr>
               
               <!-- Datos del Docente -->
               <tr>
                  <td style="padding: 0 40px;">
                     <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9ff; border-radius: 8px; border-left: 4px solid #667eea;">
                        <tr>
                           <td style="padding: 20px;">
                              <h2 style="margin: 0 0 15px 0; color: #667eea; font-size: 18px; font-weight: bold;">
                                 👤 Datos del Docente
                              </h2>
                              <table width="100%" cellpadding="8" cellspacing="0">
                                 <tr>
                                    <td style="font-weight: bold; color: #555; width: 40%; font-size: 14px;">Nombre completo:</td>
                                    <td style="color: #333; font-size: 14px;">' . htmlspecialchars($data['teacher_name']) . '</td>
                                 </tr>
                                 <tr>
                                    <td style="font-weight: bold; color: #555; font-size: 14px;">Nro. de Legajo:</td>
                                    <td style="color: #333; font-size: 14px;">' . htmlspecialchars($data['teacher_legajo']) . '</td>
                                 </tr>
                                 <tr>
                                    <td style="font-weight: bold; color: #555; font-size: 14px;">Email solicitado:</td>
                                    <td style="color: #667eea; font-weight: bold; font-size: 14px;">' . htmlspecialchars($data['teacher_email']) . '</td>
                                 </tr>
                                 <tr>
                                    <td style="font-weight: bold; color: #555; font-size: 14px;">Departamento/Área:</td>
                                    <td style="color: #333; font-size: 14px;">' . htmlspecialchars($data['department_name']) . '</td>
                                 </tr>
                              </table>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               
               <!-- Mensaje de Acción -->
               <tr>
                  <td style="padding: 25px 40px;">
                     <p style="margin: 0; font-size: 15px; color: #333333; line-height: 1.6; text-align: center;">
                        Como <strong>responsable del departamento/área ' . htmlspecialchars($data['department_name']) . '</strong>,<br>
                        debe <strong>aceptar o rechazar</strong> la creación de esta cuenta de correo institucional.
                     </p>
                  </td>
               </tr>
               
               <!-- Botones de Acción -->
               <tr>
                  <td style="padding: 10px 40px 30px 40px;">
                     <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                           <td width="48%" align="center">
                              <a href="' . $approve_url . '" style="display: block; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #ffffff; text-decoration: none; padding: 16px 20px; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                                 ✅ APROBAR SOLICITUD
                              </a>
                           </td>
                           <td width="4%"></td>
                           <td width="48%" align="center">
                              <a href="' . $reject_url . '" style="display: block; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #ffffff; text-decoration: none; padding: 16px 20px; border-radius: 8px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
                                 ❌ RECHAZAR SOLICITUD
                              </a>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               
               <!-- Información Importante -->
               <tr>
                  <td style="padding: 0 40px 30px 40px;">
                     <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                        <tr>
                           <td style="padding: 20px;">
                              <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px; font-weight: bold;">
                                 ⚠️ Información Importante
                              </h3>
                              <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 13px; line-height: 1.8;">
                                 <li>Este enlace es <strong>único y de un solo uso</strong></li>
                                 <li>Expira en <strong>48 horas</strong></li>
                                 <li>No comparta este enlace con terceros</li>
                                 <li>Su decisión quedará registrada en el sistema</li>
                              </ul>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               
               <!-- Link al Ticket -->
               <tr>
                  <td style="padding: 0 40px 30px 40px; text-align: center;">
                     <p style="margin: 0 0 10px 0; font-size: 13px; color: #666;">
                        Para ver más detalles del ticket:
                     </p>
                     <a href="' . $ticket_url . '" style="color: #667eea; text-decoration: none; font-weight: bold; font-size: 14px;">
                        🔗 Ver Ticket #' . $ticket_id . ' en GLPI
                     </a>
                  </td>
               </tr>
               
               <!-- Footer -->
               <tr>
                  <td style="background-color: #f8f9fa; padding: 25px 40px; text-align: center; border-top: 1px solid #e0e0e0;">
                     <p style="margin: 0 0 5px 0; font-size: 13px; color: #666;">
                        Este es un mensaje automático del Sistema de Gestión GLPI
                     </p>
                     <p style="margin: 0; font-size: 12px; color: #999;">
                        ' . date('d/m/Y H:i') . ' | No responder a este email
                     </p>
                  </td>
               </tr>
               
            </table>
         </td>
      </tr>
   </table>
</body>
</html>';
      
      return $html;
   }
   
   /**
    * Obtener plantilla de texto plano (fallback)
    */
   private static function getEmailTemplatePlainText($data, $approve_url, $reject_url, $ticket_url, $ticket_id) {
      $text = "═══════════════════════════════════════════════════════════════\n";
      $text .= "  SOLICITUD DE CORREO INSTITUCIONAL DOCENTE\n";
      $text .= "  Ticket #$ticket_id - Aprobación Requerida\n";
      $text .= "═══════════════════════════════════════════════════════════════\n\n";
      
      $text .= "Estimado/a Responsable de " . $data['department_name'] . ",\n\n";
      
      $text .= "Se requiere su aprobación para la siguiente solicitud:\n\n";
      
      $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $text .= "DATOS DEL DOCENTE\n";
      $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
      $text .= "Nombre completo:      " . $data['teacher_name'] . "\n";
      $text .= "Nro. de Legajo:       " . $data['teacher_legajo'] . "\n";
      $text .= "Email solicitado:     " . $data['teacher_email'] . "\n";
      $text .= "Departamento/Área:    " . $data['department_name'] . "\n\n";
      
      $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
      
      $text .= "Como responsable del departamento/área " . $data['department_name'] . ",\n";
      $text .= "debe ACEPTAR o RECHAZAR la creación de esta cuenta de correo institucional.\n\n";
      
      $text .= "Por favor, haga clic en uno de los siguientes enlaces:\n\n";
      $text .= "✅ APROBAR: $approve_url\n\n";
      $text .= "❌ RECHAZAR: $reject_url\n\n";
      
      $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $text .= "INFORMACIÓN IMPORTANTE\n";
      $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
      $text .= "• Este enlace es único y de un solo uso\n";
      $text .= "• Expira en 48 horas\n";
      $text .= "• No comparta este enlace con terceros\n";
      $text .= "• Su decisión quedará registrada en el sistema\n\n";
      
      $text .= "Ver ticket completo: $ticket_url\n\n";
      
      $text .= "═══════════════════════════════════════════════════════════════\n";
      $text .= "Sistema de Gestión GLPI - " . date('d/m/Y H:i') . "\n";
      $text .= "Este es un mensaje automático. No responder a este email.\n";
      $text .= "═══════════════════════════════════════════════════════════════\n";
      
      return $text;
   }
   
   /**
    * Enviar email de aprobación con enlaces únicos
    * 
    * @param int $approval_id
    * @param Ticket $ticket
    * @param string $token
    * @param string $approver_email
    * @return bool
    */
   private static function sendApprovalEmail($approval_id, $ticket, $token, $approver_email) {
      global $CFG_GLPI;
      
      // Construir URLs de aprobación/rechazo
      $base_url = rtrim($CFG_GLPI['url_base'], '/');
      $approve_url = "$base_url/plugins/emailapproval/front/approve.php?token=$token&action=approve";
      $reject_url = "$base_url/plugins/emailapproval/front/approve.php?token=$token&action=reject";
      
      // Preparar contenido del email
      $ticket_url = "$base_url/front/ticket.form.php?id=" . $ticket->getID();
      
      $subject = "[GLPI] Aprobación requerida: Solicitud de correo institucional";
      
      $body = "Estimado/a Director/a,\n\n";
      $body .= "Se requiere su aprobación para la siguiente solicitud:\n\n";
      $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $body .= "TICKET #" . $ticket->getID() . "\n";
      $body .= "Título: " . $ticket->fields['name'] . "\n";
      $body .= "Solicitante: " . getUserName($ticket->fields['users_id_recipient']) . "\n";
      $body .= "Fecha: " . Html::convDateTime($ticket->fields['date']) . "\n";
      $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
      
      if (!empty($ticket->fields['content'])) {
         $body .= "Descripción:\n" . Html::clean(Html::entity_decode_deep($ticket->fields['content'])) . "\n\n";
      }
      
      $body .= "Por favor, indique su decisión haciendo clic en uno de los siguientes enlaces:\n\n";
      $body .= "✓ APROBAR: $approve_url\n\n";
      $body .= "✗ RECHAZAR: $reject_url\n\n";
      $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $body .= "IMPORTANTE:\n";
      $body .= "- Este enlace es único y de un solo uso\n";
      $body .= "- Expira en 48 horas\n";
      $body .= "- No comparta este enlace con terceros\n";
      $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
      $body .= "Si necesita más información, puede ver el ticket completo en:\n";
      $body .= "$ticket_url\n\n";
      $body .= "Este es un mensaje automático de GLPI.\n";
      
      // Enviar email usando la API de GLPI
      $mmail = new GLPIMailer();
      $mmail->AddAddress($approver_email);
      $mmail->Subject = $subject;
      $mmail->Body = $body;
      $mmail->isHTML(false);
      
      $sent = $mmail->send();
      
      if ($sent) {
         self::logAction($approval_id, $ticket->getID(), 'email_sent', 
            "Email de aprobación enviado a $approver_email");
      } else {
         self::logAction($approval_id, $ticket->getID(), 'email_failed', 
            "Error al enviar email a $approver_email: " . $mmail->ErrorInfo);
      }
      
      return $sent;
   }
   
   /**
    * Validar y procesar token de aprobación/rechazo
    * 
    * @param string $token Token recibido
    * @param string $action 'approve' o 'reject'
    * @return array ['success' => bool, 'message' => string, 'data' => array]
    */
   public static function processApproval($token, $action) {
      global $DB;
      
      // Validar acción
      if (!in_array($action, ['approve', 'reject'])) {
         return [
            'success' => false,
            'message' => 'Acción no válida',
            'data' => null
         ];
      }
      
      // Validar formato del token
      if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
         self::logSecurityEvent(null, 'invalid_token_format', $token);
         return [
            'success' => false,
            'message' => 'Token no válido',
            'data' => null
         ];
      }
      
      // Buscar el token en la base de datos
      $iterator = $DB->request([
         'FROM' => 'glpi_plugin_emailapproval_approvals',
         'WHERE' => ['token' => $token]
      ]);
      
      if (count($iterator) === 0) {
         self::logSecurityEvent(null, 'token_not_found', $token);
         return [
            'success' => false,
            'message' => 'Token no encontrado o ya utilizado',
            'data' => null
         ];
      }
      
      $approval = $iterator->current();
      
      // Verificar estado (debe ser pending)
      if ($approval['status'] !== 'pending') {
         self::logSecurityEvent($approval['id'], 'token_already_used', 
            "Estado actual: {$approval['status']}");
         return [
            'success' => false,
            'message' => 'Este enlace ya ha sido utilizado anteriormente',
            'data' => $approval
         ];
      }
      
      // Verificar expiración
      if (strtotime($approval['expires_at']) < time()) {
         // Marcar como expirado
         $DB->update('glpi_plugin_emailapproval_approvals', [
            'status' => 'expired'
         ], [
            'id' => $approval['id']
         ]);
         
         self::logAction($approval['id'], $approval['tickets_id'], 'expired', 
            'Token expirado al intentar usarlo');
         
         return [
            'success' => false,
            'message' => 'Este enlace ha expirado (más de 48 horas)',
            'data' => $approval
         ];
      }
      
      // Procesar aprobación/rechazo
      $new_status = ($action === 'approve') ? 'approved' : 'rejected';
      
      $DB->update('glpi_plugin_emailapproval_approvals', [
         'status' => $new_status,
         'responded_at' => date('Y-m-d H:i:s'),
         'ip_address' => self::getClientIP(),
         'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
      ], [
         'id' => $approval['id']
      ]);
      
      // Actualizar ticket
      self::updateTicketStatus($approval['tickets_id'], $action, $approval['approver_email']);
      
      // Registrar en auditoría
      self::logAction($approval['id'], $approval['tickets_id'], $action, 
         "Solicitud {$new_status} por {$approval['approver_email']}");
      
      return [
         'success' => true,
         'message' => $action === 'approve' ? 
            'Solicitud aprobada correctamente' : 
            'Solicitud rechazada correctamente',
         'data' => $approval
      ];
   }
   
   /**
    * Actualizar estado del ticket según la decisión
    * 
    * @param int $tickets_id
    * @param string $action
    * @param string $approver_email
    */
   private static function updateTicketStatus($tickets_id, $action, $approver_email) {
      $config = Config::getConfigurationValues('plugin:emailapproval');
      
      $ticket = new Ticket();
      $ticket->getFromDB($tickets_id);
      
      // Determinar nuevo estado
      if ($action === 'approve') {
         $new_status = $config['approved_status'] ?? 5; // 5 = Resuelto
         $message = "✓ SOLICITUD APROBADA\n\n";
         $message .= "El directivo ha aprobado esta solicitud.\n";
         $message .= "Aprobado por: $approver_email\n";
         $message .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
      } else {
         $new_status = $config['rejected_status'] ?? 6; // 6 = Cerrado
         $message = "✗ SOLICITUD RECHAZADA\n\n";
         $message .= "El directivo ha rechazado esta solicitud.\n";
         $message .= "Rechazado por: $approver_email\n";
         $message .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
      }
      
      // Actualizar estado del ticket
      $ticket->update([
         'id' => $tickets_id,
         'status' => $new_status
      ]);
      
      // Añadir seguimiento al ticket
      $ticketFollowup = new ITILFollowup();
      $ticketFollowup->add([
         'itemtype' => 'Ticket',
         'items_id' => $tickets_id,
         'users_id' => 0,
         'content' => $message,
         'is_private' => 0,
         'date' => date('Y-m-d H:i:s')
      ]);
   }
   
   /**
    * Tarea CRON: Enviar recordatorios de aprobaciones pendientes
    * 
    * @param CronTask $task
    * @return int Número de recordatorios enviados
    */
   public static function cronSendReminders($task) {
      global $DB;
      
      $config = Config::getConfigurationValues('plugin:emailapproval');
      $reminder_hours = $config['reminder_hours'] ?? 48;
      $reminder_time = date('Y-m-d H:i:s', time() - ($reminder_hours * 3600));
      
      $count = 0;
      
      // Buscar aprobaciones pendientes sin recordatorio enviado
      $iterator = $DB->request([
         'FROM' => 'glpi_plugin_emailapproval_approvals',
         'WHERE' => [
            'status' => 'pending',
            'reminder_sent' => 0,
            'created_at' => ['<', $reminder_time],
            'expires_at' => ['>', date('Y-m-d H:i:s')] // No expiradas
         ]
      ]);
      
      foreach ($iterator as $approval) {
         if (self::sendReminder($approval)) {
            $count++;
         }
      }
      
      $task->addVolume($count);
      return 1;
   }
   
   /**
    * Enviar email de recordatorio
    * 
    * @param array $approval
    * @return bool
    */
   private static function sendReminder($approval) {
      global $DB, $CFG_GLPI;
      
      $ticket = new Ticket();
      if (!$ticket->getFromDB($approval['tickets_id'])) {
         return false;
      }
      
      // Construir URLs
      $base_url = rtrim($CFG_GLPI['url_base'], '/');
      $approve_url = "$base_url/plugins/emailapproval/front/approve.php?token={$approval['token']}&action=approve";
      $reject_url = "$base_url/plugins/emailapproval/front/approve.php?token={$approval['token']}&action=reject";
      
      $hours_remaining = round((strtotime($approval['expires_at']) - time()) / 3600);
      
      $subject = "[GLPI] RECORDATORIO: Aprobación pendiente - Correo institucional";
      
      $body = "Estimado/a Director/a,\n\n";
      $body .= "Le recordamos que tiene una solicitud de aprobación pendiente:\n\n";
      $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
      $body .= "TICKET #" . $ticket->getID() . "\n";
      $body .= "Título: " . $ticket->fields['name'] . "\n";
      $body .= "⚠️  Expira en: $hours_remaining horas\n";
      $body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
      $body .= "Por favor, indique su decisión:\n\n";
      $body .= "✓ APROBAR: $approve_url\n\n";
      $body .= "✗ RECHAZAR: $reject_url\n\n";
      $body .= "Este es un recordatorio automático.\n";
      
      $mmail = new GLPIMailer();
      $mmail->AddAddress($approval['approver_email']);
      $mmail->Subject = $subject;
      $mmail->Body = $body;
      $mmail->isHTML(false);
      
      $sent = $mmail->send();
      
      if ($sent) {
         // Marcar recordatorio como enviado
         $DB->update('glpi_plugin_emailapproval_approvals', [
            'reminder_sent' => 1,
            'reminder_sent_at' => date('Y-m-d H:i:s')
         ], [
            'id' => $approval['id']
         ]);
         
         self::logAction($approval['id'], $approval['tickets_id'], 'reminder_sent', 
            "Recordatorio enviado a {$approval['approver_email']}");
      }
      
      return $sent;
   }
   
   /**
    * Registrar acción en log de auditoría
    * 
    * @param int $approval_id
    * @param int $tickets_id
    * @param string $action
    * @param string $message
    */
   private static function logAction($approval_id, $tickets_id, $action, $message) {
      global $DB;
      
      $DB->insert('glpi_plugin_emailapproval_logs', [
         'approvals_id' => $approval_id,
         'tickets_id' => $tickets_id,
         'action' => $action,
         'message' => $message,
         'ip_address' => self::getClientIP(),
         'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
         'created_at' => date('Y-m-d H:i:s')
      ]);
   }
   
   /**
    * Registrar evento de seguridad
    */
   private static function logSecurityEvent($approval_id, $action, $details) {
      global $DB;
      
      $DB->insert('glpi_plugin_emailapproval_logs', [
         'approvals_id' => $approval_id ?? 0,
         'tickets_id' => 0,
         'action' => 'security_' . $action,
         'message' => $details,
         'ip_address' => self::getClientIP(),
         'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
         'created_at' => date('Y-m-d H:i:s')
      ]);
   }
   
   /**
    * Obtener IP del cliente (considerando proxies)
    * 
    * @return string
    */
   private static function getClientIP() {
      if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
         $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
      } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
         $ip = $_SERVER['HTTP_X_REAL_IP'];
      } else {
         $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
      }
      
      return trim($ip);
   }
   
   /**
    * Definir información para tareas CRON
    */
   public static function cronInfo($name) {
      switch ($name) {
         case 'SendReminders':
            return [
               'description' => 'Enviar recordatorios de aprobaciones pendientes',
               'parameter' => 'Ninguno'
            ];
      }
      return [];
   }
}
