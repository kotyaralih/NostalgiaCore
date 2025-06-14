<?php

class IronHelmetItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(IRON_HELMET, $meta, $count, "Iron Helmet");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::IRON;
	}
	
	public function getBaseDurability(){
		return 11;
	}
	public function getDamageReduceAmount()
	{
		return 2;
	}
}