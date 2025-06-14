<?php

class QuartzStairsBlock extends StairBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(QUARTZ_STAIRS, $meta, "Quartz Stairs");
		$this->breakTime = 0.8;
		$this->hardness = 4;
		$this->material = Material::$stone;
	}
	
}