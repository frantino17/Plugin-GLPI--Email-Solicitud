<?php
/**
 * Clase para agregar menú en GLPI
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginEmailapprovalMenu extends CommonGLPI {
   
   static $rightname = 'ticket';
   
   /**
    * Obtener nombre del menú
    */
   static function getMenuName() {
      return __('Solicitud Correo Docente', 'emailapproval');
   }
   
   /**
    * Obtener contenido del menú
    */
   static function getMenuContent() {
      global $CFG_GLPI;
      
      $menu = [];
      
      if (Session::haveRight('ticket', CREATE)) {
         $menu['title'] = self::getMenuName();
         $menu['page'] = Plugin::getWebDir('emailapproval') . '/front/request.form.php';
         $menu['icon'] = 'fa fa-envelope';
         
         $menu['options'] = [
            'request' => [
               'title' => __('Nueva Solicitud', 'emailapproval'),
               'page' => Plugin::getWebDir('emailapproval') . '/front/request.form.php',
               'icon' => 'fa fa-plus-circle'
            ]
         ];
      }
      
      return $menu;
   }
}
