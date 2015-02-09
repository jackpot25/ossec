ossecweb-stanford
=================

Stanford tools for managing/customizing OSSEC.

To use the management jQuery/jTable front-end, you will need to give the user
apache is running as access to execute sudo commands.  The sudoers file is
included for reference.

The two commands referenced from the sudoers (ossec-add and ossec-del) are
located in ./bin for reference, but should be soft linked into /usr/local/bin.

The db directory (specified by $db_path in the config) needs to be owned by the apache process, or the db/ossec.db file needs to allow the apache process to read/write to it.

The Screen Shot.png is an overview of the UI.

Inactive agents are highlighted in red, and you can sort the table on name, IP,
or active status in the table.  Pagination is also available for those having
many agents.

Access control is managed via NetDB node roles.  Privileges in the
interface are set based on if you have netdb node user, admin or team
privileges. Additionally, there is a config.php file that defines an array of
sunetIDs with admin access.  Admin access means that you will bypass any node
role checks and be able to see and update everything.

Read-only access for:

    netdb user role only

Read-write access for:

    netdb admin or team roles
    admins listed in config.php

Visibility of servers, and all other read and write functions, are limited
based on your access level.

Access privileges are cached in a sqlite database (see info below on db schema
and other design info).  The cache is kept for a set number of minutes (see the
config.php) before it purges access for normal users (not admins).  This
permissions cache improves performance greatly.  The initial visit to the
management page will be slow since it is creating the cache in the sqlite db.
Subsequent page loads will use the sqlite cached permissions and be much more
responsive.  The limitation to this is that if you update a netdb node
privilege, you have to wait until the cache expires.  A potential TODO item for
this issue would be to add the ability to purge just your own permissions cache
from the db.


Some behavior is configurable in the config.php file.  Specifically:

  # Array of admins listed by sunetid - this assumes webauth is in use.
  $admins = [ 'foo', 'bar', 'baz' ];

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


