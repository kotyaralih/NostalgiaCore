<?php

class CobblestoneStairsBlock extends StairBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(COBBLESTONE_STAIRS, $meta, "Cobblestone Stairs");
		$this->breakTime = 2;
		$this->hardness = 30;
		$this->material = Material::$stone;
	}
	
}