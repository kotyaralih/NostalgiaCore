<?php

class LapisBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(LAPIS_BLOCK, 0, "Lapis Block");
		$this->hardness = 15;
		$this->breakTime = 3.0;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.6,
			4 => 0.75,
			3 => 1.15,
			default => 15,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 3){
			return array(
				array(LAPIS_BLOCK, 0, 1),
			);
		}else{
			return array();
		}
	}

}
