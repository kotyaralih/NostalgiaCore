<?php

class GoldOreBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(GOLD_ORE, 0, "Gold Ore");
		$this->hardness = 15;
		$this->breakTime = 3.0;
		$this->material = Material::$stone;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.6,
			4 => 0.75,
			default => 15,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 4){
			return array(
				array(GOLD_ORE, 0, 1),
			);
		}else{
			return array();
		}
	}
}
