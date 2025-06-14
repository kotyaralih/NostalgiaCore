<?php

abstract class ItemPickaxe extends ItemTool
{
	
	public function isTool(){
		return true;
	}

	public function isPickaxe(){
		return true;
	}

	public function getLevel(){
		return match ($this->id) {
			WOODEN_PICKAXE => 1,
			GOLDEN_PICKAXE => 2,
			STONE_PICKAXE => 3,
			IRON_PICKAXE => 4,
			DIAMOND_PICKAXE => 5,
			default => false,
		};
	}
	
	public function canDestroySpecial($id, $meta){
		$level = ItemTool::TIER_MINING_LEVEL[$this->getLevel()] ?? 0;
		$mat = StaticBlock::getMaterial($id);
		if($id == OBSIDIAN || $id == GLOWING_OBSIDIAN) return $level == 3;
		if($id == DIAMOND_BLOCK || $id == DIAMOND_ORE || $id == GOLD_BLOCK || $id == GOLD_ORE || $id == REDSTONE_ORE || $id == GLOWING_REDSTONE_ORE) return $level > 1;
		if($id == IRON_BLOCK || $id == IRON_ORE || $id == LAPIS_BLOCK || $id == LAPIS_ORE) return $level > 0;
		if($mat == Material::$stone) return true;
		return $mat == Material::$metal;
		
	}
	
	public function getDestroySpeed($id, $meta){
		$effMult = ItemTool::TIER_EFFICIENCY_MULTIPLIER[$this->getLevel()] ?? ItemTool::TIER_EFFICIENCY_MULTIPLIER[0];
		
		$mat = StaticBlock::getMaterial($id);
		if($mat == Material::$metal || $mat == Material::$stone) return $effMult;
		return parent::getDestroySpeed($id, $meta);
	}
}

