<?php

class StoneBrickStairsBlock extends StairBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(STONE_BRICK_STAIRS, $meta, "Stone Brick Stairs");
		$this->hardness = 30;
		$this->breakTime = 1.5;
		$this->material = Material::$stone;
	}
	
}