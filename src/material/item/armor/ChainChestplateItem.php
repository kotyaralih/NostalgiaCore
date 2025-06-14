<?php

class ChainChestplateItem extends ArmorItem{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(CHAIN_CHESTPLATE, $meta, $count, "Chain Chestplate");
	}
	
	public function getMaterialDurability(){
		return ArmorMaterial::CHAIN;
	}
	
	public function getBaseDurability(){
		return 16;
	}
	public function getDamageReduceAmount()
	{
		return 5;
	}
}