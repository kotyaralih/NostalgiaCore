<?php

class LeatherBootsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(LEATHER_BOOTS, $meta, $count, "Leather Boots");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::LEATHER;
	}
	
	public function getBaseDurability(){
		return 13;
	}
	public function getDamageReduceAmount()
	{
		return 1;
	}
}