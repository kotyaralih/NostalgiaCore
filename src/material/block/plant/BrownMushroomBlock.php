<?php

class BrownMushroomBlock extends FlowableBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(BROWN_MUSHROOM, 0, "Brown Mushroom");
		$this->hardness = 0;
		$this->breakTime = 0;
		$this->material = Material::$plant;
		$this->lightEmission = 1;
	}

	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if(StaticBlock::getIsTransparent($level->level->getBlockID($x, $y - 1, $z))){ //Replace with common break method
			ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem(BROWN_MUSHROOM));
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
		}
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$down = $this->getSide(0);
			if($down->isTransparent === false){
				$this->level->setBlock($block, $this, true, false, true);
				return true;
			}
		return false;
	}		
}