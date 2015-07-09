<?php

namespace Linkfactory\Tv2Ge\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use PDO;

class Tv2GeCommand extends Command {
	protected function configure() {
		$this
			->setName('tv2ge:remap')
			->setDescription("Remap TemplaVoila settings to standard columns and gridelements")
			->addArgument(
				'mapping-json',
				InputArgument::REQUIRED,
				'A map file for the site')
			->addArgument(
				'dsn',
				InputArgument::REQUIRED,
				'DSN for the database')
			->addArgument(
				'username',
				InputArgument::OPTIONAL,
				'Database username')
			->addArgument(
				'password',
				InputArgument::OPTIONAL,
				'Database password')
			;
	}

	protected function execute(InputInterface $in, OutputInterface $out) {
		$mapping = json_decode(file_get_contents($in->getArgument('mapping-json')), true);

		if ( ! is_array($mapping)) {
			throw new Exception("Mapping data could not be parsed");
		}

		$db = new PDO($in->getArgument('dsn'), $in->getArgument('username'), $in->getArgument('password'));
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$conversions = array(
			new \Linkfactory\Tv2Ge\Mapper\GEFieldCreateMapper,
			new \Linkfactory\Tv2Ge\Mapper\PageLayoutMapper,
			new \Linkfactory\Tv2Ge\Mapper\PageColumnMapper,
			new \Linkfactory\Tv2Ge\Mapper\FCELayoutMapper,
			new \Linkfactory\Tv2Ge\Mapper\FCEColumnMapper,
			new \Linkfactory\Tv2Ge\Mapper\FCE2GEMapper,
		);

		foreach ($conversions as $conversion) {
			$out->writeln("<info>" . $conversion->getDescription() . "</info>");
			$conversion->execute($db, $mapping);
		}
	}
}