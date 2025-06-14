<?php

class DoubleSlabBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(DOUBLE_SLAB, $meta, "Double Slab");
		$names = array(
			0 => "Stone",
			1 => "Sandstone",
			2 => "Wooden",
			3 => "Cobblestone",
			4 => "Brick",
			5 => "Stone Brick",
			6 => "Quartz",
			7 => "Smooth Stone",
		);
		$this->name = "Double " . $names[$this->meta & 0x07] . " Slab";
		$this->hardness = 30;
		$this->breakTime = 2;
		$this->material = Material::$stone;
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
			default => 10,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return array(
				array(SLAB, $this->meta & 0x07, 2),
			);
		}else{
			return array();
		}
	}
	
}
