<?php

class FlintSteelItem extends ItemTool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(FLINT_STEEL, $meta, $count, "Flint and Steel");
		$this->isActivable = true;
		$this->maxStackSize = 1;
	}
	
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$this->hurtAndBreak(1, $player);
		if($block->getID() === AIR && $target->isSolid){
			$level->fastSetBlockUpdate($block->x, $block->y, $block->z, FIRE, 0, true);
			return true;
		}
		return false;
	}
}