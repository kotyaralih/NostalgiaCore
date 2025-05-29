<?php

class PumpkinBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(PUMPKIN, $meta, "Pumpkin");
		$this->hardness = 5;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 1,
			1 => 2,
			2 => 3,
			3 => 0,
		);
		$this->meta = $faces[$player->entity->getDirection()];
		$this->level->setBlock($block, $this, true, false, true);
		return true;
	}
	
	public function getDrops(Item $item, Player $player){
		return [
			[PUMPKIN, 0, 1]
		];
	}
}