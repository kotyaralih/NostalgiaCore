<?php

abstract class ItemTool extends Item
{
	const WOODEN_LEVEL = 1;
	const GOLD_LEVEL = 2;
	const STONE_LEVEL = 3;
	const IRON_LEVEL = 4;
	const DIAMOND_LEVEL = 5;
	
	public function isTool(){
		return true;
	}
	
	public function hurtEnemy(Entity $target, Player $attacker){
		if($this->isPickaxe() || $this->isAxe() || $this->isShovel()){
			$this->hurtAndBreak(2, $attacker);
			return;
		}
		
		parent::hurtEnemy($target, $attacker);
	}
	
	public function mineBlock(Block $block, Player $player){
		if($this->isPickaxe() || $this->isAxe() || $this->isShovel()){
			$this->hurtAndBreak(1, $player);
			return true;
		}
		
		return parent::mineBlock($block, $player);
	}
}

