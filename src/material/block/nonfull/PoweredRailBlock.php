<?php
require_once("RailBaseBlock.php");
class PoweredRailBlock extends RailBaseBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(POWERED_RAIL, 0, "PoweredRailBlock");
		$this->isFullBlock = false;		
		$this->isSolid = false;
		$this->hardness = 3.5;
		$this->breakTime = 0.7;
		$this->material = Material::$decoration;
	}
	
}