<?php
namespace CTXFeed\V5\Structure;
interface StructureInterface {
	public function __construct( $config );
	public function getXMLStructure();
	public function getCSVStructure();
	public function getTSVStructure();
	public function getTXTStructure();
	public function getXLSStructure();
	public function getJSONStructure();
}

