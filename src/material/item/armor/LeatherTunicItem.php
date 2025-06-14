<?php

class LeatherTunicItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(LEATHER_TUNIC, $meta, $count, "Leather Tunic");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::LEATHER;
	}
	
	public function getBaseDurability(){
		return 16;
	}
	public function getDamageReduceAmount()
	{
		return 3;
	}
}