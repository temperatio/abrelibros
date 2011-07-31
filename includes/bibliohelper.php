<?php

  require_once("includes/dbhelper.php");
  

  $bibliotecas = array(
    //"/BPCM"=>"Bibliotecas Públicas de la Comunidad de Madrid",
    "BP02"=>"Acuña",
    "BP03"=>"Canillejas",
    "BP04"=>"Carabanchel",
    "BP05"=>"Central",
    "BP06"=>"Centro",
    "BP08"=>"Fuencarral",
    "BP09"=>"Hortaleza",
    "BP10"=>"Latina",
    "BP11"=>"Manuel Alvar",
    "BP13"=>"Moratalaz",
    "BP14"=>"Paco Rabal",
    "BP15"=>"Pan Bendito",
    "BP16"=>"Retiro",
    "BP17"=>"Ruiz Egea",
    "BP18"=>"Usera",
    "BP19"=>"Vallecas",
    "BP20"=>"Villa de Vallecas",
    "BP21"=>"Villaverde",
    "BP25"=>"Telebiblioteca",
  );
  
  $baseURL = "http://www.madrid.org/biblio_catalogos/cgi-bin/abnetopac/";

  
  
  function getBiblioName($bibliokey){
    
    global $bibliotecas;
       
    return ($bibliotecas[$bibliokey]);
  
  }
  
  
  
  function getBiblioList($bibliokey){
    
    global $bibliotecas;
    
    $columnas = array(7,7,5);
    $biblioList = "";
    $class = "";
    $bibliokeys = array_keys($bibliotecas);
    $counter = 0;
    
    foreach ($columnas as $contador) {

      $biblioList .= "<div class='articleLft ".($contador==5?" last":"")."'><ul>";
      
      for ($f=0; $f<$contador; $f++) {

        $class = ($bibliokeys[$counter]==$_SESSION["bibliokey"]) ? "biblioselected" : "";
        $biblioList .= "<li><a href='bibliochoose_do.php?bibliokey=".rawurlencode($bibliokeys[$counter])."' class='button white $class'>{$bibliotecas[$bibliokeys[$counter]]}</a></li>";
        $counter++;
      
      }
      $biblioList .= "</ul></div>";
      
    }
    
    echo $biblioList;
    
  }  
  
  
  
  function getTokens(){
    
    global $baseURL;

    $contents = file_get_contents($baseURL);
    
    $re1='.*?';	# Non-greedy match on filler
    $re2='(?:[a-z][a-z]*[0-9]+[a-z0-9]*)';	# Uninteresting: alphanum
    $re3='.*?';	# Non-greedy match on filler
    $re4='((?:[a-z][a-z]*[0-9]+[a-z0-9]*))';	# Alphanum 1
    $re5='.*?';	# Non-greedy match on filler
    $re6='((?:[a-z][a-z]*[0-9]+[a-z0-9]*))';	# Alphanum 2
  
    if ($c=preg_match_all ("/".$re1.$re2.$re3.$re4.$re5.$re6."/is", $contents, $matches)){
        $token1=$matches[1][0];
        $token2=$matches[2][0];
    } 
    
    return "$token1/$token2/";
  }
  
  
  
  function getList($searchstring){
    
    global $baseURL;
    
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $baseURL . $_SESSION['tokens']);
    curl_setopt($c, CURLOPT_POSTFIELDS,"ACC=131&xindex=&xindty=&subcat=".rawurlencode("/BPCM/" . $_SESSION["bibliokey"])."&xsqf01=$searchstring&xsnf12=0&xsqf12=&xsqf03=&xsqf04=&xsqf05=&xsqf06=&xsqf07=&xsqf08=&select01=01&select02=01&select03=02&xsnlis=100&xssort=4579592&xshist=");
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($c);
    
    //Sacamos los títulos de los libros
    $resultados = array();
    if (preg_match_all("/<a href=\"#\" onclick=\"AbnOpacDoc\((.*)\);\" .*>(.*)<\/a>/i", $contents, $matches)){
      $contador = 0;
      foreach ($matches[1] as $offset){
        $resultados[$offset] = utf8_encode($matches[2][$contador++]);
      }
    }
    
    return $resultados;
    
  }
  
  
  
  function getInfoByISBN($isbn){
    
    global $baseURL;
    
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $baseURL . $_SESSION['tokens']);
    curl_setopt($c, CURLOPT_POSTFIELDS,"ACC=131&FORM=1&xsqf01=".rawurlencode($isbn)."&subcat=".rawurlencode("/BPCM/" . $_SESSION["bibliokey"]));
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($c);
        
    return getBookData($contents, $isbn);
  
  }
  

    
  function getInfoByOffset($offset){
    
    global $baseURL;
    
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $baseURL . $_SESSION['tokens']);
    curl_setopt($c, CURLOPT_POSTFIELDS,"ACC=165&DOC=$offset&xshist=1");
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($c);
    
    return getBookData($contents);

  }
  
  
  
  function getBookData ($contents, $fromisbn = "") {
    
    //echo ($contents);
    
    //Sacamos el ISBN
    preg_match("/ISBN:<\/td><td class=\"noeditable\"><div>(<span style=\"color:#f00\">)?([0-9-]*)/", $contents, $matches);
    if (array_key_exists(2, $matches)) {
      $isbn = $matches[2];
    } else {
      $isbn="";
    }
    
    $status = 0;
    
    //Sacamos los ejemplares
    $ejemplares = array();
    $contador = 0; 
    $best = 0;
    if (preg_match_all("/<td class=\"bordercontenido\">&nbsp;(.*)(&nbsp;)+<\/td>/iU", $contents, $matches)){
      for ($f=0; $f<count($matches[1]); $f++) {
        switch($f%5) {
          case 0: $ejemplares[$contador]["localizacion"] = utf8_encode($matches[1][$f]);
                  break;
          case 2: $ejemplares[$contador]["signatura"] = utf8_encode($matches[1][$f]);
                  break;
          case 4: $ejemplares[$contador]["disponibilidad"] = utf8_encode($matches[1][$f]);
                  if ($ejemplares[$contador]["disponibilidad"]=="Disponible") {
                    $ejemplares[$contador]["tipo"] = "disponible";
                    $best = $contador;
                    break 2;
                  } else if (strpos($ejemplares[$contador]["disponibilidad"], "Disponible ") === 0){ 
                    if (isBetterDate($ejemplares[$contador]["disponibilidad"],$ejemplares[$best]["disponibilidad"])) {
                      $best = $contador;
                    }
                    $ejemplares[$contador]["tipo"] = "futuro";
                  } else {
                    $ejemplares[$contador]["tipo"] = "no";
                  }
                  $contador++;                
        }
      }
    }
    
    $db = connectDatabase(); 
    if ($isbn!="") {
      
      $query = mysql_query("SELECT lib_status
                              FROM libro 
                             WHERE usu_id = {$_SESSION['usu_id']} AND
                                   lib_isbn = '$isbn'",$db);
      
      if ($result = mysql_fetch_array($query)) {

        $status = $result["lib_status"];
                
        $query = mysql_query("UPDATE libro 
                                 SET lib_ultimacomprobacion = now(), 
                                     lib_disponibilidad = '{$ejemplares[$best]["disponibilidad"]}',
                                     lib_localizacion = '{$ejemplares[$best]["localizacion"]}',
                                     lib_signatura = '{$ejemplares[$best]["signatura"]}',
                                     lib_tipo = '{$ejemplares[$best]["tipo"]}'
                               WHERE usu_id = {$_SESSION['usu_id']} AND
                                     lib_isbn = '$isbn'",$db);        
      
      } else {
        
        $query = mysql_query("INSERT INTO libro (usu_id,                lib_isbn, lib_titulo, lib_disponibilidad,                       lib_localizacion,                       lib_signatura,                       lib_ultimacomprobacion, lib_status, lib_tipo) 
                                         VALUES ({$_SESSION['usu_id']}, '$isbn',  'titulo',         '{$ejemplares[$best]["disponibilidad"]}', '{$ejemplares[$best]["localizacion"]}', '{$ejemplares[$best]["signatura"]}', now(),                  0,          '{$ejemplares[$best]["tipo"]}')",$db);  
      }
           
      
    }    
    
    if ($isbn=="" && $status==0 && $fromisbn!=""){
      $best = 1;
      $ejemplares[$best]["disponibilidad"] = "No disponible en esta biblioteca";
      $ejemplares[$best]["localizacion"] = "";
      $ejemplares[$best]["signatura"] = "";
      $ejemplares[$best]["tipo"] = "aquino";
      $query = mysql_query("UPDATE libro 
                               SET lib_ultimacomprobacion = now(), 
                                   lib_disponibilidad = '{$ejemplares[$best]["disponibilidad"]}',
                                   lib_localizacion = '{$ejemplares[$best]["localizacion"]}',
                                   lib_signatura = '{$ejemplares[$best]["signatura"]}',
                                   lib_tipo = '{$ejemplares[$best]["tipo"]}'
                             WHERE usu_id = {$_SESSION['usu_id']} AND
                                   lib_isbn = '$fromisbn'",$db);        
      
    }
    
    $ejemplares[$best]["isbn"] = $fromisbn!=""?$fromisbn:$isbn;
    $ejemplares[$best]["status"] = $status;
    return $ejemplares[$best];
  } 
  
  
  
  function isBetterDate($newDate, $oldDate){
    
    if ($newDate==$oldDate) return false;
    if (strlen($oldDate) < 10) return true; 
    if (strpos($oldDate, "Disponible ") !== 0) return true;
    //$newDateMach = dateFormat(substr($newDate,-10));
    //$oldDateMach = dateFormat(substr($oldDate,-10));
    
    return dateFormat(substr($newDate,-10)) < dateFormat(substr($oldDate,-10));
    
  }
  
  
  
  function dateFormat($date){
    return substr($date, -4) . substr($date, 3, 2) . substr($date, 0, 2);
  }