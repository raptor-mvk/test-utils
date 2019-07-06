<?php
declare(strict_types=1);

namespace RaptorTests\TestUtils;

use PHPUnit\Framework\TestCase;
use Raptor\TestUtils\ExtraAssertions;
use Raptor\TestUtils\TestDataContainer\TestDataContainer;
use Raptor\TestUtils\WithDataLoader;
use Raptor\TestUtils\WithVFS;

/**
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class WithDataLoaderTests extends TestCase
{
    use ExtraAssertions, WithVFS, WithDataLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupVFS();
    }

    /**
     * Checks that method _load_ returns correct data.
     */
    public function testLoadReturnsCorrectData(): void
    {
        $filename = 'some_file.json';
        $content = $this->prepareFileContent();
        $this->addFileToVFS($filename, null, $content);
        $fullFilename = $this->getFullPath($filename);
        $extractData = static function (TestDataContainer $container) {
            return $container->allData();
        };
        $expectedData = $this->prepareExpectedResult();

        $actualData = $this->loadDataFromFile($fullFilename);

        $extractedActualData = array_map($extractData, $actualData);

        static::assertArraysAreSame($expectedData, $extractedActualData);
    }

    /**
     * Prepares content of input file.
     *
     * @return string
     */
    private function prepareFileContent(): string
    {
        $data = [
            [
                'param_one' => 'some_value',
                '_children' => [
                    ['_name' => 'test1'],
                    ['_name' => 'test2', 'param_two' => 'no_value'],
                    ['_name' => 'test3', 'param_two' => 'extra_value', 'param_five' => ['empty_value', 'this_value']]
                ]
            ]
        ];
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Prepares expected result of data loading process.
     *
     * @return array
     */
    private function prepareExpectedResult(): array
    {
        return [
            'test1' => ['param_one' => 'some_value'],
            'test2' => ['param_one' => 'some_value', 'param_two' => 'no_value'],
            'test3' => [
                'param_one' => 'some_value',
                'param_two' => 'extra_value',
                'param_five' => ['empty_value', 'this_value']
            ]
        ];
    }
}
