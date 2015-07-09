<?php

namespace Linkfactory\Tv2Ge\Mapper;

abstract class AbstractMapper {
	abstract public function getDescription();

	abstract public function execute($db, $map);
}