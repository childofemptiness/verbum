<?php
# Database link credentials
define ("DBNAME", "verbum_db");
define ("DBUSER", "root");
define ("DBPASS", "");
define ("DBHOST", "");

# PATH to app and app name
define ("PATH", "");
define ("WEB_TITLE", "Verbum");

# PATH to media files and site root constants
define ("SITE_ROOT", "/" . PATH);
define ("MEDIA", SITE_ROOT . "/" . "public");
define ("HTML", "public" . DS . "html");

# Default states
define ("DEFAULT_CONTROLLER", "Index_Controller");
define ("DEFAULT_METHOD", "home");
define ("NOT_FOUND", "not_found");
define("AUTHORIZE", "loginpage");

