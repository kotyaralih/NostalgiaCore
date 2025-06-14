<?php

class IronDoorBlock extends DoorBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(IRON_DOOR_BLOCK, $meta, "Iron Door Block");
		//$this->isActivable = true;
		$this->hardness = 25;
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
			2 => 0.65,
			1 => 3.75,
			default => 25,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return array(
				array(IRON_DOOR, 0, 1),
			);
		}else{
			return array();
		}
	}
}
