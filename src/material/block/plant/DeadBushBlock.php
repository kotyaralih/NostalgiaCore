<?php

class DeadBushBlock extends FlowableBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(DEAD_BUSH, 0, "Dead Bush");
		//$this->isReplaceable = true;
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$replaceable_plant;
	}
	
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if(StaticBlock::getIsTransparent($level->level->getBlockID($x, $y - 1, $z))){ //Replace with common break method
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->getID() == 12){
			$this->level->setBlock($block, $this, true, false, true);
			return true;
		} 
		return false;
	}
	
	public function getDrops(Item $item, Player $player){
		if($item->isShears()){
			return [
				[DEAD_BUSH, 0, 1],
			];
		}
			
		return [];
	}
}