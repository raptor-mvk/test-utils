<?php
declare(strict_types=1);

/**
 * @license MIT
 * @license https://github.com/raptor-mvk/test-utils/blob/master/license.md
 */

namespace Raptor\TestUtils\Command;

use Raptor\TestUtils\DataLoader\ProcessingDataLoader;
use Raptor\TestUtils\DataLoader\RecursiveDirectoryDataLoader;
use Raptor\TestUtils\DataProcessor\GeneratorDataProcessor;
use Raptor\TestUtils\DataProcessor\TypeFactory\GetTypeTypeFactory;
use Raptor\TestUtils\Generator\GeneratorInterface;
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
    /** @var int OK exit code when everything is OK */
    public const OK = 0;

    /** @var int ERROR exit code when target file is not writable */
    public const ERROR = 1;

    /** @var GeneratorInterface $generator */
    private $generator;

    /** @var string $filePath */
    private $filePath;

    /**
     * @param string $filePath path to file that should be generated
     */
    public function __construct(string $filePath)
    {
        $typeFactory = new GetTypeTypeFactory();
        $dataProcessor = new GeneratorDataProcessor($typeFactory);
        $dataLoader = new ProcessingDataLoader($dataProcessor);
        $directoryDataLoader = new RecursiveDirectoryDataLoader($dataLoader);
        $this->generator = new TestDataContainerGenerator($directoryDataLoader);
        $this->filePath = $filePath;
        parent::__construct();
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @noinspection PhpUnused __approved__ used in generate-ide-test-containers
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $content = $this->generator->generate($path);
        $this->outputGeneratorErrors($output);
        $filename = "{$this->filePath}/_ide_test_container.php";
        if (file_exists($filename) && !is_writable($filename)) {
            $output->write("<error>Could not write to the file _id_test_container.php.</error>\n");

            return self::ERROR;
        }
        file_put_contents($filename, $content);
        $output->write("<info>File _id_test_container.php was successfully generated.</info>\n");

        return self::OK;
    }

    /**
     * Outputs error messages from generator.
     *
     * @param OutputInterface $output
     */
    private function outputGeneratorErrors(OutputInterface $output): void
    {
        $errors = $this->generator->getLastErrors();
        if (!empty($errors)) {
            foreach ($errors as $filename => $error) {
                $output->write("<error>$filename: $error\n");
            }
        }
    }
}
