<?php

class ObsidianBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(OBSIDIAN, 0, "Obsidian");
		$this->hardness = 6000;
		$this->breakTime = 10;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		if($item->getPickaxeLevel() >= 5){
			return 2.15;
		}else{
			return 250;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 5){
			return [
				[OBSIDIAN, 0, 1]
			];
		}else{
			return [];
		}
	}
}
