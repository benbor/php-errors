<?php
declare(strict_types=1);

namespace Benbor\PhpErrors\phpErrorsTest;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    /**
     * @var array[]
     */
    private static $type_map = [
        E_ERROR => ['name' => 'E_ERROR', 'expectedNo' => 1],
        E_WARNING => ['name' => 'E_WARNING', 'expectedNo' => 2],
        E_PARSE => ['name' => 'E_PARSE', 'expectedNo' => 4],
        E_NOTICE => ['name' => 'E_NOTICE', 'expectedNo' => 8],
        E_CORE_ERROR => ['name' => 'E_CORE_ERROR', 'expectedNo' => 16],
        E_CORE_WARNING => ['name' => 'E_CORE_WARNING', 'expectedNo' => 32],
        E_COMPILE_ERROR => ['name' => 'E_COMPILE_ERROR', 'expectedNo' => 64],
        E_COMPILE_WARNING => ['name' => 'E_COMPILE_WARNING', 'expectedNo' => 128],
        E_USER_ERROR => ['name' => 'E_USER_ERROR', 'expectedNo' => 256],
        E_USER_WARNING => ['name' => 'E_USER_WARNING', 'expectedNo' => 512],
        E_USER_NOTICE => ['name' => 'E_USER_NOTICE', 'expectedNo' => 1024],
        E_STRICT => ['name' => 'E_STRICT', 'expectedNo' => 2048],
        E_RECOVERABLE_ERROR => ['name' => 'E_RECOVERABLE_ERROR', 'expectedNo' => 4096],
        E_DEPRECATED => ['name' => 'E_DEPRECATED', 'expectedNo' => 8192],
        E_USER_DEPRECATED => ['name' => 'E_USER_DEPRECATED', 'expectedNo' => 16384],
    ];

    public function mapNameCodeExpected()
    {
        foreach (self::$type_map as $code => $map) {
            yield $map['name'] => [$code, $map['expectedNo']];
        }
    }

    /**
     * @dataProvider mapNameCodeExpected
     */
    public function testTypeCodesAreExpected($code, $expectedCode)
    {
        self::assertSame($expectedCode, $code);
    }

    public function mapErrorCodeErrorName()
    {
        foreach (self::$type_map as $code => $map) {
            yield $map['name'] => [$code, $map['name']];
        }
    }

    /**
     * @dataProvider mapErrorCodeErrorName
     */
    public function testPhp8Errors($errorCode, $errorName)
    {
        if (PHP_VERSION_ID < 80000 || PHP_VERSION_ID >= 90000) {
            $this->markTestSkipped("because of expected PHP8");
        }
        $this->skipCodes($errorCode, [E_NOTICE], "because of PHP8 do not trigger E_NOTICE");
        self::expectPhpError($errorName, function () use ($errorName) {
            /** @noinspection PhpIncludeInspection */
            include __DIR__ . "/../_errors/$errorName.php";
        });

    }

    /**
     * @dataProvider mapErrorCodeErrorName
     */
    public function testPhp7Errors($errorName, $errorCode)
    {
        if (PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped("because of expected PHP7");
        }
        self::expectPhpError($errorName, function () {
            /** @noinspection PhpIncludeInspection */
            include __DIR__ . "../_errors/$errorName.php";
        });

    }

    private function expectPhpError($expectedName, \Closure $closure)
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
        self::assertNull($throwable, sprintf(
            "PHP Engine error expected: %s (%d)\n".
            "Got exception:\n %s",
            self::$type_map[$expectedNo]['name'],
            $expectedNo,
            (string) $throwable
        ));
        self::assertNotNull($errno, sprintf(
            "PHP Engine error expected: %s (%d)\n".
            "Got nothing",
            self::$type_map[$expectedNo]['name'],
            $expectedNo
        ));
        self::assertSame($expectedNo, $errno, sprintf(
            "PHP Engine error expected: %s (%d)\n" .
            "Got %s (%d) on %s:%s",
            self::$type_map[$expectedNo]['name'],
            $expectedNo,
            self::$type_map[$errno]['name'],
            $errno,
            $errstr,
            $errfile,
        ));
    }

    private function skipCodes($code, array $haystack, string $message)
    {
        if (in_array($code, $haystack, true)) {
            $this->markTestSkipped($message);
        }
    }
}
