<?php

class GoldenLeggingsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(GOLDEN_LEGGINGS, $meta, $count, "Golden Leggings");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::GOLD;
	}
	
	public function getBaseDurability(){
		return 15;
	}
	public function getDamageReduceAmount()
	{
		return 3;
	}
}