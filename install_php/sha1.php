<?php
$request_body = json_decode(file_get_contents('php://input'));
$ingave = $request_body->ingave;

echo sha1($ingave);
?>