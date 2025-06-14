<?php

class GasMaterial extends Material
{
	public function __construct(){
		parent::__construct();
		
		$this->blocksLight = false;
		$this->blocksMotion = false;
		$this->isSolid = false;
		$this->replaceable = true;
	}
}

