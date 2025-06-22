<?php

class GravelBlock extends FallableBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(GRAVEL, 0, "Gravel");
		$this->hardness = 3;
		$this->breakTime = 0.6;
		$this->material = Material::$sand;
		$this->lightBlock = 255;
	}
	
	public function getDrops(Item $item, Player $player){
		return [
			[mt_rand(1,10) == 1 ? FLINT : GRAVEL, 0, 1],
		];
	}
	
}