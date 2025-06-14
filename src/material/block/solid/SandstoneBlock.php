<?php

class SandstoneBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(SANDSTONE, $meta, "Sandstone");
		$names = array(
			0 => "Sandstone",
			1 => "Chiseled Sandstone",
			2 => "Smooth Sandstone",
		);
		$this->name = $names[$this->meta & 0x03];		
		$this->hardness = 4;
		$this->breakTime = 0.8;
		$this->material = Material::$stone;
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}
		return match ($item->getPickaxeLevel()) {
			5 => 0.15,
			4 => 0.2,
			3 => 0.3,
			2 => 0.1,
			1 => 0.6,
			default => 4,
		};
	}

	public function getDrops(Item $item, Player $player){
		if($item->getPickaxeLevel() >= 1){
			return array(
				array(SANDSTONE, $this->meta & 0x03, 1),
			);
		}else{
			return array();
		}
	}
	
}
