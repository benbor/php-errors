<?php

declare(strict_types=1);

namespace Benbor\PhpErrors\TestsBase;

use PHPUnit\Framework\Assert;

class PhpErrorTester
{
    /**
     * @var array<int, PhpError>
     */
    private $typeMap;

    public function __construct() {
        $this->typeMap =  [
            E_ERROR => new PhpError("E_ERROR", 1),
            E_WARNING => new PhpError("E_WARNING", 2),
            E_PARSE => new PhpError("E_PARSE", 4),
            E_NOTICE => new PhpError("E_NOTICE", 8),
            E_CORE_ERROR => new PhpError("E_CORE_ERROR", 16),
            E_CORE_WARNING => new PhpError("E_CORE_WARNING", 32),
            E_COMPILE_ERROR => new PhpError("E_COMPILE_ERROR", 64),
            E_COMPILE_WARNING => new PhpError("E_COMPILE_WARNING", 128),
            E_USER_ERROR => new PhpError("E_USER_ERROR", 256),
            E_USER_WARNING => new PhpError("E_USER_WARNING", 512),
            E_USER_NOTICE => new PhpError("E_USER_NOTICE", 1024),
            E_RECOVERABLE_ERROR => new PhpError("E_RECOVERABLE_ERROR", 4096),
            E_DEPRECATED => new PhpError("E_DEPRECATED", 8192),
            E_USER_DEPRECATED => new PhpError("E_USER_DEPRECATED", 16384),
        ];
    }

    public function baseTestThatPhpCodeNoExpected()
    {
        foreach ($this->typeMap as $code => $error) {
            Assert::assertEquals($error->getCode(), $code);
        }
    }

    public function expectPhpBehavior($expectedName, \Closure $closure)
    {
        if ($expectedName === "NULL") {
            $this->expectNothing();
            return;
        }

        if (strpos($expectedName, 'E_') === 0) {
            $this->expectPhpEngineError($expectedName, $closure);
            return;
        }

        if (strpos($expectedName, 'Error') !== false) {
            $this->expectPhpErrorThrowable($expectedName, $closure);
            return;
        }
    }
    private function expectPhpEngineError($expectedName, \Closure $closure)
    {
        $expectedNo = constant($expectedName);
        $throwable = null;

        $errno = $errstr = $errfile = $errline = null;
        $oldErrorHandler = set_error_handler(function ($eno, $estr, $efile, $eline) use (&$errno, &$errstr, &$errfile, &$errline) {
            $errno = $eno;
            $errstr = $estr;
            $errfile = $efile;
            $errline = $eline;

            return true; //prevent native callback
        });
        try {
            $closure();
        } catch (\Throwable $throwable) {
            //processing below
        } finally {
            if ($oldErrorHandler !== null) {
                restore_error_handler();
            }
        }
        Assert::assertNull($throwable, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got exception:\n %s",
            $this->typeMap[$expectedNo]->getName(),
            $expectedNo,
            (string)$throwable
        ));
        Assert::assertNotNull($errno, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got nothing",
            $this->typeMap[$expectedNo]->getName(),
            $expectedNo
        ));
        Assert::assertSame($expectedNo, $errno, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got %s (%d) on %s:%s",
            $this->typeMap[$expectedNo]->getName(),
            $expectedNo,
            $this->typeMap[$errno]->getName(),
            $errno,
            $errstr,
            $errfile
        ));
    }
//
//    public function skipCodes($code, array $haystack, string $message)
//    {
//        if (in_array($code, $haystack, true)) {
//            $this->markTestSkipped($message);
//        }
//    }
    private function expectNothing()
    {
    }
}