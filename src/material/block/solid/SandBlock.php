<?php

class SandBlock extends FallableBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(SAND, 0, "Sand");
		$this->hardness = 2.5;
		$this->breakTime = 0.5;
		$this->material = Material::$sand;
		$this->lightBlock = 255;
	}
	
}