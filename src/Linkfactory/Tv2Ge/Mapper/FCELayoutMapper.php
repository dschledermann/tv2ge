<?php

namespace Linkfactory\Tv2Ge\Mapper;

class FCELayoutMapper {
	public function __construct($db, $map) {

		// Map for "this level"
		$stmt_here = $db->prepare(
			"UPDATE tt_content " .
			"SET tx_gridelements_backend_layout = :belayout " .
			"pi_flexform = tx_templavoila_flex, " .
			"WHERE tx_templavoila_to = :tvlayout");

		// Executing
		foreach ($map as $tvid => $belayout) {
			$map = array(':tvlayout' => $tvid, ':belayout' => $belayout);
			$stmt_here->execute($map);
			$stmt_next->execute($map);
		}
	}
}