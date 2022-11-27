<?php
namespace DigiWallet;

/**
 *  DigiWallet autoloader
 *
 *  Only use this if no global autoloader is present and can be used
 *  Usage:
 *
 *     require_once "<path-to-here>/DigiWallet/autoload.php"
 * @param $name
 */
function autoload($name)
{
    // Check if this is a DigiWallet class
    if (\substr_compare($name, "DigiWallet\\", 0, 10) !== 0) return;

    // Remove namespace from class name
    $stem = \substr($name, 10);

    // Convert "\" and "_" to path separators.
    $pathified = \str_replace(array("\\", "_"), '/', $stem);

    $path = __DIR__ . "/" . $pathified . ".php";
    if (\is_file($path)) require_once $path;
}

\spl_autoload_register('DigiWallet\autoload');
