--INI--

--FILE--
<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\Assert;
use Symfony\Component\ErrorHandler\ErrorHandler;

$vendor = __DIR__;
while (!file_exists($vendor . '/vendor')) {
    $vendor = \dirname($vendor);
}
require $vendor . '/vendor/autoload.php';


$handler = new ErrorHandler();
$logger = new Logger('monolog', [new StreamHandler('php://stdout')]);
$handler->setDefaultLogger($logger);
$handler->throwAt(0);
//$handler->screamAt();

ErrorHandler::register($handler);

try {
    trigger_error("Test", E_USER_ERROR);
    Assert::fail("Should be never achieved");
} catch (\ErrorException $e) {
    echo "Success";
}


var_dump($logger);
?>
--EXPECT--
monolog
Success