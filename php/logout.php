<?php
session_start();
session_unset();  
session_destroy(); 
header("Location: ../php/login.php");
echo "Log out successfully";
exit();
?>
