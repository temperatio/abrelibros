<?php
//ini_set('display_errors', 1);

  session_start();

  require_once("includes/twitteroauth/twitteroauth.php");   
  require_once("includes/htmlhelper.php");
  require_once("includes/bibliohelper.php");
  
  
  if($_REQUEST['isbn']!="") {
    header("Cache-Control: no-cache");     
    echo json_encode(getInfoByISBN($_REQUEST['isbn']));
  }
