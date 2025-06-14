<?php

class QuartzBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(QUARTZ_BLOCK, $meta, "Quartz Block");
		$names = array(
			0 => "Quartz Block",
			1 => "Chiseled Quartz Block",
			2 => "Quartz Pillar",
			3 => "Quartz Pillar",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->breakTime = 0.8;
		$this->hardness = 4;
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
				array(QUARTZ_BLOCK, $this->meta & 0x03, 1),
			);
		}else{
			return array();
		}
	}
}
