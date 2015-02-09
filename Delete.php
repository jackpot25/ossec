<?php
# Include security functions.
require 'Security.php';
# Init security db to check for r or w access.
$db = dbinit($_SERVER['REMOTE_USER']);

$id = $_POST['SystemId'];
$delete;
$systems = array();
$systems = shell_exec("sudo /var/ossec/bin/syscheck_control -ls 2>&1");
# Set error prior to trying to loop through systems.
$json['Result'] = "Error";
$json['Message'] = "Failed to list systems";

foreach (str_getcsv($systems, "\n") as $line) {
  $vals = str_getcsv($line);
  if ($vals[0] == $id) {
    $name = $vals[1];
    if (has_w_access($db,$_SERVER['REMOTE_USER'],$name)) {
      $delete = shell_exec("sudo /usr/local/bin/ossec-del $name 2>&1");
      if ($delete != '') {
        $json['Result'] = "Error";
        $json['Message'] = "Failed to delete $name: $delete";
      }
      else {
        $json['Result'] = "OK";
      }
    }
    else {
      $json['Message'] = "Failed to delete $name: insufficient netdb privs";
    }
  }
}
echo json_encode($json);
?>
