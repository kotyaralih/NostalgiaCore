<?php

class DiamondBootsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(DIAMOND_BOOTS, $meta, $count, "Diamond Boots");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::DIAMOND;
	}
	
	public function getBaseDurability(){
		return 13;
	}
	public function getDamageReduceAmount()
	{
		return 3;
	}
}