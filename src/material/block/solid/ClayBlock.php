<?php

class ClayBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(CLAY_BLOCK, 0, "Clay Block");
		$this->hardness = 3;
		$this->breakTime = 0.6;
		$this->material = Material::$clay;
	}

	public function getDrops(Item $item, Player $player){
		return array(
			array(CLAY, 0, 4),
		);
	}
}