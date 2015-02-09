          <h2>About OSSEC Management</h2>
            <p>The following management interface is built on jtable/jquery.</p>
            <p>There is a cache for access control that is refreshed every <?php echo $cache_timeout; ?> minutes.  It may take some time (60 seconds or more) to generate the db cache on the server during your first page load.</p>
            <p>Use the "Add new record" button (top right in the table) to add a new OSSEC client (add using the FQDN only). Retrieve the agent key by clicking in the "Key" column for the new server. To delete a OSSEC client, click the trash icon in the row listing the server.</p>
            <p>The two left columns for "Sys" and "Root" expand down when you click the icon to show you syscheck and rootcheck reports respectively.  Once these are expanded, you can click the X in the top right of the drop-down table to close the view, or click the trash icon to clear the syscheck or rootcheck report.  Be careful to click the correct trash icon in the report sub-table row instead of the system list.</p>
            <p>Access control is managed via webauth for workgroup crc:crcsg</p>
          </div>
          <div class="well">
          <h2>Related Tools</h2>
          <p>Check out our <a href="./webui">OSSEC Web UI</a></p>
          <p>For a few graphs of our environment, see the <a href="./dash">Dashboard</a></p>
          <p>For those who want to use the command line, there are remctl tools available using your root principal.  Specific commands are:
          <ul>
          <li>remctl crclogs syscheck ...</li>
          <li>remctl crclogs rootcheck ...</li>
          <li>remctl crclogs list_agents ...</li>
          <li>remctl crclogs agent_control ...</li>
          <li>remctl crclogs ossecadd ...</li>
          <li>remctl crclogs ossecdel ...</li>
          </ul>

