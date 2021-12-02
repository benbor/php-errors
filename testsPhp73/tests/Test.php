<?php
declare(strict_types=1);

use Benbor\PhpErrors\TestsBase\PhpUnitAbstractTestCase;

class Test extends PhpUnitAbstractTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct("PHP73", $name, $data, $dataName);
    }

    /**
     * @dataProvider dataProviderTestPhpErrors
     */
    public function testPhpErrors($expected, $reproScript)
    {
        $this->executeTestPhpErrors($expected, $reproScript);
    }

    public function testTypeCodesAreExpected()
    {
        $this->executeTestTypeCodesAreExpected();
    }
}
