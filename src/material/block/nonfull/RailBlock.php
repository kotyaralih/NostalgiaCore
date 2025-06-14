<?php
require_once("RailBaseBlock.php");
class RailBlock extends RailBaseBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(RAIL, $meta, "Rail");
		$this->hardness = 0.7*5;
		$this->breakTime = 0.7;
		$this->isFullBlock = false;		
		$this->isSolid = false;
		$this->material = Material::$decoration;
	}
	
	public function updateState(){
		$logic = (new RailLogic($this));
		if($logic->countPotentialConnections() == 3){
			$logic->place(false, false);
		}
	}
	
}