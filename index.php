<?php
if (isset($_GET["logout"])) {
  session_start();
  unset($_SESSION);
  session_destroy();
  header("Location: index");
  exit();
}

 header("Location: create");
 ?>
