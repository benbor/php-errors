<?php

declare(strict_types=1);

namespace Benbor\PhpErrors;

class PhpError
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $code;

    public function __construct(string $name, int $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

}