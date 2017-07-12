<?php

namespace Colfej\LeKioskCLI\Command;

use Humbug\SelfUpdate\Updater;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command {

    const LAST_PHAR_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/lekiosk-cli.phar';
    const LAST_VERSION_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/version';

    protected $input;
    protected $output;

    protected $formatter;

    protected $updater;

    protected function configure() {

        $this->setName('self-update');
        $this->setDescription('Update LeKiosk-cli to most recent version');

        $this->addOption('rollback', 'r', InputOption::VALUE_NONE, 'Rollback to previous version, if available on filesystem.');
        $this->addOption('check', 'c', InputOption::VALUE_NONE, 'Check if an update is available.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->input = $input;
        $this->output = $output;

        $this->formatter = $this->getHelper('formatter');

        $this->updater = new Updater(null, false);

        $this->output->writeln('Phar URL : '.self::LAST_PHAR_URL, OutputInterface::VERBOSITY_DEBUG);
        $this->updater->getStrategy()->setPharUrl(self::LAST_PHAR_URL);

        $this->output->writeln('Version URL : '.self::LAST_VERSION_URL, OutputInterface::VERBOSITY_DEBUG);
        $this->updater->getStrategy()->setVersionUrl(self::LAST_VERSION_URL);

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lekiosk-cli_backup.phar';
        $this->output->writeln('Roolback path : '.$path, OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->updater->setBackupPath($path);

        if ($this->input->getOption('rollback')) {
            $this->rollback();
            return;
        }

        if ($this->input->getOption('check')) {
            $this->check();
            return;
        }

        $this->update();
        
    }

    protected function rollback() {
        
        $this->output->writeln('Rollback ...', OutputInterface::VERBOSITY_VERBOSE);

        $result = $this->updater->rollback();

        if ($result) {
            $this->output->writeln('<fg=green>LeKiosk-cli has been rolled back.</fg=green>');
        } else {
            $this->output->writeln('<fg=red>Rollback failed for reasons unknown ...</fg=red>');
        }

    }

    protected function check() {

        $this->output->writeln('Check ...', OutputInterface::VERBOSITY_VERBOSE);

        if ($this->updater->hasUpdate()) {
            $this->output->writeln('<fg=green>There is a new build available remotely !</fg=green>');
        } elseif (!$this->updater->getNewVersion()) {
            $this->output->writeln('<fg=green>There are no builds available.</fg=green>');
        } else {
            $this->output->writeln('<fg=green>You have the last build installed.</fg=green>');
        }

    }

    protected function update() {

        $this->output->writeln('<fg=yellow>Updating ...</fg=yellow>');

        $result = $this->updater->update();
    
        if ($result) {
            $this->output->writeln('<fg=green>LeKiosk-cli has been updated.</fg=green>');
        } else {
            $this->output->writeln('<fg=green>LeKiosk-cli is currently up to date.</fg=green>');
        }

    }

}