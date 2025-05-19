<?php
  $hostname = "192.168.6.14";
  $username = "aleks";
  $password = "masmetrolog14";
  $dbname = "masmetrologia";

  $conn = mysqli_connect($hostname, $username, $password, $dbname);
  if(!$conn){
    echo "Database connection error".mysqli_connect_error();
  }
?>
