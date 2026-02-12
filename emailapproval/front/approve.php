<?php
/**
 * Endpoint público para procesar aprobaciones/rechazos
 * 
 * Este archivo es accesible sin autenticación y procesa tokens únicos
 * 
 * URL: /plugins/emailapproval/front/approve.php?token=XXX&action=approve|reject
 */

// Deshabilitar autenticación para este endpoint público
define('GLPI_ROOT', dirname(dirname(dirname(dirname(__FILE__)))));
$SECURITY_STRATEGY = 'no_check'; // Necesario para GLPI 11

include(GLPI_ROOT . "/inc/includes.php");

// Iniciar sesión HTML
Html::nullHeader("Email Approval", '');

// Obtener parámetros
$token = $_GET['token'] ?? '';
$action = $_GET['action'] ?? '';

// Validar que tenemos los parámetros necesarios
if (empty($token) || empty($action)) {
   displayErrorPage(
      'Parámetros inválidos',
      'Faltan parámetros requeridos en la solicitud.',
      400
   );
   exit;
}

// Procesar la aprobación/rechazo
$result = PluginEmailapprovalApproval::processApproval($token, $action);

// Mostrar resultado al usuario
if ($result['success']) {
   displaySuccessPage($action, $result['message'], $result['data']);
} else {
   displayErrorPage(
      'No se pudo procesar la solicitud',
      $result['message'],
      403
   );
}

Html::nullFooter();
exit;

/**
 * Mostrar página de éxito
 */
function displaySuccessPage($action, $message, $data) {
   $title = ($action === 'approve') ? 'Solicitud Aprobada' : 'Solicitud Rechazada';
   $icon = ($action === 'approve') ? '✓' : '✗';
   $color = ($action === 'approve') ? '#28a745' : '#dc3545';
   
   echo '<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>' . $title . ' - GLPI</title>
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }
      body {
         font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 20px;
      }
      .container {
         background: white;
         border-radius: 16px;
         box-shadow: 0 20px 60px rgba(0,0,0,0.3);
         max-width: 600px;
         width: 100%;
         padding: 48px 40px;
         text-align: center;
         animation: slideIn 0.4s ease-out;
      }
      @keyframes slideIn {
         from {
            opacity: 0;
            transform: translateY(-20px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }
      .icon {
         font-size: 80px;
         color: ' . $color . ';
         margin-bottom: 24px;
         line-height: 1;
         animation: scaleIn 0.5s ease-out 0.2s both;
      }
      @keyframes scaleIn {
         from {
            transform: scale(0);
         }
         to {
            transform: scale(1);
         }
      }
      h1 {
         color: #333;
         font-size: 32px;
         margin-bottom: 16px;
         font-weight: 600;
      }
      .message {
         color: #666;
         font-size: 18px;
         line-height: 1.6;
         margin-bottom: 32px;
      }
      .info-box {
         background: #f8f9fa;
         border-left: 4px solid ' . $color . ';
         padding: 16px 20px;
         margin: 24px 0;
         text-align: left;
         border-radius: 4px;
      }
      .info-box p {
         margin: 8px 0;
         color: #555;
         font-size: 14px;
      }
      .info-box strong {
         color: #333;
      }
      .footer {
         margin-top: 32px;
         padding-top: 24px;
         border-top: 1px solid #e0e0e0;
         color: #999;
         font-size: 13px;
      }
      .security-note {
         background: #fff3cd;
         border: 1px solid #ffc107;
         border-radius: 8px;
         padding: 16px;
         margin-top: 24px;
         font-size: 13px;
         color: #856404;
      }
   </style>
</head>
<body>
   <div class="container">
      <div class="icon">' . $icon . '</div>
      <h1>' . $title . '</h1>
      <div class="message">
         ' . htmlspecialchars($message) . '
      </div>
      
      <div class="info-box">
         <p><strong>Ticket #' . ($data['tickets_id'] ?? 'N/A') . '</strong></p>
         <p>Aprobador: ' . htmlspecialchars($data['approver_email'] ?? 'N/A') . '</p>
         <p>Fecha: ' . date('d/m/Y H:i:s') . '</p>
      </div>
      
      <div class="security-note">
         <strong>⚠️ Importante:</strong> Este enlace ya no es válido y no puede ser utilizado nuevamente.
      </div>
      
      <div class="footer">
         Sistema de Gestión GLPI<br>
         Este proceso fue registrado en la auditoría del sistema
      </div>
   </div>
</body>
</html>';
}

/**
 * Mostrar página de error
 */
function displayErrorPage($title, $message, $code = 400) {
   http_response_code($code);
   
   echo '<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>' . $title . ' - GLPI</title>
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }
      body {
         font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
         background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 20px;
      }
      .container {
         background: white;
         border-radius: 16px;
         box-shadow: 0 20px 60px rgba(0,0,0,0.3);
         max-width: 600px;
         width: 100%;
         padding: 48px 40px;
         text-align: center;
         animation: slideIn 0.4s ease-out;
      }
      @keyframes slideIn {
         from {
            opacity: 0;
            transform: translateY(-20px);
         }
         to {
            opacity: 1;
            transform: translateY(0);
         }
      }
      .icon {
         font-size: 80px;
         color: #dc3545;
         margin-bottom: 24px;
         line-height: 1;
      }
      h1 {
         color: #333;
         font-size: 28px;
         margin-bottom: 16px;
         font-weight: 600;
      }
      .message {
         color: #666;
         font-size: 16px;
         line-height: 1.6;
         margin-bottom: 32px;
      }
      .error-code {
         display: inline-block;
         background: #f8f9fa;
         color: #999;
         padding: 8px 16px;
         border-radius: 20px;
         font-size: 13px;
         font-weight: 500;
         margin-top: 16px;
      }
      .suggestions {
         background: #f8f9fa;
         border-radius: 8px;
         padding: 24px;
         margin-top: 32px;
         text-align: left;
      }
      .suggestions h3 {
         color: #333;
         font-size: 16px;
         margin-bottom: 16px;
      }
      .suggestions ul {
         list-style: none;
         padding-left: 0;
      }
      .suggestions li {
         color: #666;
         font-size: 14px;
         padding: 8px 0;
         padding-left: 24px;
         position: relative;
      }
      .suggestions li:before {
         content: "•";
         position: absolute;
         left: 8px;
         color: #dc3545;
      }
      .footer {
         margin-top: 32px;
         padding-top: 24px;
         border-top: 1px solid #e0e0e0;
         color: #999;
         font-size: 13px;
      }
   </style>
</head>
<body>
   <div class="container">
      <div class="icon">⚠️</div>
      <h1>' . htmlspecialchars($title) . '</h1>
      <div class="message">
         ' . htmlspecialchars($message) . '
      </div>
      <div class="error-code">Código de error: ' . $code . '</div>
      
      <div class="suggestions">
         <h3>Posibles causas:</h3>
         <ul>
            <li>El enlace ya fue utilizado anteriormente</li>
            <li>El enlace ha expirado (más de 48 horas)</li>
            <li>El enlace está incompleto o fue modificado</li>
            <li>La solicitud ya fue procesada por otro medio</li>
         </ul>
      </div>
      
      <div class="footer">
         Si necesita ayuda, contacte con el administrador del sistema<br>
         Sistema de Gestión GLPI
      </div>
   </div>
</body>
</html>';
}
