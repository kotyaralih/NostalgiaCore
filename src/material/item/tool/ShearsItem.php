<?php

class ShearsItem extends ItemTool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(SHEARS, $meta, $count, "Shears");
	}
	
	public function mineBlock(Block $block, Player $player){
		if($block->getID() === LEAVES || $block->getID() === COBWEB){
			$this->hurtAndBreak(1, $player);
			return true;
		}
		return parent::mineBlock($block, $player);
	}
}