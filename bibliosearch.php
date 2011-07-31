<?php
//ini_set('display_errors', 1);

  session_start();

  require_once("includes/twitteroauth/twitteroauth.php");   
  require_once("includes/htmlhelper.php");
  require_once("includes/bibliohelper.php");
  



  
  
  //Si tenemos sesión, estamos registrados
  //y vamos a la página principal
  //Lo mismo habría que hacer algún tipo más de comprobación
  if(empty($_SESSION['username'])){  
    header('Location: logout.php');  
  }  
  
  if(empty($_SESSION['bibliokey'])){  
    header('Location: bibliochoose.php');  
  }   
 
  if(empty($_REQUEST['searchtext'])) {
    header('Location: main.php');
  }
  
  starthtml(1, rawurlencode($_REQUEST['searchtext']));
  cabecera(true);  
  
  echo <<<FIN
  <h2>Añade los libros a tu lista o ve a buscarlos</h2>
  <div id='searchlist'><img src='img/progress.gif'/>Cargando resultados...</div>
  <div class='line'></div>
  <div class="space"><br clear="all"/></div>
  <h3>¿Quieres buscar más libros?</h3>
  <form action="bibliosearch.php">
    <input type="text" placeholder="Busca el libro por autor, título, editorial, isbn..." name="searchtext"/>
    <input class="button blue" type="submit" value="buscar"/>
    <br/>
    <input class="button white flright" style="margin-right:7px;" type="submit" value="volver"/>
  </form>
  <div class="space"><br clear="all"></div>
FIN;
    
  
  endhtml();    