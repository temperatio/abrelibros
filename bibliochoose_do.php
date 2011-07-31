<?php
//ini_set('display_errors', 1);

  session_start();

  require_once("includes/htmlhelper.php");
  require_once("includes/bibliohelper.php");
  require_once("includes/dbhelper.php");
  


  
  
  
  //Si tenemos sesión, estamos registrados
  //y vamos a la página principal
  //Lo mismo habría que hacer algún tipo más de comprobación
  
  if(empty($_SESSION['username'])){  
      header('Location: logout.php');  
  }  
    
  
  if (getBiblioName($_REQUEST["bibliokey"])==""){
      header('Location: bibliochoose.php');  
  }
  
  //echo "-".$_SESSION['bibliokey'] . "-". $_REQUEST["bibliokey"] . "-";
  if ($_SESSION['bibliokey'] == $_REQUEST["bibliokey"]){
    //echo 1;
    header('Location: main.php');
    exit;
  }
  
  $db = connectDatabase(); 
  
  $query = mysql_query("SELECT * 
                          FROM usuario 
                         WHERE usu_oauth_provider = '{$_SESSION['oauth_provider']}' 
                           AND usu_oauth_uid = {$_SESSION['oauth_uid']} 
                           AND usu_id = {$_SESSION['usu_id']}", $db); 
   
  $result = mysql_fetch_array($query);  
  
  if(!$result){  
    
    header('Location: logout.php');
  
  } else {
    
    $query = mysql_query("UPDATE usuario 
     						 SET usu_bibliokey = '{$_REQUEST["bibliokey"]}'
     					   WHERE usu_id = {$_SESSION['usu_id']}", $db);  
    
    $_SESSION['bibliokey'] = $_REQUEST["bibliokey"];
    $_SESSION['forceupdate'] = 1;
    
    header('Location: main.php');
    
    
  }
  