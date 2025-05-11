<?php

class DoubleWoodSlabBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(DOUBLE_WOOD_SLAB, $meta, "Double Wooden Slab");
		$names = array(
			0 => "Oak",
			1 => "Spruce",
			2 => "Birch",
			3 => "Jungle",
		);
		$this->name = "Double " . $names[$this->meta & 0x07] . " Wooden Slab";
		$this->hardness = 15;
	}

	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		if(!$item->isAxe()) return 3;
		
		return match ($item->getLevel()) {
			5 => 0.4,
			4 => 0.5,
			3 => 0.75,
			2 => 0.25,
			1 => 1.5,
			default => 3,
		};
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(WOOD_SLAB, $this->meta & 0x07, 2),
		);
	}
	
}
