<?php

abstract class ItemAxe extends ItemTool
{
	public function isAxe(){
		return true;
	}
	
	public function getLevel(){
		return match ($this->id) {
			WOODEN_AXE => 1,
			GOLDEN_AXE => 2,
			STONE_AXE => 3,
			IRON_AXE => 4,
			DIAMOND_AXE => 5,
			default => false,
		};
	}
	public function getDestroySpeed($id, $meta){
		$effMult = ItemTool::TIER_EFFICIENCY_MULTIPLIER[$this->getLevel()] ?? ItemTool::TIER_EFFICIENCY_MULTIPLIER[0];
		
		$mat = StaticBlock::getMaterial($id);
		if($mat == Material::$wood) return $effMult;
		return parent::getDestroySpeed($id, $meta);
	}
}

