<?php

class DecorationMaterial extends Material
{
	public function __construct(){
		parent::__construct();
		
		$this->flammable = false;
		$this->translucent = false;
		$this->alwaysDestroyable = false;
		$this->replaceable = false;
		
		$this->blocksMotion = false;
		$this->blocksLight = false;
		$this->isSolid = false;
	}
}

