<?php

function starthtml($script = -1, $param = ""){
  
  $javascript = "";
  if ($script==1) {
    $javascript = <<<FIN
    $().ready(function() {

      $.ajax({type:'POST', async:false, url:'ajax_getlist.php', timeout: 50000, cache:false, data: 'searchtext=$param',
        success: function(d,s){
          $('#searchlist').html(d);
        },
        error: function(o,s,e){
          alert(s);
        }
      });
      
      $('#resultados tbody tr').each(function(i){
        $.ajax({type:'POST', url:'ajax_getinfobyoffset.php', timeout: 50000, cache:false, data: 'offset='+(i+1), context:this, dataType: 'json',
          success: function(d,s){
            $(this).find('td:eq(2)').html(d.disponibilidad)
            $(this).find('td:eq(3)').html(d.localizacion);
            $(this).find('td:eq(4)').html(d.signatura);            
            if (d.isbn=='' || d.isbn==null){
              $(this).find('td:eq(0)').html('<img src="img/bot_disabled.png"/>');
              $(this).addClass('bot_disabled');
              $(this).find('td:eq(5)').html('');
            } else {
              $(this).find('td:eq(2)').removeClass().addClass('bot_'+d.tipo);
              $(this).find('td:eq(1)').attr('id','td_' + d.isbn);
              if (d.status==1) {
                $(this).find('td:eq(5)').html("<a id='isbn_"+d.isbn+"' onclick='addRemoveISBN(\""+d.isbn+"\")' class='selectedisbn'><img src='img/added.png'/></a>");
              } else {
                $(this).find('td:eq(5)').html("<a id='isbn_"+d.isbn+"' onclick='addRemoveISBN(\""+d.isbn+"\")'><img src='img/add.png'/></a>");              
              }
              $(this).find('td:eq(0)').html('<img src="img/bot_'+d.tipo+'.png"/>');
            }
          },
          error: function(o,s,e){
            alert(s);
          }
        });
        
      });
      
      
    });
       
    function addRemoveISBN(isbnnumber){

      var e = $("#isbn_"+isbnnumber);
      
      if ($(e).hasClass("selectedisbn")) {

        $.ajax({type:'POST', async:false, url:'ajax_removebook.php', timeout: 50000, cache:false, data: 'isbn='+isbnnumber, context:this, dataType: 'json',
          success: function(d,s){
            $(e).removeClass("selectedisbn").html("<img src='img/add.png'/>");
          },
          error: function(o,s,e){
            alert(s);
          }
        });
             
      } else {
      
        var t = $("#td_"+isbnnumber).html();
      
        $.ajax({type:'POST', async:false, url:'ajax_addbook.php', timeout: 50000, cache:false, data: 'isbn='+isbnnumber+'&titulo='+t, context:this, dataType: 'json',
          success: function(d,s){
            $(e).removeClass().addClass("selectedisbn").html("<img src='img/added.png'/>");
          },
          error: function(o,s,e){
            alert(s);
          }
        });
        
      } 
        
    }
FIN;
  } else if ($script==2) {
    $javascript = <<<FIN
    $().ready(function() {
      
      $('#resultados tbody tr').each(function(){
        var isbn = $(this).attr('id').substring(4);
        $(this).find('td:eq(0)').html('<img src="img/progress.gif"/>');
        $('p.actu').html($(this).find('td:eq(1)').html().substring(0,32) + '...');
        $.ajax({type:'POST', async:false, url:'ajax_getinfobyisbn.php', timeout: 50000, cache:false, data: 'isbn='+isbn, context:this, dataType: 'json',
          success: function(d,s){
            //alert(d);
            $(this).find('td:eq(2)').html(d.disponibilidad);
            $(this).find('td:eq(3)').html(d.localizacion);
            $(this).find('td:eq(4)').html(d.signatura);            
            if (d.isbn=='' || d.isbn==null){
              $(this).find('td:eq(0)').html('<img src="img/bot_disabled.png"/>');
              $(this).addClass('bot_disabled');
            } else {
              $(this).find('td:eq(2)').removeClass().addClass('bot_'+d.tipo);
              $(this).find('td:eq(0)').html('<img src="img/bot_'+d.tipo+'.png"/>');
            }
          },
          error: function(o,s,e){
            alert(s);
          }
        });
        $('p.actu').html('Próxima comprobación en 60 minutos');
        
        
      });
      
      
    });
    
    
FIN;
}
  
  echo <<<FIN
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  dir="ltr" lang="es-ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="description" content="Buscador de libros en las Bibliotecas Públicas de la Comunidad de Madrid. Entrada para AbreDatos 2011." />
  <title>Abrelibros para AbreDatos 2011</title>
  <link href="img/favicon.ico" rel="shortcut icon" type="image/x-icon" />
  <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Lobster"/>
  <link rel="stylesheet" type="text/css" href="css/css.css"/>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
  <script type="text/javascript">                                         
  $javascript;                                     
  </script>  
  <script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-127745-19']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>   
</head>
<body>
<div id="container">
  <div id="article">
    
FIN;
}

function cabecera($isLogged = true){
  
  
  if ($isLogged){
    
    $biblioName = getBiblioName($_SESSION['bibliokey']);

    echo <<<FIN
    <div id="header">
    
    <div id="mainlocabibli">
      <span class="locabibli">
        Estás en la biblioteca de $biblioName
        <br/>
        <a href="bibliochoose.php">¿Quieres cambiar?</a>
      </span>
      <p class="actu">{$_SESSION['disponibilidad']}</p>
    </div>
    
    <div id="headertit">
    <h1><a href="main.php"><span>abre</span>Libros</a></h1>
    </div>
    
    <div id="user">
      <div class="fleft">
      <img width="48" height="48" alt="user" src="{$_SESSION['avatar']}"/>
      </div>
      <div class="flright">
      <a href="http://twitter.com/{$_SESSION['username']}">@{$_SESSION['username']}</a>
      </div>
      <br/>
      <br/>
      <div class="flright">
      <a href="logout.php"> salir</a>
      </div>
    </div>
   
  </div>
  <br clear="all">
    
    
    

FIN;
        
  } else {

    echo <<<FIN
  <div id="header">
  <h1><span>abre</span>Libros</h1>
  </div>
FIN;
  
  }
  
  echo "<div class='articleBody  clear'>";
  
}

function endhtml(){
  echo <<<FIN
</div>
</div>
<div id='footer'>
<p>
AbreLibros es un proyecto de <a href="http://tecnilogica.com" target="_blank">Tecnilógica</a> 
para <a href="http://www.abredatos.es/" target="_blank">AbreDatos 2011</a> - <a href="http://code.google.com/p/abrelibros/" target="_blank">Código fuente</a>
<br/>
Hemos usado <a href="http://net.tutsplus.com/tutorials/php/how-to-authenticate-users-with-twitter-oauth/" target="_blank">Nettuts+</a>, 
<a href="https://github.com/abraham/twitteroauth" target="_blank">TwitterOAuth</a>,
<a href="http://www.txt2re.com/" target="_blank">txt2re</a>,
<a href="http://www.apachefriends.org/es/xampp.html" target="_blank">xampp</a>,
<a href="http://jquery.com/" target="_blank">jQuery</a>,
<a href="http://www.google.com/webfonts" target="_blank">Google Web Fonts</a>,
<a href="http://www.ajaxload.info/" target="_blank">Ajaxload</a>,<br/>
<a href="http://tutorialzine.com/2010/02/free-xhtml-css3-website-template/" target="_blank">Tutorialzine</a>,
<a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">Silk Icons</a>,
<a href="http://www.uservoice.com/" target="_blank">UserVoice</a>,
<a href="http://projects.korrelboom.com/gradient-generator/" target="_blank">Cross-Browser Gradient Generator</a>
</p>
</div>
</div>
<script type="text/javascript">
  (function() {
    var uv = document.createElement('script'); uv.type = 'text/javascript'; uv.async = true;
    uv.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'widget.uservoice.com/znLhhLXxuzqmhhY0EfHRw.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uv, s);
  })();
</script>
</body>
</html>
FIN;
}
