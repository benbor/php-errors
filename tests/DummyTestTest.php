<?php

namespace Benbor\PhpErrors;

use PHPUnit\Framework\TestCase;

class DummyTestTest extends TestCase
{
    public function testDummy()
    {
        self::assertSame('Tasd', "asdf");
    }
}
