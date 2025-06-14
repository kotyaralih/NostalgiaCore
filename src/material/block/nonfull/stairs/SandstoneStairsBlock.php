<?php

class SandstoneStairsBlock extends StairBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(SANDSTONE_STAIRS, $meta, "Sandstone Stairs");
		$this->hardness = 0.8*5;
		$this->breakTime = 0.8;
		$this->material = Material::$stone;
	}
	
}