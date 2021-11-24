--INI--
error_reporting=0
--FILE--
<?php

use PHPUnit\Framework\Assert;
use Symfony\Component\ErrorHandler\ErrorHandler;

$vendor = __DIR__;
while (!file_exists($vendor . '/vendor')) {
    $vendor = \dirname($vendor);
}
require $vendor . '/vendor/autoload.php';


ErrorHandler::register();

try {
    trigger_error("Test", E_USER_ERROR);
    Assert::fail("Should be never achieved");
} catch (\ErrorException $e) {
    echo "Success";
}

?>
--EXPECT--
Successs