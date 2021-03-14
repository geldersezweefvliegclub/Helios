<?php

chdir(".."); // terug naar de home directory

if (file_exists("installer_account.php"))
{
    header('HTTP/1.0 409 Conflict');
    die();
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.0 400 Wrong request');
    die();
} 

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

$key = sha1(strtolower ($username) . $password);

$file_content = sprintf(
    "<?php

    \$installer_account = array(
        'id' => 0,
        'username' => '%s',
        'password' => '%s'
    );
    
    ?>", $username, $key);

$file = fopen("installer_account.php", "w") or die("Unable to open installer_account.php file!");
fwrite($file, $file_content);
fclose($file);


?>