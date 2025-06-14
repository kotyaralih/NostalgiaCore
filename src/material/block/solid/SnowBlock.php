<?php

class SnowBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(SNOW_BLOCK, 0, "Snow Block");
		$this->hardness = 1;
		$this->breakTime = 0.2;
		$this->material = Material::$snow;
	}

	public function getDrops(Item $item, Player $player){
		if($item->isShovel() !== false){
			return [
				[SNOWBALL, 0, 4],
			];
		}
		return [];
	}
	
}