<?php

namespace Colfej\LeKioskCLI\Command;

use Humbug\SelfUpdate\Updater;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

    const LAST_PHAR_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/lekiosk-cli.phar';
    const LAST_VERSION_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/version';

    protected $output;
    protected $version;
    protected $updater;

    protected function configure() {

        $this->setName('update');
        $this->setDescription('Update to most recent build.');

        $this->addOption('rollback', 'r', InputOption::VALUE_NONE, 'Rollback to previous version if available on filesystem.');
        $this->addOption('check', 'c', InputOption::VALUE_NONE, 'Check if update is available.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->output = $output;

        $this->version = $this->getApplication()->getVersion();

        $this->updater = new Updater(null, false);

        $this->updater->getStrategy()->setPharUrl(self::LAST_PHAR_URL);
        $this->updater->getStrategy()->setVersionUrl(self::LAST_VERSION_URL);

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
                $this->output->writeln('<fg=green>LeKiosk-cli has been rolled back to prior version.</fg=green>');
            } else {
                $this->output->writeln('<fg=red>Rollback failed for reasons unknown ...</fg=red>');
            }

        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error : <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }

    }

    protected function check() {

        $this->output->writeln(sprintf('Your current local build version is : <options=bold>%s</options=bold>', $this->version));

        try {

            if ($this->updater->hasUpdate()) {
                $this->output->writeln(sprintf('The current build available remotely is : <options=bold>%s</options=bold>', $this->updater->getNewVersion()));
            } elseif (!$this->updater->getNewVersion()) {
                $this->output->writeln('There are no new builds available.');
            } else {
                $this->output->writeln('You have the last build installed.');
            }

        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error : <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }

    }

    protected function update() {

        $this->output->writeln('Updating ...'.PHP_EOL);

        try {

            $result = $this->updater->update();

            $newVersion = $this->updater->getNewVersion();
            $oldVersion = $this->updater->getOldVersion();
        
            if ($result) {

                $this->output->writeln('<fg=green>LeKiosk-cli has been updated.</fg=green>');
                $this->output->writeln(sprintf('<fg=green>Current version is :</fg=green> <options=bold>%s</options=bold>.', $newVersion));
                $this->output->writeln(sprintf('<fg=green>Previous version was :</fg=green> <options=bold>%s</options=bold>.', $oldVersion));

            } else {

                $this->output->writeln('<fg=green>LeKiosk-cli is currently up to date.</fg=green>');
                $this->output->writeln(sprintf('<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.', $oldVersion));
            }

        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error : <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }

    }

}