<?php

class GoldenChestplateItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(GOLDEN_CHESTPLATE, $meta, $count, "Golden Chestplate");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::GOLD;
	}
	
	public function getBaseDurability(){
		return 16;
	}
	public function getDamageReduceAmount()
	{
		return 5;
	}
}