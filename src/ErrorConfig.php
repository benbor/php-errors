<?php
declare(strict_types=1);

namespace Benbor\PhpErrors;

class ErrorConfig
{
    const PHP70 = "PHP70";
    const PHP71 = "PHP71";
    const PHP72 = "PHP72";
    const PHP73 = "PHP73";
    const PHP74 = "PHP74";
    const PHP80 = "PHP80";
    const PHP81 = "PHP81";

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var array
     */
    private $expected;

    public function __construct(string $fileName, string $php70, string $php71, string $php72, string $php73, string $php74, string $php80, string $php81)
    {
        $this->fileName = $fileName;
        $this->expected[self::PHP70] = $php70;
        $this->expected[self::PHP71] = $php71;
        $this->expected[self::PHP72] = $php72;
        $this->expected[self::PHP73] = $php73;
        $this->expected[self::PHP74] = $php74;
        $this->expected[self::PHP80] = $php80;
        $this->expected[self::PHP81] = $php81;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getExpectedFor(string $phpVersion)
    {
        return $this->expected[$phpVersion];
    }

}