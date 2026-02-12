<?php
/**
 * Página de configuración del plugin (opcional pero recomendada)
 * 
 * Permite configurar el email del aprobador y otros parámetros
 */

include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

Html::header('Email Approval', $_SERVER['PHP_SELF'], "config", "plugins");

$config = Config::getConfigurationValues('plugin:emailapproval');

if (isset($_POST['update_config'])) {
   $new_config = [
      'approver_email' => $_POST['approver_email'] ?? '',
      'token_expiry_hours' => intval($_POST['token_expiry_hours'] ?? 48),
      'reminder_hours' => intval($_POST['reminder_hours'] ?? 48),
      'ticket_name_match' => $_POST['ticket_name_match'] ?? 'Solicitud de correo electrónico institucional',
      'approved_status' => intval($_POST['approved_status'] ?? 5),
      'rejected_status' => intval($_POST['rejected_status'] ?? 6),
   ];
   
   $config_obj = new Config();
   $config_obj->setConfigurationValues('plugin:emailapproval', $new_config);
   
   Session::addMessageAfterRedirect('Configuración actualizada correctamente', false, INFO);
   Html::back();
}

echo "<div class='center'>";
echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";

echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>Configuración del Plugin Email Approval</th></tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>Email del Directivo Aprobador:</td>";
echo "<td><input type='email' name='approver_email' value='".$config['approver_email']."' size='50' required></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>Horas de Expiración del Token:</td>";
echo "<td><input type='number' name='token_expiry_hours' value='".$config['token_expiry_hours']."' min='1' max='168'></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>Horas antes de Recordatorio:</td>";
echo "<td><input type='number' name='reminder_hours' value='".$config['reminder_hours']."' min='1' max='168'></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>Nombre Exacto del Ticket:</td>";
echo "<td><input type='text' name='ticket_name_match' value='".$config['ticket_name_match']."' size='50'></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>Estado cuando se Aprueba (ID):</td>";
echo "<td><input type='number' name='approved_status' value='".$config['approved_status']."' min='1'></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>Estado cuando se Rechaza (ID):</td>";
echo "<td><input type='number' name='rejected_status' value='".$config['rejected_status']."' min='1'></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td colspan='2' class='center'>";
echo "<input type='submit' name='update_config' value='Actualizar Configuración' class='submit'>";
echo "</td>";
echo "</tr>";

echo "</table>";
Html::closeForm();

// Mostrar estadísticas
global $DB;

$stats = [
   'pending' => 0,
   'approved' => 0,
   'rejected' => 0,
   'expired' => 0
];

$result = $DB->request([
   'SELECT' => ['status', 'COUNT(*) AS total'],
   'FROM' => 'glpi_plugin_emailapproval_approvals',
   'GROUP' => 'status'
]);

foreach ($result as $row) {
   $stats[$row['status']] = $row['total'];
}

echo "<br><table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>Estadísticas</th></tr>";
echo "<tr class='tab_bg_1'><td>Pendientes:</td><td><b>".$stats['pending']."</b></td></tr>";
echo "<tr class='tab_bg_1'><td>Aprobadas:</td><td><span style='color:green'><b>".$stats['approved']."</b></span></td></tr>";
echo "<tr class='tab_bg_1'><td>Rechazadas:</td><td><span style='color:red'><b>".$stats['rejected']."</b></span></td></tr>";
echo "<tr class='tab_bg_1'><td>Expiradas:</td><td><span style='color:orange'><b>".$stats['expired']."</b></span></td></tr>";
echo "</table>";

echo "</div>";

Html::footer();
