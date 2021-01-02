<?php

echo "check";
$foo = file_get_contents("php://input");

var_dump(json_decode($foo, true));

//$conn = new mysqli($db_info['dbHost'], $db_info['dbUser'], $db_info['dbPassword']);

?>