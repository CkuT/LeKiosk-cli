<?php

namespace Colfej\LeKioskCLI\Command\Download;

use Colfej\LeKioskCLI\Api\Reader;
use Colfej\LeKioskCLI\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadManualCommand extends Command {

    protected function configure() {

        $this->setName('download:manual');
        $this->setDescription('Download issue from publication ID and issue ID');

        $this->addArgument('id_publication', InputArgument::REQUIRED, 'Publication ID');
        $this->addArgument('id_issue', InputArgument::REQUIRED, 'Issue ID');

        $this->addOption('clean', 'd', InputOption::VALUE_NONE, 'Delete original data.');

        $this->addOption('pdf', 'p', InputOption::VALUE_NONE, 'Convert to PDF.');
        $this->addOption('zip', 'z', InputOption::VALUE_NONE, 'Convert to ZIP.');

        $this->addOption('cover', 'c', InputOption::VALUE_NONE, 'Get cover image.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        self::download($input, $output, $input->getArgument('id_publication'), $input->getArgument('id_issue'));

    }

    public static function download(InputInterface $input, OutputInterface $output, $idPublication, $idIssue) {

        $output->writeln('Download pages ...');

        $info = Reader::download($idPublication, $idIssue, $output);

        if ($input->getOption('pdf')) {

            $output->writeln('Create PDF ...');

            $files = array();

            foreach (scandir($info['path']) as $file) {
                if (is_file($info['path'].$file)) {
                    $files[] = $info['path'].$file;
                }
            }
            
            $pdf = new \Imagick($files);
            $pdf->setImageFormat('pdf');
            $pdf->writeImages('.'.DIRECTORY_SEPARATOR.$info['sanitize'].'.pdf', true);

        }

        if ($input->getOption('zip')) {

            $output->writeln('Create ZIP ...');

            $zip = new \ZipArchive();
            $zip->open('.'.DIRECTORY_SEPARATOR.$info['sanitize'].'.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach (scandir($info['path']) as $file) {
                if (is_file($info['path'].$file)) {
                    $zip->addFile($info['path'].$file, $file);
                }
            }

            $zip->close();

        }

        if ($input->getOption('cover')) {

            $output->writeln('Copy cover ...');

            copy($info['path'].'001.jpg', '.'.DIRECTORY_SEPARATOR.$info['sanitize'].'.jpg');

        }

        if ($input->getOption('clean')) {

            $output->writeln('Clean ...');

            Helper::deleteDirectory($info['path']);

        } else {
            rename($info['path'], '.'.DIRECTORY_SEPARATOR.$info['sanitize'].DIRECTORY_SEPARATOR);
        }

    }

}