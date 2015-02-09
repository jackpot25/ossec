<?php

# Array of admins listed by sunetid - this assumes webauth is in use.
$admins = [  '', '' ];

# int - Timeout in min to flush the user and server cache.
$cache_timeout = 240;

# bool - Enable debugging - only useful for cli debugging.
$debug = false;

# Path to the ticket cache. This should be maintained by a k5start job.
$k5_ticket_cache='/var/run/web/crcweb.k5.tgt';

# Path to the slite db.
$db_path = 'db/ossec.db';

# bool - Enable/disable filtering by sysadmin
$filter_by_sysadmin = true;

# bool - Limit adding to admins only?
$limit_add_to_admin = true;

?>
