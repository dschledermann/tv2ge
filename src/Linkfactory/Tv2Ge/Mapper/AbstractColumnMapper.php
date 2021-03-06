<?php

namespace Linkfactory\Tv2Ge\Mapper;

abstract class AbstractColumnMapper extends AbstractMapper {
	protected function makeColumnList($columnNames) {
		$columnList = 'uid';
		foreach ($columnNames as $columnName) {
			$columnList .= ", ExtractValue(tx_templavoila_flex, '/T3FlexForms/data/sheet/language/field[@index=\"$columnName\"]/value') AS $columnName";
		}
		return $columnList;
	}
}

