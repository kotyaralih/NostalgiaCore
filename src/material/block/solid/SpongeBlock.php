<?php

class SpongeBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(SPONGE, 0, "Sponge");
		$this->hardness = 3;
		$this->breakTime = 0.6;
		$this->material = Material::$dirt;
	}
	
}
