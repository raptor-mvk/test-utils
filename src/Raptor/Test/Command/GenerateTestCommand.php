<?php
declare(strict_types=1);

namespace Raptor\Test\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда, генерирующая загрузчик данных и контейнер данных для набора тестов из JSON-файла.
 *
 * @author Михаил Каморин aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
class GenerateTestCommand extends Command
{
    /** @noinspection PhpMissingParentCallCommonInspection __approved__ переопределение родительского метода */
    /**
     * Конфигурация команды.
     */
    protected function configure(): void
    {
        // ...
    }

    /** @noinspection PhpMissingParentCallCommonInspection __approved__ переопределение родительского метода */
    /**
     * Запуск команды.
     *
     * @param InputInterface    $input      поток ввода
     * @param OutputInterface   $output     поток вывода
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // ...
    }
}