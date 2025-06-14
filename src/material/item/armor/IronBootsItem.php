<?php

class IronBootsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(IRON_BOOTS, $meta, $count, "Iron Boots");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::IRON;
	}
	
	public function getBaseDurability(){
		return 13;
	}
	public function getDamageReduceAmount()
	{
		return 2;
	}
}