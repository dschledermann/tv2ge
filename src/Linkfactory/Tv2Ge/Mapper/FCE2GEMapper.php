<?php


namespace Linkfactory\Tv2Ge\Mapper;


class FCE2GEMapper extends AbstractMapper {
	public function getDescription() {
		return "Convert FCE's to GE's";
	}

	public function execute($db, $map) {
		$db->query("UPDATE tt_content SET CType = 'gridelements_pi1' WHERE CType = 'templavoila_pi1'");
	}
}

