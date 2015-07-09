<?php

namespace Linkfactory\Tv2Ge\Mapper;

class GEFieldCreateMapper extends AbstractMapper {
	public function getDescription() {
		return "Creating gridelements fields";
	}

	public function execute($db, $map) {
		$db->query("ALTER TABLE tt_content MODIFY colPos tinyint(6) NOT NULL DEFAULT 0");

		$fields = array(
			'tx_gridelements_backend_layout' => 'varchar(256) NOT NULL',
			'tx_gridelements_children' => 'int(11) NOT NULL DEFAULT 0',
			'tx_gridelements_container' => 'int(11) NOT NULL DEFAULT 0',
			'tx_gridelements_columns' => 'int(11) NOT NULL DEFAULT 0');

		foreach ($fields as $field => $definition) {
			$stmtCheck = $db->query("SHOW COLUMNS FROM tt_content LIKE '$field'");
			if ($stmtCheck->rowCount() == 0) {
				$stmt = $db->query("ALTER TABLE tt_content ADD $field $definition");
			}
		}

	}
}

