<?php

class GoldenHelmetItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(GOLDEN_HELMET, $meta, $count, "Golden Helmet");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::GOLD;
	}
	
	public function getBaseDurability(){
		return 11;
	}
	public function getDamageReduceAmount()
	{
		return 1;
	}
}