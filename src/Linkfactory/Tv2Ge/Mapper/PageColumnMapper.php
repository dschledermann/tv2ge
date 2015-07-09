<?php

namespace Linkfactory\Tv2Ge\Mapper;
use Exception;
use PDO;

class PageColumnMapper extends AbstractColumnMapper {
	public function getDescription() {
		return "Assigning page columns to content elements";
	}

	public function execute($db, $map) {
		$this->db = $db;
		$fields = $this->makeColumnList(array_keys($map['pages']['columns']));
		
		// This statement is used to extract all the current mapping
		$stmtPages = $this->db->prepare(
			"SELECT $fields FROM pages " .
			"WHERE deleted = 0 " .
			"AND doktype = 1 " .
			"AND tx_templavoila_flex <> ''");
		
		$stmtPages->execute();

		while ($row = $stmtPages->fetch(PDO::FETCH_ASSOC)) {
 			foreach ($map['pages']['columns'] as $column => $conf) {
				$columnContent = $row[$column];

				switch ($conf['type']) {
				case 'column' :
					$this->remapElements($columnContent, $row['uid'], $conf['colPos']);
					break;
					
				case 'header' :
					$this->createHeader($columnContent, $row['uid'], $conf['colPos']);
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
	public function createHeader($content, $pid, $colPos) {
		static $stmtCheckElement;
		static $stmtCreateElement;

		$values = array(
			':pid' => $pid,
			':content' => $content,
			':colPos' => $colPos,
			':tstamp' => time());

		if (! $stmtCheckElement) {
			$stmtCheckElement = $this->db->prepare(
				"SELECT COUNT(*) FROM tt_content " .
				"WHERE pid = :pid AND colPos = :colPos AND CType = 'header' AND header = :content");
		}

		if (! $stmtCreateElement) {
			$stmtCreateElement = $this->db->prepare(
				"INSERT INTO tt_content(pid, CType, header, colPos, tstamp) VALUES (:pid, 'header', :content, :colPos, :tstamp)");
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
	 * @param   integer   $colPos          Colpos to place elements into
	 */
	public function remapElements($elementList, $pid, $colPos) {
		static $stmtCheckElement;
		static $stmtUpdateElement;
		static $stmtRefCopy;

		// Only work if there is anything on the list
		if ( ! trim($elementList)) {
			return;
		}

		// Used for check if an element has already been remapped		
		if (! $stmtCheckElement) {
			$stmtCheckElement = $this->db->prepare(
				"SELECT COUNT(*) FROM tt_content " .
				"WHERE colPos = :colPos " .
				"AND (uid = :element_uid OR records = :tt_content_element_uid) " .
				"AND pid = :pid");
		}

		// Remap an element
		if (! $stmtUpdateElement) {
			$stmtUpdateElement = $this->db->prepare(
				"UPDATE tt_content " .
				"  SET colPos = :colPos, " .
				"      sorting = :sorting " .
				"  WHERE pid = :pid " .
				"    AND uid = :element_uid");
		}

		// Create a link to an element
		// This is used for elements on different pages
		if (! $stmtRefCopy) {
			$stmtRefCopy = $this->db->prepare(
				"INSERT INTO tt_content (CType, tstamp, sorting, records, pid, colPos, sys_language_uid)" .
				"  SELECT 'shortcut', tstamp, :sorting, :tt_content_element_uid, :pid, :colPos, sys_language_uid" .
				"  FROM tt_content WHERE uid = :element_uid");
		}

		// Traverse the elements
		foreach (explode(',', $elementList) as $sorting => $uid) {

			// Check if the element was moved already
			$stmtCheckElement->execute(array(
				':colPos' => $colPos,
				':element_uid' => $uid,
				':tt_content_element_uid' => 'tt_content_' . $uid,
				':pid' => $pid));

			list($alreadyUpdated) = $stmtCheckElement->fetch(PDO::FETCH_NUM);

			// No?
			if (! $alreadyUpdated) {
				$stmtUpdateElement->execute(array(
					':colPos' => $colPos,
					':sorting' => $sorting * 100,
					':pid' => $pid,
					':element_uid' => $uid));

				// Could not moved remap?
				// We assume that pid differs
				if ($stmtUpdateElement->rowCount() == 0) {
					$stmtRefCopy->execute(array(
						":element_uid" => $uid,
						":sorting" => $sorting * 100,
						":tt_content_element_uid" => "tt_content_$uid",
						":pid" => $pid,
						":colPos" => $colPos));
				}
			}
		}
	}
}
