<?php

class IronBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(IRON_BLOCK, 0, "Iron Block");
		$this->hardness = 30;
		$this->breakTime = 5;
		$this->material = Material::$metal;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.95,
			4 => 1.25,
			3 => 1.9,
			default => 25,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 3){
			return array(
				array(IRON_BLOCK, 0, 1),
			);
		}else{
			return array();
		}
	}
}
