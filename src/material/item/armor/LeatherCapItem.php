<?php

class LeatherCapItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(LEATHER_CAP, $meta, $count, "Leather Cap");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::LEATHER;
	}
	
	public function getBaseDurability(){
		return 11;
	}
	public function getDamageReduceAmount()
	{
		return 1;
	}
}