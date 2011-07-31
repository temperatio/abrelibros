<?php

//ini_set('display_errors', 1);
session_start();

  require_once("includes/twitteroauth/twitteroauth.php");  
  require_once("includes/config.php");
  require_once("includes/htmlhelper.php");
    
  
  
  //Si tenemos sesión, estamos registrados
  //y vamos a la página principal
  //Lo mismo habría que hacer algún tipo más de comprobación
  if(!empty($_SESSION['username'])){  
      header('Location: main.php');  
  }  
  
  
  
  //Llamamos a twitter para que nos devuelva los tokens que necesitamos
  $twitteroauth = new TwitterOAuth($consumer_key, $consumer_secret); 
  $request_token = $twitteroauth->getRequestToken($oauth_callback);
  
  $_SESSION['oauth_token'] = $request_token['oauth_token'];  
  $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret']; 
  
  
  
  //Mostramos el enlace de login / autorización
  $url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']); 
  
  starthtml();
  cabecera(false);
  
  echo <<<FIN
  <h2>¿No sabes dónde encontrar el libro que buscas?</h2>
  <p class="bigger">Aprovéchate de las Bibliotecas Públicas de la Comunidad de Madrid: 
  identifícate, crea una lista con los libros que quieres leer y abreLibros te dirá cuándo están disponibles
  en tu biblioteca preferida.</p>
  <div class="space"></div>
  <div class="flcenter">
  <a href="$url">
  <img width="371" height="46" alt="iniciar sesión con twitter" src="img/btntwitter.png">
  </a>
  </div>
  </div>
  <div class='articleBody clear'>
  <div class='articleLft'>
    <h3>Elige una biblioteca</h3>
    <div class="figure">
      <img width="263" alt="Seleciona una biblioteca" src="img/paso1.png">
    </div>
    <p>Elige una Biblioteca Pública de la Comunidad de Madrid de la lista. No te preocupes, puedes elegir otra en cualquier momento, sin perder tu lista de libros.</p>
  </div>
  <div class='articleLft'>
    <h3>Busca los libros</h3>  
    <div class="figure">
      <img width="263" alt="Busca los libros" src="img/paso2.png">
    </div>
    <p>Busca los libros por autor, editorial, título, ISBN, ... AbreLibros te devolverá la lista de libros que encuentre, indicando en qué zona de la biblioteca están, si están disponibles o no y la fecha prevista de devolución en caso de que los hayan prestados.</p>
  </div>
  <div class='articleLft last'>
    <h3>Añádelos a tu lista o ve a por ellos</h3>
    <div class="figure">
      <img width="263" alt="Añádelos a tu lista o ve a por ellos" src="img/paso3.png">
    </div>
    <p>Para añadir los libros a tu lista, marca el checkbox que aparece a la derecha de los resultados. AbreLibros se conectará cada hora al servidor de las bibliotecas y te dirá si hay novedades.</p>
  </div>
FIN;

  endhtml();