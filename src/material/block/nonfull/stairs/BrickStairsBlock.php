<?php

class BrickStairsBlock extends StairBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(BRICK_STAIRS, $meta, "Brick Stairs");
		$this->breakTime = 2;
		$this->hardness = 30;
		$this->material = Material::$stone;
	}
	
}