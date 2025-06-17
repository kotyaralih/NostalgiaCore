<?php

class ItemShovel extends ItemTool
{
	public function isShovel(){
		return true;
	}

	public function getLevel(){
		return match ($this->id) {
			WOODEN_SHOVEL => 1,
			GOLDEN_SHOVEL => 2,
			STONE_SHOVEL => 3,
			IRON_SHOVEL => 4,
			DIAMOND_SHOVEL => 5,
			default => false,
		};
	}
	
	public function canDestroySpecial($id, $meta){
		return $id == SNOW_LAYER || $id == SNOW_BLOCK;
	}
	public function getDestroySpeed($id, $meta){
		$effMult = ItemTool::TIER_EFFICIENCY_MULTIPLIER[$this->getLevel()] ?? ItemTool::TIER_EFFICIENCY_MULTIPLIER[0];
		
		if($id == GRASS || $id == DIRT || $id == SAND || $id == GRAVEL || $id == SNOW_LAYER || $id == SNOW || $id == CLAY_BLOCK || $id == FARMLAND) return $effMult;
		return parent::getDestroySpeed($id, $meta);
	}
}

