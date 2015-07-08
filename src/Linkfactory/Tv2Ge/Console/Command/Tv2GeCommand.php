<?php

namespace Linkfactory\Tv2Ge\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Linkfactory\Tv2Ge\Mapper\PageLayoutMapper;
use Linkfactory\Tv2Ge\Mapper\PageColumnMapper;
use Linkfactory\Tv2Ge\Mapper\FCELayoutMapper;
use Linkfactory\Tv2Ge\Mapper\FCEColumnMapper;

class Tv2GeCommand extends Command {
	public $db;

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
		$this->db = new PDO($in->getArgument('dsn'), $in->getArgument('username'), $in->getArgument('password'));

		new PageLayoutMapper($this->db, $mapping['pages']['types']);
		new PageColumnMapper($this->db, $mapping['pages']['columns']);
		new FCELayoutMapper($this->db, $mapping['fce']['types']);
		new FCEColumnMapper($this->db, $mapping['fce']['columns']);
	}
}