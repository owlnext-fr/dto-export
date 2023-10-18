<?php

namespace OwlnextFr\DtoExport\Command;

use OwlnextFr\DtoExport\DTOExport\DTOExporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DTOExportCommand - Export APIPlatform DTOs for the desired export format.
 * @package OwlnextFr\DtoExport\Command
 */
#[AsCommand(
    name: 'dto:export',
    description: 'Export APIPlatform DTOs for the desired export format.',
)]
class DTOExportCommand extends Command
{

    /** @var int Error: export path argument is nor a valid path, nor a writable path. */
    private const ERR_INVALID_EXPORT_PATH = 10;

    /** @var int Error: the type of export requires a project name option but it was not provided. */
    private const ERR_EXPORT_REQUIRES_PROJECT_NAME = 11;

    /** @var InputInterface|null $input Console IO input. */
    private InputInterface|null $input;

    /** @var SymfonyStyle|null $io Style utility to format command output. */
    private SymfonyStyle|null $io;

    /**
     * Constructor.
     *
     * @param DTOExporter $exporter
     */
    public function __construct(
        private DTOExporter $exporter,
    )
    {
        parent::__construct(null);
        $this->exporter = $exporter;
    }


    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(
            'export-path',
            InputArgument::REQUIRED,
            'Base path to export the DTO. (this must be an absolute path)'
        );
        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'type of export. Either "dart" or "typescript"',
            'dart'
        );
        $this->addOption(
            'project-name',
            'p',
            InputOption::VALUE_REQUIRED,
            'Project name (required for dart)',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);

        return $this->doRun();
    }

    protected function doRun(): int
    {
        $this->io->title('DTO export');

        $type = $this->input->getOption('type');
        $exportPath = $this->input->getArgument('export-path');
        $projectName = $this->input->getOption('project-name');

        $fs = new Filesystem();

        if (false === $fs->exists($exportPath)) {
            $this->io->error(sprintf('Export path %s does not exists or is not writable.', $exportPath));
            return self::ERR_INVALID_EXPORT_PATH;
        }

        if (true === is_null($projectName) &&
            true === in_array(strtolower($type), $this->exporter::REQUIRES_PROJECT_NAME)) {
            $this->io->error(sprintf('Project name option (--project-name, -p) is required for type %s', $type));
            return self::ERR_EXPORT_REQUIRES_PROJECT_NAME;
        }

        $this->io->writeln(sprintf('Type: %s', $type));
        $this->io->writeln(sprintf('Export path: %s', $exportPath));
        $this->io->writeln(sprintf('Project name: %s', $projectName));
        $this->io->newLine();

        $this->exporter
            ->setIO($this->io)
            ->export($type, $exportPath, [
                'project_name' => $projectName
            ]);

        $this->io->success(sprintf("DTOs exported to %s", $exportPath));
        return self::SUCCESS;
    }

}