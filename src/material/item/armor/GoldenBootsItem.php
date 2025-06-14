<?php

class GoldenBootsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(GOLDEN_BOOTS, $meta, $count, "Golden Boots");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::GOLD;
	}
	
	public function getBaseDurability(){
		return 13;
	}
	public function getDamageReduceAmount()
	{
		return 1;
	}
}