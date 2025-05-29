<?php

abstract class ItemSword extends ItemTool
{

	public function isSword(){
		return true;
	}

	public function getLevel(){
		return match ($this->id) {
			WOODEN_SWORD => 1,
			GOLDEN_SWORD => 2,
			STONE_SWORD => 3,
			IRON_SWORD => 4,
			DIAMOND_SWORD => 5,
			default => false,
		};
	}
	
	public function hurtEnemy(Entity $target, Player $attacker){
		$this->hurtAndBreak(1, $attacker);
	}
	
	
	public function mineBlock(Block $block, Player $player){
		$this->hurtAndBreak(2, $player);
		return true;
	}
}

