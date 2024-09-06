<?php
  $host_name = 'db5015909185.hosting-data.io';
  $database = 'dbs12966751';
  $user_name = 'dbu3832332';
  $password = 'Temporal1_2024!';
  $dbh = null;

  try {
    $dbh = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
    echo "Conexión realizada con exito" ."<br/>";
  } catch (PDOException $e) {
    echo "Error!:" . $e->getMessage() . "<br/>";
    die();
  }
?>
