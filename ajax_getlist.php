<?php
//ini_set('display_errors', 1);

  session_start();

  require_once("includes/twitteroauth/twitteroauth.php");   
  require_once("includes/htmlhelper.php");
  require_once("includes/bibliohelper.php");

  $contents = "No se encontraron resultados para '{$_REQUEST['searchtext']}'";
  
  if($_REQUEST['searchtext']!="") {

    header("Cache-Control: no-cache");  
    
    $_SESSION['tokens'] =  getTokens();
    $titulos = getList(utf8_decode($_REQUEST['searchtext']));
      
    if (count($titulos)>0) {
      
      $contents = "<table id='resultados' width='99%' cellspacing='0' cellpadding='0' border='0' summary='Listado de libros'><thead><tr><th width='50%' colspan='2'>{$_REQUEST['searchtext']}</th><th width='15%'>disponibilidad</th><th width='15%'>localización</th><th width='15%'>signatura</th><th width='5%'></th></tr></thead><tbody>";
      
      $contador = 0;
      foreach($titulos as $key=>$value){
        $class= (++$contador % 2==1) ? "class='tr_odd'":"";
        $contents.= "<tr $class><td><img src='img/progress.gif'/></td><td>$value</td><td></td><td></td><td></td><td class='ult'></td></tr>";
      }
      
      $contents.= "</tbody></table>";
      
    } 
      
    echo $contents;
      
  }