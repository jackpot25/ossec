<!DOCTYPE html>
<!--[if IE 7]> <html lang="en" class="ie7"> <![endif]-->
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<head>
<title>OSSEC Management</title>
<!-- TemplateParam name="theme" type="text" value="cardinal" -->

<!-- Meta -->
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="OSSEC Management" />
<meta name="author" content="Darren Patterson" />

<!-- CSS -->
<link rel="stylesheet" href="assets/cardinal/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="assets/cardinal/css/base.min.css?v=0.1" type="text/css" />
<link rel="stylesheet" href="assets/cardinal/css/custom.css?v=0.1" type="text/css"/>
<!--[if lt IE 9]>
  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if IE 8]>
  <link rel="stylesheet" type="text/css" href="assets/cardinal/css/ie/ie8.css" />
<![endif]-->
<!--[if IE 7]>
  <link rel="stylesheet" type="text/css" href="assets/cardinal/css/ie/ie7.css" />
<![endif]-->
<!-- JS and jQuery --> 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script> 
<script src="assets/cardinal/js/modernizr.custom.17475.js"></script> 
<script src="assets/cardinal/js/bootstrap.min.js"></script> 

<!--[if lt IE 9]>
    <script src="assets/cardinal/js/respond.js"></script>
<![endif]--> 

<!-- custom JS -->
<script src="assets/cardinal/js/base.js?v=1.0"></script>
<script src="assets/cardinal/js/custom.js"></script>
<script src="jquery-ui-1.11.1/jquery-ui.min.js" type="text/javascript"></script>
<link href="jquery-ui-1.11.1/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="jquery-ui-1.11.1/jquery-ui.theme.css" rel="stylesheet" type="text/css" />

<!-- Include one of jTable styles. -->
<link href="jtable.2.4.0/themes/metro/lightgray/jtable.min.css" rel="stylesheet" type="text/css" />
<!-- Include jTable script file. -->
<script src="jtable.2.4.0/jquery.jtable.min.js" type="text/javascript"></script>
<?php 
require './config.php';
require './Security.php';
if (($limit_add_to_admin && i_is_admin($_SERVER['REMOTE_USER'])) || 
  ! $limit_add_to_admin) {
?>
<script type="text/javascript">var myCreateAction="'./Create.php'";</script>
<?php
} 
else {
?>
<script type="text/javascript">var myCreateAction=undefined;</script>
<?php 
}
?>
<script src="assets/ossec-mgmt.js" type="text/javascript"></script>

</head>

<body class="home"> <!-- class="home" underlines the Home link in the top nav -->
    
    <!--=== Top ===-->
    <div id="top">
      <div class="container"> 
        <!--=== Skip links ===-->
        <div id="skip"> <a href="#content" onClick="$('#content').focus()">Skip to content</a> </div>
        <!-- /Skip links --> 
      </div>
    </div>
    <!--/top--> 
    
    <!--=== Header ===-->
    <div id="header" class="clearfix" role="banner">
      <div class="container">
        <div class="row">
          <div class="col-md-8"> 
            <!-- Logo -->
            <div id="logo" class="clearfix"> <a href="http://www.stanford.edu/"><img class="img-responsive" src="assets/cardinal/images/stanford-white@2x.png"  alt="Stanford University" /></a> </div>
            <!-- /logo -->
            <div id="signature">
              <div id="site-name">
                <a href="/">
                  <span id="site-name-1">OSSEC Management</span>
                  <span id="site-name-2">Optional line 2 - add two-line-signature to body class to display</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Menu -->
    <div id="mainmenu" class="clearfix" role="navigation">
      <div class="container">
        <div class="navbar navbar-default"> 
          
          <!-- main navigation -->
          <button type="button" class="navbar-toggle btn-navbar" data-toggle="collapse" data-target=".navbar-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="menu-text">Menu</span></button>
          <!-- /nav-collapse -->
          
            <div  role="navigation">
              <div id="primary-nav">
                <ul class="nav navbar-nav" aria-label="primary navigation">
                  <li id="nav-home"> <a href="./">Home</a></li>
                  <li > <a href="./webui">OSSEC Web UI</a></li>
                  <li > <a href="./dash">OSSEC Dashboard</a></li>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <!-- /nav-collapse --> 
        </div>
        <!-- /navbar --> 
        
      </div>
      <!-- /container --> 
    </div>
    <!-- /mainmenu --> 
    
    <!-- main content -->
    <div id="content" class="container" role="main" tabindex="0">
      <div class="row">
                <!-- Main column -->
        <div id="main-content" class="col-md-9 col-md-push-3" role="main">
            <!-- insert ossec html here -->
           <div class="filtering">
              <p>
                <form>
                  <table>
                    <tr>
                      <td>Perform action:</td>
                      <td><select id='myActions' onChange='actions();'>
                        <option default=true value=''></option>
                        <option value='expandsys'>Expand syscheck for selected</option>
                        <option value='expandroot'>Expand rootcheck for selected</option>
                        <option value='updatesys'>Update syscheck for selected</option>
                        <option value='updateroot'>Update rootcheck for selected</option>
                        <option value='updatesysall'>Update syscheck for ALL systems</option>
                        <option value='updaterootall'>Update rootcheck for ALL systems</option>
                      </select></td>
                    </tr>
                    <tr>
                      <td>Search host name: </td><td><input type="text" name="name" id="name" />&nbsp;&nbsp;</td>
                    </tr>
                    <!-- start filter by sysadmin -->
                    <?php require 'config.php'; if ($filter_by_sysadmin) { ?>
                    <tr>
                      <td>Return only my servers: &nbsp;&nbsp;</td><td><input type="checkbox" name="sysadmin" id="sysadmin" value="true" onclick="clearUser();"></td>
                    </tr>
                    <tr>
                      <td>... Or select sysadmin:</td>
                      <td><?php require './Users.php'; ?></td>
                    </tr>
                    <!-- end filter by sysadmin -->
                    <?php } ?>
                    </table>
                  <button type="submit" id="LoadRecordsButton">Find Server(s)</button>
                </form>
              </p>
          </div>
          <!-- 
            There is an issue with the footer overlapping on the jtable in
            only Safari/Chrome.  After looking into why, I settled on the easy
            fix of adding padding-bottom on those platforms.
          -->
          <?php 
            $safariorchrome = 
              strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') ? true : false;
            if ($safariorchrome) {
          ?>
          <div id="ossecContainer" style='padding-bottom: 335px;'></div>
          <?php 
            }
            else {
          ?>
          <div id="ossecContainer"></div>
          <?php
            }
          ?>
          <div>
            <p />
            <p>
            <div id="dialog-confirm"></div>
            </p>
          </div>
        </div>
        <div id="sidebar-second" class="col-md-3 col-md-pull-9">
          <div class="well">
          <?php require './leftbar.php'; ?>
          </div>
    </div>
    <!-- #content--> 
    
  </div>
  <!-- #su-content --> 
</div>
<!-- #su-wrap --> 

<!-- BEGIN footer -->
<div id="footer" class="clearfix footer" role="contentinfo">
  <!-- Fat footer start -->
  <div class="container">
    <div id="footer-content" class="row">
      <div class="col-xs-12 col-sm-6 col-sm-push-3">
        <?php require './footer.php'; ?>
     </div>
  </div>
  <!-- Fat footer end -->
  
  <!-- Global footer snippet start -->
  <div id="global-footer">
    <div class="container">
      <div class="row">
        <div id="bottom-logo" class="col-xs-6 col-sm-2"> <a href="http://www.stanford.edu"> <img src="assets/cardinal/images/footer-stanford-logo@2x.png" alt="Stanford University" width="105" height="49"/> </a> </div>
        <!-- #bottom-logo end -->
        <div id="bottom-text" class="col-xs-6 col-sm-10">
          <ul>
            <li class="home"><a href="http://www.stanford.edu">SU Home</a></li>
            <li class="maps alt"><a href="http://visit.stanford.edu/plan/maps.html" class="su-link" data-ua-action="visit.stanford.edu/plan/maps" data-ua-label="global-footer">Maps &amp; Directions</a></li>
            <li class="search-stanford"><a href="http://stanford.edu/search/">Search Stanford</a></li>
            <li class="terms alt"><a href="http://www.stanford.edu/site/terms.html">Terms of Use</a></li>
            <li class="emergency-info"><a href="http://emergency.stanford.edu/" class="su-link" data-ua-label="global-footer">Emergency Info</a></li>
          </ul>
        </div>
        <!-- .bottom-text end -->
        <div class="clear"></div>
        <p class="copyright vcard col-sm-10">&copy; <span class="fn org">Stanford University</span>.&nbsp; <span class="adr"> <span class="locality">Stanford</span>, <span class="region">California</span> <span class="postal-code">94305</span></span>. <span id="termsofuse"><a href="http://www.stanford.edu/group/security/dmca.html" class="su-link" data-ua-action="copyright-complaints" data-ua-label="global-footer">Copyright Complaints</a>&nbsp;&nbsp;&nbsp;<a href="https://adminguide.stanford.edu/chapter-1/subchapter-5/policy-1-5-4" class="su-link" data-ua-action="trademark-notice" data-ua-label="global-footer">Trademark Notice</a></span></p>
      </div>
      <!-- .row end --> 
    </div>
    <!-- .container end --> 
  <!-- Global footer snippet end --> 
  
<!-- END footer --> 

</body>
</html>

