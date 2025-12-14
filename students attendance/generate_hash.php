<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Your hashed password is: <br><br>";
echo $hash;
echo "<br><br>Copy this and update your database.";
?>