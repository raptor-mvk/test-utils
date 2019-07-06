<?php
declare(strict_types=1);

namespace Raptor\TestUtils\Command;

use Raptor\TestUtils\Generator\Generator;
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
final class GenerateIDETestContainersCommand extends Command
{
    /** @var Generator $generator */
    private $generator;

    /** @var string $filePath */
    private $filePath;

    /**
     * @param Generator $generator
     * @param string $filePath path to file that should be generated
     */
    public function __construct(Generator $generator, string $filePath)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->filePath = $filePath;
    }

    /** @noinspection PhpMissingParentCallCommonInspection __approved__ parent method is overridden */
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
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $path = $input->getArgument('path');
        $content = $this->generator->generate($path);
        file_put_contents("{$this->filePath}/_ide_test_container.php", $content);
    }
}
