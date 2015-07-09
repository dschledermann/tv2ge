<?php

namespace Linkfactory\Tv2Ge\Mapper;

class FCELayoutMapper {
	public function getDescription() {
		return "Assigning layout to GE's";
	}

	public function execute($db, $map) {

		$stmt_here = $db->prepare(
			"UPDATE tt_content " .
			"SET tx_gridelements_backend_layout = :belayout, " .
			"pi_flexform = tx_templavoila_flex " .
			"WHERE tx_templavoila_to = :tvlayout " .
			"AND CType = 'templavoila_pi1'");

		// Executing
		foreach ($map['fce']['types'] as $tvid => $belayout) {
			$stmt_here->execute(array(':tvlayout' => $tvid, ':belayout' => $belayout));
		}
	}
}