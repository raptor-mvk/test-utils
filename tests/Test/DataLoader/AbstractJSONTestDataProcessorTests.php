<?php
declare(strict_types=1);

namespace RaptorTests\Test\DataLoader;

use Mockery;
use PHPUnit\Framework\TestCase;
use Raptor\Test\DataProcessor\AbstractJSONTestDataProcessor;
use Raptor\Test\DataProcessor\TestContainerTestDataProcessor;
use Raptor\Test\Exceptions\DataParseException;

/**
 * Класс с тестами для абстрактного класса обработчика JSON-файлов
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class AbstractJSONTestDataProcessorTests extends TestCase
{
    pri

    /**
     * Проверяет, что метод _process_ выбрасывает исключение `DataParseException` с сообщением о синтаксической ошибке
     * при ошибке разбора JSON-данных.
     */
    public function testProcessThrowDataParseExceptionWithCorrectMessageOnJSONSyntaxError(): void
    {
        $this->expectException(DataParseException::class);
        $this->expectExceptionMessageRegExp('/^Ошибка при разборе JSON-данных$/');

        $data = '[{"some_field}]';
        $dataProcessor = Mockery::mock(AbstractJSONTestDataProcessor::class);

        $dataProcessor->process($data);
    }
}