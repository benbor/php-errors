<?php

namespace Benbor\PhpErrors\TestsBase;

use PHPUnit\Framework\TestCase;

abstract class PhpUnitAbstractTestCase extends TestCase
{
    /**
     * @var array<int, PhpError>
     */
    private $typeMap;
    /**
     * @var string
     */
    private $testingPhpVersion;
    /**
     * @var ErrorConfigRegister
     */
    private $casesRegister;

    public function __construct($testingPhpVersion, $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (!self::isPhpVersionParamValid($testingPhpVersion)) {
            self::fail("Not supported PHP version: $testingPhpVersion");
        }
        $this->testingPhpVersion = $testingPhpVersion;
        $this->typeMap = [
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

        $this->casesRegister = new ErrorConfigRegister();
    }

    /* ============= Abstract tests ============= */
    /*
     * To the correct work with PHPUnit filters, each test should be specified
     * in the non-abstract class. It is why here in the abstract class defined stubs,
     * which should be called
     */

    public function dataProviderTestPhpErrors()
    {
        yield from $this->casesRegister->casesFor($this->testingPhpVersion);
    }
    protected function executeTestPhpErrors($expected, $reproScript)
    {
        $this->expectPhpBehavior($expected, function () use ($reproScript) {
            include $reproScript;
        });
    }
    abstract public function testPhpErrors($expected, $reproScript);

    protected function executeTestTypeCodesAreExpected()
    {
        foreach ($this->typeMap as $code => $error) {
            self::assertEquals($error->getCode(), $code);
        }
    }

    abstract public function testTypeCodesAreExpected();

    /* ============= End abstract tests ============= */

    private static function isPhpVersionParamValid($testingPhpVersion): bool
    {
        $isValidVersion = in_array($testingPhpVersion, [
            ErrorConfig::PHP70,
            ErrorConfig::PHP71,
            ErrorConfig::PHP72,
            ErrorConfig::PHP73,
            ErrorConfig::PHP74,
            ErrorConfig::PHP80,
            ErrorConfig::PHP81,
        ], true);

        $isValidEnv = $testingPhpVersion === 'PHP'.PHP_MAJOR_VERSION.PHP_MINOR_VERSION;

        return $isValidVersion && $isValidEnv;
    }

    private function expectPhpBehavior($expectedName, \Closure $closure)
    {
        if ($expectedName === "NULL") {
            $this->expectNothing($expectedName, $closure);
            return;
        }

        if (strpos($expectedName, 'E_') === 0) {
            $this->expectPhpEngineError($expectedName, $closure);
            return;
        }

        if (strpos($expectedName, 'Error') !== false) {
            $this->expectThrowableError($expectedName, $closure);
            return;
        }

        self::fail(sprintf(
            "The expected value should be one of the following:\n"
            . " - E_* Engine error\n"
            . " - *Error Throwable\n"
            . " - NULL\n"
            ."Got %s",
            $expectedName
        ));
    }

    private function expectPhpEngineError($expectedName, \Closure $closure)
    {
        list($throwable, $errno, $errstr, $errfile, $errline) = $this->execute($closure);

        $expectedNo = constant($expectedName);
        self::assertNull($throwable, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got exception:\n %s",
            $this->typeMap[$expectedNo]->getName(),
            $expectedNo,
            (string)$throwable
        ));
        self::assertNotNull($errno, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got nothing",
            $this->typeMap[$expectedNo]->getName(),
            $expectedNo
        ));
        self::assertSame($expectedNo, $errno, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got PHP Engine error %s (%d) on %s:%s\n %s",
            $this->typeMap[$expectedNo]->getName(),
            $expectedNo,
            $this->typeMap[$errno]->getName(),
            $errno,
            $errfile,
            $errline,
            $errstr
        ));
    }

    private function expectThrowableError($expectedName, \Closure $closure)
    {
        list($throwable, $errno, $errstr, $errfile, $errline) = $this->execute($closure);

        if ($errno !== null || $errstr !== null || $errfile!== null || $errline !== null) {
            self::fail(sprintf(
                "PHP Throwable error expected: %s \n" .
                "Got PHP Engine error %s (%d) on %s:%s\n %s",
                $expectedName,
                $this->typeMap[$errno]->getName(),
                $errno,
                $errfile,
                $errline,
                $errstr
            ));
        }


        self::assertNotNull($throwable, sprintf(
            "PHP Throwable error expected: %s \n" .
            "Got nothing",
            $expectedName
        ));

        self::assertSame($expectedName, get_class($throwable), sprintf(
            "PHP Throwable error expected: %s \n" .
            "Got:\n %s",
            $expectedName,
            (string)$throwable
        ));

    }

    private function expectNothing($expectedName, \Closure $closure)
    {
        list($throwable, $errno, $errstr, $errfile, $errline) = $this->execute($closure);

        if ($errno !== null || $errstr !== null || $errfile!== null || $errline !== null) {
            self::fail(sprintf(
                "No errors expected \n" .
                "Got PHP Engine error %s (%d) on %s:%s\n %s",
                $this->typeMap[$errno]->getName(),
                $errno,
                $errfile,
                $errline,
                $errstr
            ));
        }

        self::assertNull($throwable, sprintf(
            "No errors expected \n" .
            "Got exception:\n %s",
            (string)$throwable
        ));
    }

    /**
     * @param \Closure $closure
     * @return array
     */
    private function execute(\Closure $closure): array
    {
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
        return [$throwable, $errno, $errstr, $errfile, $errline];
    }

}