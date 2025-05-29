<?php

abstract class ItemHoe extends ItemTool
{
	public function isHoe(){
		return true;
	}

	public function getLevel(){
		return match ($this->id) {
			WOODEN_HOE => 1,
			GOLDEN_HOE => 2,
			STONE_HOE => 3,
			IRON_HOE => 4,
			DIAMOND_HOE => 5,
			default => false,
		};
	}
}

