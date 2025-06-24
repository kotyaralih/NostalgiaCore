<?php


/***REM_START***/
require_once("WaterBlock.php"); //TODO class loader?
require_once("LiquidBlockStatic.php"); //TODO class loader?

/***REM_END***/

class StillWaterBlock extends LiquidBlockStatic{
	public static $blockID = STILL_WATER;
	public function __construct($meta = 0){
		LiquidBlock::__construct(STILL_WATER, $meta, "Still Water");
		$this->hardness = 500;
		$this->breakTime = -1; //in vanilla it is 100, but you cant interact with the block so it is not possible to destroy it
		$this->material = Material::$water;
		$this->lightBlock = 3;
	}
	
	public static function getTickDelay(){
		return 5;
	}
}