<?php

# Check if required extensions are loaded.
if (!extension_loaded('remctl')) {
  fatal_error_popup("Failed to load remctl extension\n");
}
if (!extension_loaded('sqlite3')) {
  fatal_error_popup("Failed to load sqlite extension\n");
}


# Return: sqlite3 dbhandle
# Initializes db, calls purgedb if tables exist. 
# If sunetid not in user table, loop through all ossec agents to add entries.
function dbinit ($user) {
  require 'config.php';
  $dbhandle = new SQLite3($db_path);
  if (!$dbhandle) {
    fatal_error_popup('sqlite error: '.$dbquery->lastErrorMsg());
    return;
  }
  $stm = "CREATE TABLE IF NOT EXISTS server(id INTEGER PRIMARY KEY," .
    " sunetid TEXT NOT NULL, server TEXT NOT NULL, r_user INTEGER,".
    " r_admin INTEGER, r_team INTEGER )";

  $ok = $dbhandle->exec($stm);
  if (!$ok) {
    fatal_error_popup("sqlite cannot execute query. ".
      $dbhandle->lastErrorMsg());
    return;
  }

  $stm = "CREATE TABLE IF NOT EXISTS user(id INTEGER PRIMARY KEY," .
    " date INTEGER, sunetid STRING )";
  
  $ok = $dbhandle->exec($stm);
  if (!$ok) {
    fatal_error_popup("Cannot execute query. ".
      $dbhandle->lastErrorMsg());
    return;
  }
 
  # purge the user entries;
  purgedb($dbhandle, $cache_timeout);

  # check if sunetid is in user table
  if (! i_is_cached($dbhandle, $user)) {
    # loop through and populate server table
    $systems = array_filter(explode("\n", shell_exec("sudo /var/ossec/bin/syscheck_control -ls")));
    # kept here for reference for fields from syscheck_control csv output
    #$headers = array ("SystemId", "Name", "IP", "Active");
    $mysys = array();
    foreach ($systems as $row) {
      $row = rtrim($row, ",");
      $row = str_getcsv($row);
      # lookup netdb node roles for $row[1] for $user
      $roles = i_get_roles($dbhandle, $user, split(" ", $row[1])[0]);

      i_add($dbhandle, $user, $row[1], $roles[0], $roles[1], $roles[2]);
    }

    if ($debug) {
      echo "DEBUG: In dbinit -- dumping servers\n";
      var_dump($dbhandle->query('SELECT * FROM server')->fetchArray());
    }
    # after loop, add sunetid to user table
    i_set_cached($dbhandle, $user);
  }
  return $dbhandle;
}

# date comparison using unix epoch time in seconds
# SELECT strftime('%s','now'); 

# $cache_timeout is var for timeout in min

function i_is_cached ($dbhandle, $user) {
  require 'config.php';
  # shortcut to avoid caching for admins
  if (i_is_admin($user)) {
    if ($debug) echo "DEBUG: In i_is_cached -- returning true for admin\n";
    return true;
  }
  $stm = 'SELECT id FROM user WHERE sunetid=\''.$user.'\'';
  if ($dbhandle->querySingle($stm)) {
    if ($debug) echo "DEBUG: In i_is_cached -- returning true\n";
    return true;
  } 
  else {
    if ($debug) echo "DEBUG: In i_is_cached -- returning false\n";
    return false;
  }
}

function i_set_cached ($dbhandle, $user) {
  require 'config.php';
  $stm = 'INSERT OR REPLACE INTO user (id, date, sunetid) values ('.
    '(SELECT id FROM user WHERE sunetid=\''.$user.'\'),'.
    '(SELECT strftime(\'%s\', \'now\')), \''.$user.'\')';
  $ok = $dbhandle->exec($stm);
  if (!$ok) {
    fatal_error_popup('sqlite failed to set '.$user.' to cached in db.');
    return;
  }

  if ($debug) {
    echo "DEBUG: In i_set_cached -- dumping users\n";
    var_dump($dbhandle->query('SELECT * FROM user')->fetchArray());
  }
}

function purgedb ($dbhandle, $min) {
  require 'config.php';

  # Delete all server rows w/ sunetid != current user table entries. EG orphan.
  $delstm = 'DELETE FROM server WHERE server.sunetid NOT IN (SELECT '.
    'user.sunetid FROM user)';
  $ok = $dbhandle->exec($delstm);
  if (!$ok) {
    fatal_error_popup('failed to delete server rows for missing users.');
  }

  $stm = 'SELECT sunetid FROM user WHERE date < (SELECT strftime(\'%s\', '.
    '\'now\', \'-'.$min.' minutes\'))';
  $results = $dbhandle->query($stm)->fetchArray();

  if ($results == false) {
    if ($debug) { 
      echo "DEBUG: In purgedb -- results from date comparison was false\n";
      #echo "DEBUG: In purgedb -- query was $stm\n";
    }
    return;
  }

  foreach ($results as $row) {
    $delstm = 'DELETE FROM server WHERE sunetid=\''.$row.'\'';
    $ok = $dbhandle->exec($delstm);
    if (!$ok) {
      fatal_error_popup('failed to delete server row for '.$row);
    }
    $delstm = 'DELETE FROM user WHERE sunetid=\''.$row.'\'';
    $ok = $dbhandle->exec($delstm);
    if (!$ok) {
      fatal_error_popup('failed to delete user row for '.$row);
    }
  }

  if ($debug) {
    echo "DEBUG: In purgedb -- dumping servers\n\n";
    var_dump($dbhandle->query('SELECT * FROM server')->fetchArray());
    echo "DEBUG: In purgedb -- dumping users\n\n";
    var_dump($dbhandle->query('SELECT * FROM user')->fetchArray());
  }
}

function i_is_admin ($user) {
  require 'config.php';
  return in_array($user, $admins);
}

# Returns an array of access for user, admin and team roles from server table.
# If the server isn't in the cache, add it and return the array of access.
function i_check ($dbhandle, $user, $server) {
  if (i_is_admin($user)) {
    return array(1,1,1);
  }
  $stm = 'SELECT * FROM server WHERE server=\''.$server.'\' AND sunetid=\''.
    $user.'\'';
  $result = $dbhandle->query($stm)->fetchArray();
  if ($result == false) {
    $perms = i_get_roles($dbhandle, $user, $server);
    i_add($dbhandle, $user, $server, $perms[0], $perms[1], $perms[2]);
    return array( $perms[0], $perms[1], $perms[2] );
  }
  else {
    return array( $result['r_user'], $result['r_admin'], $result['r_team'] );
  }
}

# Add a server table entry for the provided server and user w/ perms.
function i_add ($dbhandle, $user, $server, $r_u, $r_a, $r_t) {
  $stm = 'INSERT INTO server VALUES(NULL,\''.$user.'\', \''.$server.'\', \''.
    $r_u.'\', \''.$r_a.'\', \''.$r_t.'\')';
  $ok = $dbhandle->exec($stm);
  if (!$ok) {
    fatal_error_popup('sqlite failed to add server to db');
    return;
  }
}

# Retrieve and return the roles from netdb.  This is expensive! Only to be 
# called if the data is not in cache.
function i_get_roles ($dbhandle, $user, $server) {
  require 'config.php';
  # NOTE: this requires a ticket cache maintained by k5start that is
  # readable by the apache process.  The principal from the keytab must
  # have been given permissions in netdb to run node-roles.
  $command = array ('netdb', 'node-roles', $user, $server);
  $perms = array();
  $r = remctl_new();
  remctl_set_ccache($r, $k5_ticket_cache);
  $result = remctl('netdb-node-roles-rc.stanford.edu', 0, '', $command);
  if ($result->stderr != '' || $result->error) {
    fatal_error_popup("Remctl node roles returned error: ".$result->stderr);
    return;
  } 
  else {
    $perms = split ("\n", $result->stdout);
  }

  $r_u = $r_a = $r_t = 0;
  if (count($perms) >= 0) {
    if (in_array('user', $perms)) {
      $r_u = 1;
    }
    if (in_array('admin', $perms)) {
      $r_a = 1;
    }
    if (in_array('team', $perms)) {
      $r_t = 1;
    }
  }
  #if ($debug) echo "DEBUG: In i_get_roles -- perms for $server are $r_u, $r_a, $r_t\n";
  return array($r_u, $r_a, $r_t);
}

# Returns true of sunetid has user, admin or team privs from netdb.
function has_r_access ($dbhandle, $user, $server) {
  $results = i_check($dbhandle, $user, $server);
  return in_array('1', $results);
}

# Returns true only if sunetid has admin or team privs from netdb.
function has_w_access ($dbhandle, $user, $server) {
  $results = i_check($dbhandle, $user, $server);
  return ($results[1] == '1' or $results[2] == '1');
}

# Returns an error to jquery/jtable
function fatal_error_popup ($msg) {
  $json['Result'] = 'Error';
  $json['Message'] = $msg;
  echo json_encode($json);
}

##
# Test for verifying access:
##
#$db = dbinit('darrenp1');
#if (has_w_access($db, 'darrenp1', 'crc-web2.stanford.edu')) {
#  echo "true\n";
#}

?>
