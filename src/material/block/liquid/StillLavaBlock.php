<?php
require_once("LiquidBlockStatic.php"); //TODO class loader?

class StillLavaBlock extends LiquidBlockStatic{
	public static $blockID = STILL_LAVA;
	public function __construct($meta = 0){
		parent::__construct(STILL_LAVA, $meta, "Still Lava");
		$this->hardness = 500;
		$this->breakTime = -1; //in vanilla it is 100, but you cant interact with the block so it is not possible to destroy it
		$this->material = Material::$lava;
	}
	
	public static function getTickDelay(){
		return 30;
	}
	
}