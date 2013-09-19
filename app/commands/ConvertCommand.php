<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console;


class ConvertCommand extends Console\Command\Command
{

	protected function configure()
	{
		$this
			->setName('convert')
			->setDescription('Convert MARC21 to MODS format.')
			->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'File path(s) of SQL to be executed.', getcwd())
			->addOption('list', 'l', InputOption::VALUE_NONE, 'List filenames of generated MODS files.')
			->addOption('full-paths', 'f', InputOption::VALUE_NONE, 'Displays relative paths (listing generated MODS files only).')
			->setHelp(<<<EOT
Discovers URLs in directory and creates MODS where AlephId is available
EOT
			);
	}


	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$list = $input->getOption('list');
		$fullPaths = $input->getOption('full-paths');
		if (!$list && $fullPaths) {
			$output->writeln('<error>Option full-paths is only available with list option enabled.</error>');

			return 1;
		}

		$directory = realpath($input->getOption('directory'));
		if (!is_dir($directory)) {
			$output->writeln('<error>Invalid directory <info>\'' . $input->getOption('directory') . '\'</info>.</error>');
			return 1;
		}

		if ($list) {
			$output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
		}

		$process = $this->getHelper('container')->getContainer()->getService('conversionProcess');
		/** @var ConversionProcess $process */

		$output->write('<info>Discovering URLs...</info>');
		$count = $process->discoverUrls($directory);
		$output->writeln('<info><fg=green>' . $count . '</fg=green> found</info>');

		$progress = $this->getHelper('progress');
		/** @var Symfony\Component\Console\Helper\ProgressHelper $progress */
		$output->writeln('Matching urls with AlephIDs...');
		$progress->setRedrawFrequency(max(1, pow(10, strlen($count) - 2)) /* two grades lower then total count is */);
		$progress->start($output, $count);
		$count = $process->resolveUrls(function () use ($progress) {
			$progress->advance(1);
		});
		$progress->finish();
		$output->writeln('<info>Matched <fg=green>' . $count . '</fg=green> AlephIDs to urls</info>');

		$output->writeln('<info>Retrieving MARC and generating MODS...</info>');
		$verbosity = $output->getVerbosity();
		if (!$list) {
			$progress->start($output, $count);
		} else {
			$output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
		}
		$len = strlen($directory);
		$progress->setRedrawFrequency(max(1, pow(10, strlen($count) - 2)) /* two grades lower then total count is */);
		$process->convert($list
			? function ($filename) use ($len, $output, $fullPaths) {
				$output->writeln($fullPaths ? $filename : ltrim(substr($filename, $len), DIRECTORY_SEPARATOR));
			}
			: function () use ($progress) {
				$progress->advance(1);
			}
		);
		if (!$list) {
			$progress->finish();
		}
		$output->setVerbosity($verbosity);
		$output->writeln(sprintf('<info>Memory peak %.2f MB</info>', memory_get_peak_usage(TRUE) / 1024 / 1024));
	}

}
