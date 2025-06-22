<?php

class StoneBricksBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(STONE_BRICKS, $meta, "Stone Bricks");
		$names = array(
			0 => "Stone Bricks",
			1 => "Mossy Stone Bricks",
			2 => "Cracked Stone Bricks",
			3 => "Chiseled Stone Bricks",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 30;
		$this->breakTime = 1.5;
		$this->material = Material::$stone;
		$this->lightBlock = 255;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.4,
			4 => 0.5,
			3 => 0.75,
			2 => 0.25,
			1 => 1.5,
			default => 7.5,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return array(
				array(STONE_BRICKS, $this->meta & 0x03, 1),
			);
		}else{
			return array();
		}
	}
	
}
