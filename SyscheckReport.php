<?php
# Include security functions.
require 'Security.php';
# Init security db to check for r or w access.
$db = dbinit($_SERVER['REMOTE_USER']);

$id = $_GET['SystemId'];
$json = '';

# default failure message
$json['Result'] = 'Error';
$json['Message'] = "SystemId provided ($id) was not found.";
  
if (!is_numeric($id)) {
  $json['Result'] = "Error";
  $json['Message'] = "Provided SystemId is not an integer: $id";
}
else {
  $systems = array_filter(explode("\n", shell_exec("sudo /var/ossec/bin/syscheck_control -ls")));
  foreach ($systems as $row) {
    $r = array_filter(explode(",", $row));
    if ($r[0] == $id) {
      if (has_r_access($db, $_SERVER['REMOTE_USER'], $r[1])) {
        $results["Report"] = nl2br(htmlspecialchars(shell_exec("sudo /var/ossec/bin/syscheck_control -i $id")));
        $results["SystemId"] = $id;

        $json['Result'] = "OK";
        $json['Records'] = array($results);
      }
      else {
        $json['Result'] = 'Error';
        $json['Message'] = "Insufficient netdb privs for $r[1]";
      }
      break;
    }
  }
}
echo json_encode($json);
?>
