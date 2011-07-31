<?php
//ini_set('display_errors', 1);

  session_start();

  require_once("includes/htmlhelper.php");
  require_once("includes/bibliohelper.php");
  



  
  
  //Si tenemos sesión, estamos registrados
  //y vamos a la página principal
  //Lo mismo habría que hacer algún tipo más de comprobación
  if(empty($_SESSION['username'])){  
      header('Location: logout.php');  
  }  
  
  
  starthtml();
  cabecera(true);

  echo "<h2>Selecciona tu biblioteca</h2>";
  
  getBiblioList($_SESSION['bibliokey']);
  
  echo <<<FIN
  <div class='line'></div>
  <div class="flright"><a class="button gray" href="main.php">cancelar</a></div>
FIN;
  
  endhtml();    