<?php
/**
 * Hooks del plugin Email Approval
 * 
 * Detecta creación de tickets específicos y dispara el proceso de aprobación
 */

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
