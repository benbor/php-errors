<?php
declare(strict_types=1);

namespace Benbor\PhpErrors\TestsBase;

class ErrorConfigRegister
{
    /**
     * @var ErrorConfig[]
     */
    private $cases;

    private $casesDir = __DIR__ . '/../cases/';

    private $csvColumnsConfig = [
        "fileName" => 0,
        ErrorConfig::PHP71 => 1,
        ErrorConfig::PHP72 => 2,
        ErrorConfig::PHP73 => 3,
        ErrorConfig::PHP74 => 4,
        ErrorConfig::PHP80 => 5,
        ErrorConfig::PHP81 => 6,
    ];

    public function __construct()
    {
        $this->cases = $this->loadCases($this->casesDir . 'Register.csv');
    }

    /**
     * @param string $path
     * @return array<ErrorConfig>
     */
    private function loadCases(string $path)
    {
        $phpErrors = [];

        if (($handle = fopen($path, "r")) !== false) {

            /** @var array<string> $headers */
            if (($headers = fgetcsv($handle)) === false) {
                throw  new \LogicException("File $path contains no rows");
            }

            $i = 0;
            $headers = $this->assertLine($headers, $i);
            $this->assertHeaders($headers);

            while (($data = fgetcsv($handle)) !== false) {
                $i++;
                if ($this->skipLine($data)) {
                    continue;
                }
                $data = $this->assertLine($data, $i);
                $phpErrors[] = new ErrorConfig(
                    $data[$this->csvColumnsConfig['fileName']],
                    $data[$this->csvColumnsConfig[ErrorConfig::PHP71]],
                    $data[$this->csvColumnsConfig[ErrorConfig::PHP72]],
                    $data[$this->csvColumnsConfig[ErrorConfig::PHP73]],
                    $data[$this->csvColumnsConfig[ErrorConfig::PHP74]],
                    $data[$this->csvColumnsConfig[ErrorConfig::PHP80]],
                    $data[$this->csvColumnsConfig[ErrorConfig::PHP81]]
                );
            }

            // close handler
            fclose($handle);
        }

        return $phpErrors;
    }


    /**
     * @param array<string> $headers
     */
    private function assertHeaders(array $headers)
    {
        if (count($this->csvColumnsConfig) !== count($headers)) {
            throw new \LogicException(sprintf("Csv should contain exactly %d. Presented %d columns", count($this->csvColumnsConfig), count($headers)));
        }
        foreach ($this->csvColumnsConfig as $columnName => $expectedIndex) {
            if ($headers[$expectedIndex] !== $columnName) {
                throw new \LogicException("Header #$expectedIndex should be called $columnName");
            }
        }
    }

    private function assertLine(array $data, int $lineNumber)
    {
        $result = [];
        foreach ($this->csvColumnsConfig as $columnName => $expectedIndex) {
            if (!isset($data[$expectedIndex])) {
                throw new \LogicException("Csv should contain data on raw #$lineNumber  for $columnName column.");
            }
            $value = trim($data[$expectedIndex]);

            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param string $phpVersion
     * @return \Generator
     */
    public function casesFor(string $phpVersion)
    {
        foreach ($this->cases as $case) {
            yield $case->getFileName() => [$case->getExpectedFor($phpVersion), $this->casesDir . $case->getFileName()];
        }
    }

    private function skipLine(array $data): bool
    {
        // skip empty string
        if ($data[0] === null) {
            return true;
        }

        // skip if comments
        if (strpos($data[0], '#') === 0) {
            return true;
        }

        return false;
    }
}