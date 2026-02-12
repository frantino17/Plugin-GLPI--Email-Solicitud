<?php
/**
 * Plugin Email Approval para GLPI 11
 * 
 * Sistema de aprobación externa de solicitudes de correo institucional
 * mediante enlaces únicos enviados por email.
 * 
 * @version 1.0.0
 * @author Senior PHP Developer
 * @license GPLv2+
 */

define('PLUGIN_EMAILAPPROVAL_VERSION', '1.0.0');
define('PLUGIN_EMAILAPPROVAL_MIN_GLPI', '11.0.0');
define('PLUGIN_EMAILAPPROVAL_MAX_GLPI', '11.0.99');
define('PLUGIN_EMAILAPPROVAL_TOKEN_EXPIRY', 48 * 3600); // 48 horas en segundos

/**
 * Inicialización del plugin
 */
function plugin_init_emailapproval() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['emailapproval'] = true;
   
   // Registrar clase del plugin
   Plugin::registerClass('PluginEmailapprovalApproval', [
      'addtabon' => 'Ticket'
   ]);

   // Hook para detectar creación de tickets
   $PLUGIN_HOOKS['item_add']['emailapproval'] = [
      'Ticket' => 'plugin_emailapproval_item_add_ticket'
   ];

   // Hook para tareas automáticas (cron)
   $PLUGIN_HOOKS['cron']['emailapproval'] = [
      'PluginEmailapprovalApproval::cronSendReminders'
   ];

   // Añadir entrada de menú en herramientas (opcional)
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['menu_toadd']['emailapproval'] = [
         'tools' => 'PluginEmailapprovalApproval'
      ];
   }
   
   // Añadir entrada en el menú de Helpdesk para crear solicitud manual
   if (Session::haveRight('ticket', CREATE)) {
      $PLUGIN_HOOKS['menu_toadd']['emailapproval']['helpdesk'] = 'PluginEmailapprovalMenu';
   }
   
   // Registrar clase de menú
   Plugin::registerClass('PluginEmailapprovalMenu');
}

/**
 * Obtener nombre del plugin
 */
function plugin_version_emailapproval() {
   return [
      'name'           => 'Email Approval',
      'version'        => PLUGIN_EMAILAPPROVAL_VERSION,
      'author'         => 'Senior PHP Developer',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/yourrepo/emailapproval',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_EMAILAPPROVAL_MIN_GLPI,
            'max' => PLUGIN_EMAILAPPROVAL_MAX_GLPI
         ]
      ]
   ];
}

/**
 * Verificar prerequisitos antes de instalación
 */
function plugin_emailapproval_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_EMAILAPPROVAL_MIN_GLPI, 'lt')
       || version_compare(GLPI_VERSION, PLUGIN_EMAILAPPROVAL_MAX_GLPI, 'gt')) {
      echo "Este plugin requiere GLPI >= " . PLUGIN_EMAILAPPROVAL_MIN_GLPI . 
           " y <= " . PLUGIN_EMAILAPPROVAL_MAX_GLPI;
      return false;
   }
   
   // Verificar extensiones PHP necesarias
   if (!function_exists('random_bytes')) {
      echo "Se requiere la función random_bytes() para generar tokens seguros (PHP 7.0+)";
      return false;
   }
   
   return true;
}

/**
 * Verificar si la configuración del plugin es válida
 */
function plugin_emailapproval_check_config($verbose = false) {
   if ($verbose) {
      echo 'Instalado y configurado correctamente';
   }
   return true;
}
