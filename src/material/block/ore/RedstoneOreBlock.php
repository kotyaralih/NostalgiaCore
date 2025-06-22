<?php

class RedstoneOreBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(REDSTONE_ORE, 0, "Redstone Ore");
		$this->hardness = 15;
		$this->breakTime = 3;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
	}
	
	public static function interact(Level $level, $x, $y, $z, Player $player){
		$level->fastSetBlockUpdate($x, $y, $z, GLOWING_REDSTONE_ORE, 0);
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