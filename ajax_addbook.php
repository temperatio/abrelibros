<?php
//ini_set('display_errors', 1);

  require_once("includes/dbhelper.php");

  session_start();
    
  if($_REQUEST['isbn']!="") {

    header("Cache-Control: no-cache");  
    
    $db = connectDatabase(); 
    
    $query = mysql_query("UPDATE libro
                                 SET lib_status = 1,
                                     lib_titulo = '{$_REQUEST['titulo']}' 
         					   WHERE usu_id = {$_SESSION['usu_id']}
                                 AND lib_status = 0
                                 AND lib_isbn = '{$_REQUEST['isbn']}'", $db);      
  }