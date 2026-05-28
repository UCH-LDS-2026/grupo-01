 <?php
session_start();
session_destroy();

header("Location: /MediFlow/grupo-01/MediFlow/vista/login.php");
exit;
?>