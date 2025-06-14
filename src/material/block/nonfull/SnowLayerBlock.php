<?php

class SnowLayerBlock extends FlowableBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(SNOW_LAYER, $meta, "Snow Layer");
		$this->isReplaceable = true;
		$this->isSolid = false;
		$this->isFullBlock = false;
		$this->hardness = 0.5;
		$this->breakTime = 0.1;
		$this->material = Material::$topSnow;
	}
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down instanceof SolidBlock){
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		}
		return false;
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if($level->level->getBlockID($x, $y - 1, $z) == AIR){ //Replace with common break method
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
		}
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isShovel() !== false){
			return array(
				array(SNOWBALL, 0, 1),
			);
		}
		
		return array();
	}
}