<?php
declare(strict_types=1);

namespace Raptor\TestUtils\Command;

use Raptor\TestUtils\DataLoader\ProcessingDataLoader;
use Raptor\TestUtils\DataLoader\RecursiveDirectoryDataLoader;
use Raptor\TestUtils\DataProcessor\GeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;
use Raptor\TestUtils\Generator\Generator;
use Raptor\TestUtils\Generator\TestDataContainerGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command, that generates service file _ide_test_container.php file in project root folder for IDE. The file is
 * used for autocomplete.
 *
 * @author Mikhail Kamorin aka raptor_MVK
 *
 * @copyright 2019, raptor_MVK
 */
final class GenerateIDETestContainerCommand extends Command
{
    /** @var Generator $generator */
    private $generator;

    /** @var string $filePath */
    private $filePath;

    /**
     * @param string $filePath path to file that should be generated
     */
    public function __construct(string $filePath)
    {
        parent::__construct();
        $typeFactory = new GetTypeTypeFactory();
        $dataProcessor = new GeneratorDataProcessor($typeFactory);
        $dataLoader = new ProcessingDataLoader($dataProcessor);
        $directoryDataLoader = new RecursiveDirectoryDataLoader($dataLoader);
        $this->generator = new TestDataContainerGenerator($directoryDataLoader);
        $this->filePath = $filePath;
    }

    /** @noinspection PhpMissingParentCallCommonInspection __approved__ parent method is overridden */
    /** @noinspection PhpUnused __approved__ used in generate-ide-test-containers */
    protected function configure(): void
    {
        $this->setName('generate:ide_test_container')
             ->setDescription('Generates helper file _ide_test_container.php')
             ->addArgument('path', InputArgument::REQUIRED, 'Enter the root folder for JSON test files:');
    }

    /** @noinspection PhpMissingParentCallCommonInspection __approved__ parent method is overridden */
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    /** @noinspection PhpUnused __approved__ used in generate-ide-test-containers */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getArgument('path');
        $content = $this->generator->generate($path);
        file_put_contents("{$this->filePath}/_ide_test_container.php", $content);
    }
}
