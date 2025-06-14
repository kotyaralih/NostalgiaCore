<?php

class AirBlock extends TransparentBlock{
	public static $blockID;
	
	public function __construct(){
		parent::__construct(AIR, 0, "Air");
		$this->isActivable = false;
		$this->breakable = false;
		$this->isFlowable = true;
		$this->isTransparent = true;
		$this->isReplaceable = true;
		$this->isPlaceable = false;
		$this->hasPhysics = false;
		$this->isSolid = false;
		$this->isFullBlock = true;
		$this->hardness = 0;
		$this->material = Material::$air;
		
	}
	
}