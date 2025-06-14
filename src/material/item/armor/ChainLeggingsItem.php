<?php

class ChainLeggingsItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(CHAIN_LEGGINGS, $meta, $count, "Chain Leggings");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::CHAIN;
	}
	
	public function getBaseDurability(){
		return 15;
	}
	public function getDamageReduceAmount()
	{
		return 4;
	}
}