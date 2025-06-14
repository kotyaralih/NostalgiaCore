<?php

class DiamondLeggingsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(DIAMOND_LEGGINGS, $meta, $count, "Diamond Leggings");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::DIAMOND;
	}
	
	public function getBaseDurability(){
		return 15;
	}
	public function getDamageReduceAmount()
	{
		return 6;
	}
}