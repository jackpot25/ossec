<?php
# Check if required extensions are loaded.
if (!extension_loaded('remctl')) {
  die ("Failed to load remctl extension\n");
}

# Include security functions.
require 'Security.php';
require 'config.php';
# Init security db to check for r or w access.
$_SERVER['REMOTE_USER'] = 'darrenp1';
$db = dbinit($_SERVER['REMOTE_USER']);

$name = $_POST['Name'];
$start = $_GET['jtStartIndex'];
$pgSize = $_GET['jtPageSize'];
$sorting = $_GET['jtSorting'];
$sysadmin = $_POST['Sysadmin'];
$user = $_POST['User'];

if (isset($user) && $user != 'false' && $user != '') {
  $sysadmin = $user;
}
elseif (isset($sysadmin) && $sysadmin != 'false' && $sysadmin != '') {
  $sysadmin = $_SERVER['REMOTE_USER'];
} 

$systems = array_filter(explode("\n", shell_exec('sudo /var/ossec/bin/syscheck_control -ls')));
$headers = array ('SystemId', 'Name', 'IP', 'Active');

$hs = array();
$mdr = array();
if (isset($sysadmin) && $sysadmin != 'false' && $filter_by_sysadmin) {
  $command = array ('mdr', 'query', 'su_support is SA-CRC; su_sysadmin0');
  $r = remctl_new();
  remctl_set_ccache($r, $k5_ticket_cache);
  $result = remctl('frankoz1.stanford.edu', 0, '', $command);
  if ($result->error) {
    fatal_error_popup('Remctl frankoz1 mder for sysadmin list returned error: '.$result->error);
    return;
  }
  $mdr = array_filter(explode("\n", $result->stdout));
  foreach ($mdr as $s) {
    $s = explode(': ', $s);
    $hs[$s[0]] = $s[1];
  }
}
unset ($mdr);

$mysys = array();

# Search by name, check if sysadmin for filter.
if (isset($name) && $name != '') {
  foreach ($systems as $row) {
    $row = rtrim($row, ',');
    $row = str_getcsv($row);
    if (strpos($row[1], $name) !== false) {
      if (isset($sysadmin) && $sysadmin != 'false' && $filter_by_sysadmin) {
        $ln = explode("\s", $row[1])[0];
        if (has_r_access($db, $_SERVER['REMOTE_USER'], $ln)) {
          if ($hs[$ln] == $sysadmin) {
            $mysys[] = array_combine($headers, $row);
          }
        }
      } 
      else {
        if (has_r_access($db,$_SERVER['REMOTE_USER'],explode("\s",$row[1])[0])){
          $mysys[] = array_combine($headers, $row);
        }
      }
    }
  }
}
else {
  # Return all systems, still check if sysadmin for filter.
  foreach($systems as $row) {
    $row = rtrim($row, ',');
    $row = str_getcsv($row);
    if (isset($sysadmin) && $sysadmin != 'false' && $filter_by_sysadmin ) {
      $ln = explode("\s", $row[1])[0];
      if (has_r_access($db, $_SERVER['REMOTE_USER'],$ln)) {
        if ($hs[$ln] == $sysadmin) {
          $mysys[] = array_combine($headers, $row);
        }
      }
    } 
    else {
      if (has_r_access($db,$_SERVER['REMOTE_USER'],explode("\s", $row[1])[0])){
        $mysys[] = array_combine($headers, $row);
      }
    }
  }
}

# Sort on $sorting for results.
function cmp($a, $b) {
  global $sorting;
  list($sName, $order) = split (' ', $sorting);

  if ($order == 'ASC') {
    return strcmp($a[$sName], $b[$sName]);
  }
  else {
    return strcmp($b[$sName], $a[$sName]);
  }
}

usort($mysys, 'cmp');

$results = array_slice($mysys,$start,$pgSize);

$json['Result'] = 'OK';
$json['Records'] = $results;
$json['TotalRecordCount'] = count($mysys);
echo json_encode($json);
?>
