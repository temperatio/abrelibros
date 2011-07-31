<?php
//ini_set('display_errors', 1);

  session_start();

  require_once("includes/twitteroauth/twitteroauth.php");   
  require_once("includes/htmlhelper.php");
  require_once("includes/bibliohelper.php");
  require_once("includes/dbhelper.php");
  
  
  
  //Si tenemos sesión, estamos registrados
  //y vamos a la página principal
  //Lo mismo habría que hacer algún tipo más de comprobación
  if(empty($_SESSION['username'])){  
      header('Location: logout.php');  
  }  
  
  if(empty($_SESSION['bibliokey'])){  
      header('Location: bibliochoose.php');  
  }  
  

  //Nos conectamos a la BDD
  $db = connectDatabase();

  $result = mysql_query("DELETE FROM libro 
                         WHERE usu_id = '{$_SESSION['usu_id']}' 
                           AND lib_status = 0", $db);  
  
  
  $result = mysql_query("SELECT *, UNIX_TIMESTAMP(lib_ultimacomprobacion) as lib_timestamp
                          FROM libro 
                         WHERE usu_id = '{$_SESSION['usu_id']}' 
                           AND lib_status = 1", $db);  

  $contador = 0;
  $minDate = "ZZZZ";
  $table = "<table id='resultados' width='99%' cellspacing='0' cellpadding='0' border='0' summary='Listado de libros'><thead><tr><th width='50%' colspan='2'>título</th><th width='15%'>disponibilidad</th><th width='15%'>localización</th><th width='15%'>signatura</th><th width='5%'></th></tr></thead><tbody>";
  while ($row = mysql_fetch_assoc( $result )) {
    if ($row["lib_timestamp"] < $minDate) {
      $minDate = $row["lib_timestamp"];
    }
    $class= (++$contador % 2==1) ? "class='tr_odd'":"";
    $table.= "<tr id='row_{$row["lib_isbn"]}' $class><td><img src='img/bot_{$row["lib_tipo"]}.png'/></td><td>{$row["lib_titulo"]}</td><td class='bot_{$row["lib_tipo"]}'>{$row["lib_disponibilidad"]}</td><td>{$row["lib_localizacion"]}</td><td>{$row["lib_signatura"]}</td><td class='ult'><a href='removebook.php?isbn={$row["lib_isbn"]}'><img src='img/delete.png'/></a></td></tr>";
  }
  
  $table.= "</tbody></table>";
  $minutos = (int) ((time() - $minDate) / 60);
  $acomprobar = false;
  if ($contador == 0) {
    $_SESSION['disponibilidad'] = "";
  } else if ($minutos > 60 || $_SESSION['forceupdate'] == 1) {
    $acomprobar = true;
    $_SESSION['forceupdate'] = 0;
  } else {
    $_SESSION['disponibilidad'] = "Próxima comprobación en " . (60-$minutos) . " minutos";
  }
  
  if ($acomprobar) {
    $_SESSION['tokens'] =  getTokens();
    starthtml(2);
  } else {
    starthtml();
  }
  cabecera(true);  
  
  echo <<<FIN
  <h2>Busca los libros que te interesan</h2>
  <form action="bibliosearch.php">
    <input type="text" placeholder="Busca el libro por autor, título, editorial, isbn..." name="searchtext"/>
    <input class="button blue" type="submit" value="buscar"/>
  </form>
  <div class="space"><br clear="all"></div>
FIN;
  
  
  if ($contador > 0) {
    echo "<div id=\"searchlist\">$table</div>";
  } 
  
  
  endhtml();    