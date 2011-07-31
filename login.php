<?php
//ini_set('display_errors', 1);

  session_start();
  
  require_once("includes/twitteroauth/twitteroauth.php");  
  require_once("includes/config.php");
  require_once("includes/bibliohelper.php");
  require_once("includes/dbhelper.php");
    
  //Miramos que estén todos los parámetros
  if(!empty($_GET['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])){  

    //Hacemos que la conexión sea permanente
    $twitteroauth = new TwitterOAuth($consumer_key, $consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
    $user_info = $twitteroauth->get('account/verify_credentials');
   
    if(isset($user_info->error)){  

      
      //Se ha producido un error
      header('Location: logout.php'); 
    
    } else { 
      
      //Nos conectamos a la BDD
      $db = connectDatabase(); 
      
      //Miramos si el usuario existe
      //Ojo, que no estamos teniendo en cuenta ataques por SQL Injection
      $query = mysql_query("SELECT * 
                              FROM usuario 
                             WHERE usu_oauth_provider = 'twitter' 
                               AND usu_oauth_uid = ". $user_info->id, $db);  
      $result = mysql_fetch_array($query);  
      
      if(!$result){  
      
        //Si no existe el usuario, lo insertamos y recuperamos nuestro ID
        //No usamos el mysql_insert_id por si acaso
        $query = mysql_query("INSERT INTO usuario (usu_oauth_provider, usu_oauth_uid, usu_oauth_nick, usu_oauth_token, usu_oauth_secret,usu_oauth_timestamp) 
                                           VALUES ('twitter', {$user_info->id}, '{$user_info->screen_name}', '{$access_token['oauth_token']}', '{$access_token['oauth_token_secret']}',now())", $db);  
        $query = mysql_query("SELECT * 
         					    FROM usuario 
           					   WHERE usu_oauth_provider = 'twitter'
           						 AND usu_oauth_uid = " . $user_info->id, $db);  
        $result = mysql_fetch_array($query);  

      } else {  

        //El usuario existe. Hacemos un update de los tokens y de la fecha de login.
        //Hemos leído por ahí que los tokens no cambian, así que no sabemos porqué se hace eso.
        $query = mysql_query("UPDATE usuario 
         						 SET usu_oauth_token = '{$access_token['oauth_token']}', 
         						     usu_oauth_secret = '{$access_token['oauth_token_secret']}',
         						     usu_oauth_timestamp = now(),
         						     usu_oauth_nick = '{$user_info->screen_name}'
         					   WHERE usu_id = {$result['usu_id']}", $db);  
        }  
      
       $_SESSION['usu_id'] = $result['usu_id']; 
       $_SESSION['bibliokey'] = $result['usu_bibliokey'];
       $_SESSION['username'] = $user_info->screen_name; 
       $_SESSION['avatar'] = $user_info->profile_image_url;
       $_SESSION['oauth_uid'] = $user_info->id; 
       $_SESSION['oauth_provider'] = 'twitter'; 
       $_SESSION['oauth_token'] = $access_token['oauth_token']; 
       $_SESSION['oauth_token_secret'] = $access_token['oauth_token_secret']; 
       $_SESSION['forceupdate'] = 0;
      
       header('Location: main.php');  
    
    }
  
  } else {  
  
    header('Location: logout.php');  
  
  }  