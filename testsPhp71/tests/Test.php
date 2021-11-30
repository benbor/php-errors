<?php
declare(strict_types=1);

use Benbor\PhpErrors\TestsBase\ErrorConfigRegister;
use Benbor\PhpErrors\TestsBase\PhpErrorTester;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{

    /**
     * @var PhpErrorTester
     */
    private $errorTester;
    /**
     * @var ErrorConfigRegister
     */
    private $casesRegister;

    public function __construct(string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->errorTester = new PhpErrorTester();
        $this->casesRegister = new ErrorConfigRegister();

    }

    public function testTypeCodesAreExpected()
    {
        $this->errorTester->baseTestThatPhpCodeNoExpected();
    }

    public function mapErrorCodeErrorName()
    {
        yield from $this->casesRegister->casesFor("PHP71");
    }

    /**
     * @dataProvider mapErrorCodeErrorName
     */
    public function testPhp7Errors($expected, $reproScript)
    {
        $this->errorTester->expectPhpBehavior($expected, function () use ($reproScript) {
            include $reproScript;
        });

    }


}
