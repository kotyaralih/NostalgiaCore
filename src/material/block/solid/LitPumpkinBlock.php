<?php

class LitPumpkinBlock extends SolidBlock implements LightingBlock{
	public static $blockID;
	protected static $faces = [
		0 => 1,
		1 => 2,
		2 => 3,
		3 => 0,
	];
	
	
	public function __construct($meta = 0){
		parent::__construct(LIT_PUMPKIN, $meta, "Jack o'Lantern");
		$this->hardness = 5;
		$this->breakTime = 1.0;
		$this->material = Material::$vegetable;
	}
	
	public function getMaxLightValue(){
		return 15;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$id = $this->level->level->getBlockID($this->x, $this->y, $this->z);
		if(($id == 0 || $id == SNOW_LAYER) && $this->level->isTopSolidBlocking($this->x, $this->y - 1, $this->z)){
			$this->meta = self::$faces[$player->entity->getDirection()];
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		return [
			[JACK_O_LANTERN, 0, 1]
		];
	}
	
	
}