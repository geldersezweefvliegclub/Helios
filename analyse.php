<?php

$message = "hello world";

header("X-Error-Message: $message", false, intval($_GET["r"]));
header("Content-Type: text/plain");

?>