<?php
require_once("LiquidBlockDynamic.php"); //TODO class loader?

class LavaBlock extends LiquidBlockDynamic implements LightingBlock{
	public static $blockID = LAVA;
	public function __construct($meta = 0){
		parent::__construct(LAVA, $meta, "Lava");
		$this->hardness = 500; //0 in vanilla
		$this->breakTime = -1; //in vanilla it is 0, but you cant interact with the block so it is not possible to destroy it
		$this->material = Material::$lava;
		$this->lightEmission = 15;
		$this->lightBlock = 255;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$ret = $this->level->setBlock($this, $this, true, false, true);
		return $ret;
	}
	
	public static function getTickDelay(){
		return 30;
	}
	
	public function getMaxLightValue(){
		return 15;
	}
}
