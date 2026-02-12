<?php
/**
 * Tarea automática para enviar recordatorios
 * 
 * Esta clase se ejecuta automáticamente por el sistema de cron de GLPI
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginEmailapprovalCrontask extends CommonDBTM {
   
   /**
    * Instalar las tareas automáticas del plugin
    */
   public static function install() {
      CronTask::register(
         'PluginEmailapprovalApproval',
         'SendReminders',
         HOUR_TIMESTAMP, // Ejecutar cada hora
         [
            'comment' => 'Enviar recordatorios de aprobaciones pendientes después de 48 horas',
            'mode'    => CronTask::MODE_EXTERNAL,
            'state'   => CronTask::STATE_RUNNING
         ]
      );
      
      return true;
   }
   
   /**
    * Desinstalar las tareas automáticas
    */
   public static function uninstall() {
      return CronTask::unregister('emailapproval');
   }
}
