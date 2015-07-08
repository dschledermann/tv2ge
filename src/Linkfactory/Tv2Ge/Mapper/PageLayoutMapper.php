<?php

namespace Linkfactory\Tv2Ge\Mapper;

class PageLayoutMapper {
	public function __construct($db, $map) {

		// Map for "this level"
		$stmt_here = $db->prepare(
			"UPDATE pages " .
			"SET backend_layout = :belayout " .
			"WHERE tx_templavoila_to = :tvlayout");

		// Map for "next level"
		$stmt_next = $db->prepare(
			"UPDATE pages " .
			"SET backend_layout_next_level = :belayout " .
			"WHERE tx_templavoila_next_to = :tvlayout");

		// Executing
		foreach ($map as $tvid => $belayout) {
			$map = array(':tvlayout' => $tvid, ':belayout' => $belayout);
			$stmt_here->execute($map);
			$stmt_next->execute($map);
		}
	}
}