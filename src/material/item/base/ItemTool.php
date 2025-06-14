<?php

abstract class ItemTool extends Item
{
	const WOODEN_LEVEL = 1;
	const GOLD_LEVEL = 2;
	const STONE_LEVEL = 3;
	const IRON_LEVEL = 4;
	const DIAMOND_LEVEL = 5;
	
	public const TIER_MINING_LEVEL = [
		1 => 0, //wooden
		2 => 0, //golden
		3 => 1, //stone
		4 => 2, //iron
		5 => 3, //diamond
	];
	public const TIER_EFFICIENCY_MULTIPLIER = [
		1 => 2.0, //wooden
		2 => 12.0, //golden
		3 => 4.0, //stone
		4 => 6.0, //iron
		5 => 8.0, //diamond
	];
	
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

