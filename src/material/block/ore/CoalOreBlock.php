<?php

class CoalOreBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(COAL_ORE, 0, "Coal Ore");
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
			3 => 1.15,
			2 => 0.4,
			1 => 2.25,
			default => 15,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return array(
				array(COAL, 0, 1),
			);
		}else{
			return array();
		}
	}
	
}
