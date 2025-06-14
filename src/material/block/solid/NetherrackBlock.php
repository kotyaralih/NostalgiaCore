<?php

class NetherrackBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(NETHERRACK, 0, "Netherrack");
		$this->hardness = 2;
		$this->breakTime = 0.4;
		$this->material = Material::$stone;
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.1,
			4 => 0.1,
			3 => 0.15,
			2 => 0.05,
			1 => 0.3,
			default => 2,
		};
	}

	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return array(
				array(NETHERRACK, 0, 1),
			);
		}else{
			return array();
		}
	}
}
