<?php
# Include security functions.
require 'Security.php';
require 'config.php';
# Init security db to check for r or w access.
$db = dbinit($_SERVER['REMOTE_USER']);

$name = $_POST['Name'];
if (!i_is_admin($name) && $limit_add_to_admin) {
  $json['Result'] = 'Error';
  $json['Message'] = 'Create is limited to admins by configuration!';
  echo json_encode($json);
  return;
}

if (checkdnsrr($name, 'A') or checkdnsrr($name, 'CNAME')) {
  if (has_w_access($db, $_SERVER['REMOTE_USER'], $name)) {
    $create = shell_exec("sudo /usr/local/bin/ossec-add $name 2>&1");
    $systems = array_filter(str_getcsv(shell_exec('sudo /var/ossec/bin/syscheck_control -ls'), "\n" ));
    $headers = array ('SystemId', 'Name', 'IP', 'Active');
    foreach ($systems as $line) {
      if (strpos($line, $name) !== false) {
        $line = rtrim($line, ',');
        $line = str_getcsv($line);
        $line = array_combine($headers, $line);

        $json['Result'] = 'OK';
        $json['Record'] = $line;
        # Rather than do string test again, just print and exit.
        echo json_encode($json);
        return;
      }
    }
  }
  else {
    $create = 'User does not have netdb access.';
  }
  $json['Result'] = 'Error';
  $json['Message'] = 'Failed to add or failed to find $name after adding. Refresh your page. Error message was: '.$create;
}
else {
  $json['Result'] = 'Error';
  $json['Message'] = 'Name provided did not resolve in DNS!';
}

echo json_encode($json);
?>
