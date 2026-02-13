<?php
/**
 * Hooks del plugin Email Approval
 * 
 * Detecta creación de tickets específicos y dispara el proceso de aprobación
 */

/**
 * Install hook
 * 
 * @return boolean
 */
function plugin_emailapproval_install() {
   global $DB;
   
   // Crear tabla para almacenar tokens de aprobación
   $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_emailapproval_approvals` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `tickets_id` int(11) NOT NULL,
      `token` varchar(128) NOT NULL,
      `approver_email` varchar(255) NOT NULL,
      `status` enum('pending','approved','rejected','expired') NOT NULL DEFAULT 'pending',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `expires_at` timestamp NOT NULL,
      `responded_at` timestamp NULL DEFAULT NULL,
      `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
      `reminder_sent_at` timestamp NULL DEFAULT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` varchar(255) DEFAULT NULL,
      `teacher_name` varchar(255) DEFAULT NULL,
      `teacher_legajo` varchar(50) DEFAULT NULL,
      `teacher_email` varchar(255) DEFAULT NULL,
      `department_name` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `token` (`token`),
      KEY `tickets_id` (`tickets_id`),
      KEY `status` (`status`),
      KEY `expires_at` (`expires_at`),
      KEY `approver_email` (`approver_email`),
      KEY `teacher_legajo` (`teacher_legajo`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
   
   if (!$DB->query($query)) {
      return false;
   }
   
   // Crear tabla de auditoría
   $query_audit = "CREATE TABLE IF NOT EXISTS `glpi_plugin_emailapproval_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `approvals_id` int(11) NOT NULL,
      `tickets_id` int(11) NOT NULL,
      `action` varchar(50) NOT NULL,
      `message` text,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` varchar(255) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `approvals_id` (`approvals_id`),
      KEY `tickets_id` (`tickets_id`),
      KEY `action` (`action`),
      KEY `created_at` (`created_at`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
   
   if (!$DB->query($query_audit)) {
      return false;
   }
   
   // Crear configuración del plugin
   $config = new Config();
   $config->setConfigurationValues('plugin:emailapproval', [
      'approver_email' => '',
      'token_expiry_hours' => 48,
      'reminder_hours' => 48,
      'ticket_name_match' => 'Solicitud de correo electrónico institucional',
      'approved_status' => 5,
      'rejected_status' => 6,
   ]);
   
   // Registrar tareas automáticas (cron)
   CronTask::Register('PluginEmailapprovalApproval', 'sendReminders', 3600, [
      'comment' => 'Enviar recordatorios de aprobaciones pendientes',
      'mode' => CronTask::MODE_EXTERNAL
   ]);
   
   return true;
}

/**
 * Uninstall hook
 * 
 * @return boolean
 */
function plugin_emailapproval_uninstall() {
   global $DB;
   
   // Eliminar tablas
   $tables = [
      'glpi_plugin_emailapproval_approvals',
      'glpi_plugin_emailapproval_logs'
   ];
   
   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }
   
   // Eliminar configuración
   $config = new Config();
   $config->deleteConfigurationValues('plugin:emailapproval');
   
   // Eliminar tareas cron
   $DB->query("DELETE FROM `glpi_crontasks` WHERE `itemtype` = 'PluginEmailapprovalApproval'");
   
   return true;
}

/**
 * Hook ejecutado después de añadir un ticket
 * 
 * @param Ticket $item El ticket creado
 */
function plugin_emailapproval_item_add_ticket(Ticket $item) {
   
   // Verificar que el plugin está activo
   $plugin = new Plugin();
   if (!$plugin->isActivated('emailapproval')) {
      return;
   }
   
   // Obtener configuración
   $config = Config::getConfigurationValues('plugin:emailapproval');
   $ticket_name_match = $config['ticket_name_match'] ?? 'Solicitud de correo electrónico institucional';
   $approver_email = $config['approver_email'] ?? '';
   
   // Validar que hay un email configurado
   if (empty($approver_email)) {
      trigger_error(
         "Plugin Email Approval: No se ha configurado el email del aprobador. " .
         "Configure 'approver_email' en la configuración del plugin.",
         E_USER_WARNING
      );
      return;
   }
   
   // Obtener campos del ticket
   $ticket_name = $item->fields['name'] ?? '';
   
   // Verificar si el nombre del ticket coincide EXACTAMENTE
   if (trim($ticket_name) !== trim($ticket_name_match)) {
      // No es el tipo de ticket que nos interesa
      return;
   }
   
   // Este es el ticket que debe activar el proceso de aprobación
   $tickets_id = $item->getID();
   
   // Log para debugging
   Toolbox::logDebug("Plugin Email Approval: Detectado ticket #$tickets_id - '$ticket_name'");
   
   // Crear solicitud de aprobación
   $approval_id = PluginEmailapprovalApproval::createApprovalRequest(
      $tickets_id,
      $approver_email
   );
   
   if ($approval_id) {
      Toolbox::logInfo(
         "Plugin Email Approval: Solicitud de aprobación #$approval_id " .
         "creada para ticket #$tickets_id"
      );
   } else {
      Toolbox::logError(
         "Plugin Email Approval: Error al crear solicitud de aprobación " .
         "para ticket #$tickets_id"
      );
   }
}

/**
 * Hook para mostrar tab de aprobaciones en el ticket (opcional)
 * 
 * @param Ticket $item
 * @param int $withtemplate
 * @return array
 */
function plugin_emailapproval_getAddSearchOptions($itemtype) {
   $sopt = [];
   
   if ($itemtype === 'Ticket') {
      $sopt[5000] = [
         'table'         => 'glpi_plugin_emailapproval_approvals',
         'field'         => 'status',
         'name'          => 'Estado de Aprobación',
         'datatype'      => 'specific',
         'massiveaction' => false,
         'joinparams'    => [
            'jointype'   => 'child'
         ]
      ];
      
      $sopt[5001] = [
         'table'         => 'glpi_plugin_emailapproval_approvals',
         'field'         => 'approver_email',
         'name'          => 'Email Aprobador',
         'datatype'      => 'email',
         'massiveaction' => false,
         'joinparams'    => [
            'jointype'   => 'child'
         ]
      ];
      
      $sopt[5002] = [
         'table'         => 'glpi_plugin_emailapproval_approvals',
         'field'         => 'responded_at',
         'name'          => 'Fecha de Respuesta',
         'datatype'      => 'datetime',
         'massiveaction' => false,
         'joinparams'    => [
            'jointype'   => 'child'
         ]
      ];
   }
   
   return $sopt;
}
