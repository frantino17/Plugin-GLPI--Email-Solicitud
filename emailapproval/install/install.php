<?php
/**
 * Script de instalación del plugin Email Approval
 */

/**
 * Instalar el plugin
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
      'approver_email' => '',  // Email del directivo (configurable)
      'token_expiry_hours' => 48,
      'reminder_hours' => 48,
      'ticket_name_match' => 'Solicitud de correo electrónico institucional',
      'approved_status' => 5,  // Estado cuando se aprueba (configurable)
      'rejected_status' => 6,  // Estado cuando se rechaza (configurable)
   ]);
   
   // Registrar tareas automáticas (cron)
   PluginEmailapprovalCrontask::install();
   
   return true;
}

/**
 * Desinstalar el plugin
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
   
   // Desinstalar tareas automáticas
   PluginEmailapprovalCrontask::uninstall();
   
   return true;
}
