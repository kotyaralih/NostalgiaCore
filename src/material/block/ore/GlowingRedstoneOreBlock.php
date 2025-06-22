<?php

class GlowingRedstoneOreBlock extends SolidBlock implements LightingBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(GLOWING_REDSTONE_ORE, 0, "Glowing Redstone Ore");
		$this->hardness = 15;
		$this->breakTime = 3;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
		$this->lightEmission = 9;
	}
	
	public static function onRandomTick(Level $level, $x, $y, $z){
		$level->fastSetBlockUpdate($x, $y, $z, REDSTONE_ORE, 0);
	}
	
	public function getMaxLightValue(){
		return 9;
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
				array(REDSTONE_DUST, 0, mt_rand(4, 5)),
			);
		}else{
			return array();
		}
	}
	
}
