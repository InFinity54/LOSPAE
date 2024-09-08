<?php
namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand('app:clean:generateddocs', 'Permanently delete all generated documents from user bulk import process from disk.')]
class GeneratedDocumentsCleaner extends Command
{
    private $appKernel;

    public function __construct(KernelInterface $appKernel) {
        $this->appKernel = $appKernel;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $this->appKernel->getProjectDir() . "/var";
        $count = 0;

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');
        }

        foreach (array_diff(scandir($directory), array('..', '.', 'cache', 'log')) as $file) {
            $filePath = $directory."/".$file;
            $fileModifiedTime = filemtime($filePath);

            if (!$input->getOption('dry-run') && file_exists($filePath) && time() - $fileModifiedTime > 86400) {
                $count++;
                unlink($filePath);
                $io->info(sprintf('File deleted: %s', $file));
            }
        }

        $io->success(sprintf('Deleted %d generated documents from disk.', $count));

        return Command::SUCCESS;
    }
}