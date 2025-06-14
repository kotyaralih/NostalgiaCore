<?php

class DiamondHelmetItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(DIAMOND_HELMET, $meta, $count, "Diamond Helmet");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::DIAMOND;
	}
	
	public function getBaseDurability(){
		return 11;
	}
	public function getDamageReduceAmount()
	{
		return 3;
	}
}