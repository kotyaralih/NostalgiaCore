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
	
	public function canDestroySpecial($id, $meta){
		return $id == COBWEB;
	}
	public function getDestroySpeed($id, $meta){
		if($id == COBWEB || $id == LEAVES) return 15;
		if($id == WOOL) return 5;
		return parent::getDestroySpeed($id, $meta);
	}
}