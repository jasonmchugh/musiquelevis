<?php

namespace CTXFeed\V5\Structure;


/**
 * Class Structure
 *
 * @package    CTXFeed\V5\Structure
 * @subpackage CTXFeed\V5\Structure
 */
class Structure {
	private $structure;

	public function __construct( StructureInterface $structure ) {
		$this->structure = $structure;
	}

	public function getXMLStructure() {
		return $this->structure->getXMLStructure();
	}

	public function getCSVStructure() {
		return $this->structure->getCSVStructure();
	}

	public function getTSVStructure() {
		return $this->structure->getCSVStructure();
	}

	public function getTXTStructure() {
		return $this->structure->getCSVStructure();
	}

	public function getXLSStructure() {
		return $this->structure->getCSVStructure();
	}

	public function getJSONStructure() {
		return $this->structure->getCSVStructure();
	}
}
