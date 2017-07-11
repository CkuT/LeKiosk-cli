<?php

namespace Colfej\LeKioskCLI\Command;

use Humbug\SelfUpdate\Updater;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command {

    const LAST_PHAR_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/lekiosk-cli.phar';
    const LAST_VERSION_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/version';

    protected $output;
    protected $formatter;

    protected $updater;

    protected function configure() {

        $this->setName('self-update');
        $this->setDescription('Update to most recent build.');

        $this->addOption('rollback', 'r', InputOption::VALUE_NONE, 'Rollback to previous version if available on filesystem.');
        $this->addOption('check', 'c', InputOption::VALUE_NONE, 'Check if update is available.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->output = $output;
        $this->formatter = $this->getHelper('formatter');

        $this->updater = new Updater(null, false);

        $this->updater->getStrategy()->setPharUrl(self::LAST_PHAR_URL);
        $this->updater->getStrategy()->setVersionUrl(self::LAST_VERSION_URL);
        $this->updater->setBackupPath(sys_get_temp_dir());

        if ($input->getOption('rollback')) {
            $this->rollback();
            return;
        }

        if ($input->getOption('check')) {
            $this->check();
            return;
        }

        $this->update();
        
    }

    protected function rollback() {

        try {

            $result = $this->updater->rollback();

            if ($result) {
                $this->output->writeln('<fg=green>LeKiosk-cli has been rolled back.</fg=green>');
            } else {
                $this->output->writeln('<fg=red>Rollback failed for reasons unknown ...</fg=red>');
            }

        } catch (\Exception $e) {
            $error = array('Error ...', $e->getMessage());
            $block = $this->formatter->formatBlock($error, 'error');
            $this->output->writeln($block);
        }

    }

    protected function check() {

        try {

            if ($this->updater->hasUpdate()) {
                $this->output->writeln('There is a new build available remotely !');
            } elseif (!$this->updater->getNewVersion()) {
                $this->output->writeln('There are no builds available.');
            } else {
                $this->output->writeln('You have the last build installed.');
            }

        } catch (\Exception $e) {
            $error = array('Error ...', $e->getMessage());
            $block = $this->formatter->formatBlock($error, 'error');
            $this->output->writeln($block);
        }

    }

    protected function update() {

        $this->output->writeln('Updating ...'.PHP_EOL);

        try {

            $result = $this->updater->update();
        
            if ($result) {
                $this->output->writeln('<fg=green>LeKiosk-cli has been updated.</fg=green>');
            } else {
                $this->output->writeln('<fg=green>LeKiosk-cli is currently up to date.</fg=green>');
            }

        } catch (\Exception $e) {
            $error = array('Error ...', $e->getMessage());
            $block = $this->formatter->formatBlock($error, 'error');
            $this->output->writeln($block);
        }

    }

}