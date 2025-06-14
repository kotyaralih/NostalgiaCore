<?php

class LiquidMaterial extends Material
{
	public function __construct(){
		parent::__construct();
		
		$this->blocksMotion = false;
		$this->isLiquid = true;
		$this->isSolid = false;
		$this->replaceable = true;
	}
}

