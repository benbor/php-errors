<?php

if (PHP_MAJOR_VERSION !== 7 && PHP_MINOR_VERSION !== 4) {
    fwrite(
        STDERR,
        sprintf(
            'This test should be run on PHP 7.4' . PHP_EOL .
            'You are using PHP %s (%s).' . PHP_EOL,
            PHP_VERSION,
            PHP_BINARY
        )
    );

    die(1);
}

require_once __DIR__ . '/../vendor/autoload.php';
