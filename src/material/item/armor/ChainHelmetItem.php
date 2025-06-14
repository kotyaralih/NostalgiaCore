<?php

class ChainHelmetItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(CHAIN_HELMET, $meta, $count, "Chain Helmet");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::CHAIN;
	}
	
	public function getBaseDurability(){
		return 11;
	}
	public function getDamageReduceAmount()
	{
		return 1;
	}
}