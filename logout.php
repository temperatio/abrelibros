<?php
//ini_set('display_errors', 1);


  session_start();
  session_destroy();
  session_unset();

  header('Location: index.php');