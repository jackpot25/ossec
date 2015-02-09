<?php
$hs = array();
$mdr = array();
$mdr = array_filter(explode("\n", shell_exec("k5start -qUf /etc/service.crcweb -- remctl frankoz1 mdr query 'su_support is SA-CRC; su_sysadmin0'")));
foreach ($mdr as $s) {
  $s = explode(': ', $s);
  $hs[$s[0]] = $s[1];
}
unset ($mdr);

echo "<select id='user' onclick='clearSysadmin();'>\n";
echo "<option default=true value=''></option>\n";
foreach (array_unique($hs) as $s) {
  echo "<option value=\"$s\">$s</option>\n";
}
echo "</select>\n";

?>
