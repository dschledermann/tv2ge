<?php

namespace Linkfactory\Tv2Ge\Mapper;
use Exception;

class FCEColumnMapper extends AbstractColumnMapper {
	public function __construct($db, $map) {
		$this->db = $db;
		$fields = $this->makeColumnList(array_keys($map));

		// This statement is used to extract all the current mapping
		$stmtFCEs = $this->db->prepare(
			"SELECT pid, $fields FROM tt_content " .
			"WHERE deleted = 0 " .
			"AND CType = 'templavoila_pi1' " .
			"AND tx_templavoila_flex <> ''");

		$stmtFCEs->execute();

		while ($row = $stmtFCEs->fetch(PDO::FETCH_ASSOC)) {
 			foreach ($map as $column => $conf) {
				$columnContent = $row[$column];

				switch ($conf['type']) {
				case 'column' :
					$this->remapElements($columnContent, $row['pid'], $row['uid'], $conf['colPos']);
					break;
					
				case 'header' :
					$this->createHeader($columnContent, $row['pid'], $row['uid'], $conf['colPos']);
					break;

				default:
					throw new Exception("Unsupported type: $conf[type]");
				}
			}
		}
	}

	/**
	 * Create a header from the contents of a flex-field
	 * @param   string    $content         The content of the header element
	 * @param   integer   $pid             Page this is taking place on
	 * @param   integer   $colPos          Colpos to place elements into
	 */
	public function createHeader($content, $pid, $uid, $colPos) {
		static $stmtCheckElement;
		static $stmtCreateElement;

		$values = array(
			':content' => $content,
			':pid' => $pid,
			':tstamp' => time(),
			':tx_gridelements_columns' => $colPos,
			':tx_gridelements_container' => $uid);

		if (! $stmtCheckElement) {
			$stmtCheckElement = $this->db->prepare(
				"SELECT COUNT(*) FROM tt_content " .
				"WHERE colPos = -1 AND CType = 'header' AND header = :content, tx_gridelements_container = :tx_gridelements_container");
		}

		if (! $stmtCreateElement) {
			$stmtCreateElement = $this->db->prepare(
				"INSERT INTO tt_content(pid, tx_gridelements_container, tx_gridelements_columns, CType, header, colPos, tstamp) " .
				"VALUES (:pid, :tx_gridelements_container, :tx_gridelements_columns, 'header', :content, -1, :tstamp)");
		}

		// Check if the header is in place
		$stmtCheckElement->execute($values);
		list ($alreadyInserted) = $stmtCheckElement->fetch(PDO::FETCH_NUM);

		if (! $alreadyInserted) {
			$stmtCreateElement->execute($values);
		}
	}

	/**
	 * Remap all elements on to a proper column on a certain page
	 * @param   string    $elementList     Comma seperated list of tt_content uid's
	 * @param   integer   $pid             Page this is taking place on
	 * @param   integer   $container_uid   The FCE this is taking place on
	 * @param   integer   $colPos          Colpos to place elements into
	 */
	public function remapElements($elementList, $pid, $container_uid, $colPos) {
		static $stmtCheckElement;
		static $stmtUpdateElement;
		static $stmtRefCopy;

		// Used for check if an element has already been remapped		
		if (! $stmtCheckElement) {
			$stmpCheckElement = $this->db->prepare(
				"SELECT COUNT(*) FROM tt_content " .
				"WHERE colPos = -1 " .
				"AND (uid = :element_uid OR records = :tt_content_element_uid) " .
				"AND tx_gridelements_container = :tx_gridelements_container ");
		}

		// Remap an element
		if (! $stmtUpdateElement) {
			$stmtUpdateElement = $this->db->prepare(
				"UPDATE tt_content " .
				"  SET colPos = -1 " .
				"      tx_gridelements_container = :tx_gridelements_container " .
				"      sorting = :sorting " .
				"      tx_gridelements_columns = :tx_gridelements_columns" .
				"  WHERE pid = :pid " .
				"    AND uid = :element_uid");
		}

		// Create a link to an element
		// This is used for elements on different pages
		if (! $stmtRefCopy) {
			$stmtRefCopy = $this->db->prepare(
				"INSERT INTO tt_content (CType, tstamp, sorting, records, pid, colPos, sys_language_uid, tx_gridelements_columns, tx_gridelements_container)" .
				"  SELECT 'shortcut', tstamp, :sorting, :tt_content_element_uid, :pid, :colPos, sys_language_uid, :tx_gridelements_columns, :tx_gridelements_container" .
				"  FROM tt_content WHERE uid = :element_uid");
		}

		// Traverse the elements
		foreach (explode(',', $elementList) as $sorting => $uid) {
			$values = array(
				":element_uid" => $uid,
				":sorting" => $sorting * 100,
				":tt_content_element_uid" => "tt_content_$uid",
				":tx_gridelements_container" => $container_uid,
				":pid" => $pid,
				":tx_gridelements_columns" => $colPos);

			// Check if the element was moved already
			$stmtCheckElement->execute($values);
			list($alreadyUpdated) = $stmtCheckElement->fetch(PDO::FETCH_NUM);

			// No?
			if (! $alreadyUpdated) {
				$stmtUpdateElement->execute($values);

				// Could not moved remap?
				// We assume that pid differs
				if ($stmtUpdateElement->rowCount() == 0) {
					$stmtRefCopy->execute($values);
				}
			}
		}
	}
}
