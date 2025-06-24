<?php

class Reserved6Block extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(RESERVED6, 0, ".name<");
		$this->breakable = true;
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$dirt;
		$this->lightBlock = 255;
	}
	
}