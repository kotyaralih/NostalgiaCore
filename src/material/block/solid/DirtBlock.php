<?php

class DirtBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(DIRT, 0, "Dirt");
		$this->isActivable = true;
		$this->hardness = 2.5;
	}

	public function onActivate(Item $item, Player $player){
		if($item->isHoe()){
			if($this->getSide(1)->isTransparent === false) return false;
			$item->hurtAndBreak(1, $player); //TODO move to ItemHoe
			$this->level->fastSetBlockUpdate($this->x, $this->y, $this->z, FARMLAND, 0, true);
			return true;
		}
		return false;
	}
	public static function onRandomTick(Level $level, $x, $y, $z){
		/*if(mt_rand(0, 3) == 0){
			$up = $level->getBlockWithoutVector($x, $y + 1, $z, false); //$this->getSide(1);
			if(($up->isTransparent === false) or ($up->isLiquid) or ($up->getID() == 60)) return false;
			if(self::getGrassInRadius($level, $x, $y, $z)){
				$level->setBlock(new Position($x, $y, $z, $level), BlockAPI::get(GRASS), true, false, true);
			}
		}*/
	}

	public static function getGrassInRadius(Level $level, $x, $y, $z){ //umwut (although kinda faster than for loop =D)
		if($level->level->getBlockID($x+1, $y, $z+1) == 2) return true;
		if($level->level->getBlockID($x+1, $y, $z) == 2) return true;
		if($level->level->getBlockID($x+1, $y, $z-1) == 2) return true;
		if($level->level->getBlockID($x, $y, $z+1) == 2) return true;
		if($level->level->getBlockID($x, $y, $z-1) == 2) return true;
		if($level->level->getBlockID($x-1, $y, $z+1) == 2) return true;
		if($level->level->getBlockID($x-1, $y, $z) == 2) return true;
		if($level->level->getBlockID($x-1, $y, $z-1) == 2) return true;

		if($level->level->getBlockID($x+1, $y-1, $z+1) == 2) return true;
		if($level->level->getBlockID($x+1, $y-1, $z) == 2) return true;
		if($level->level->getBlockID($x+1, $y-1, $z-1) == 2) return true;
		if($level->level->getBlockID($x, $y-1, $z+1) == 2) return true;
		if($level->level->getBlockID($x, $y-1, $z) == 2) return true;
		if($level->level->getBlockID($x, $y-1, $z-1) == 2) return true;
		if($level->level->getBlockID($x-1, $y-1, $z+1) == 2) return true;
		if($level->level->getBlockID($x-1, $y-1, $z) == 2) return true;
		if($level->level->getBlockID($x-1, $y-1, $z-1) == 2) return true;

		if($level->level->getBlockID($x+1, $y+1, $z+1) == 2) return true;
		if($level->level->getBlockID($x+1, $y+1, $z) == 2) return true;
		if($level->level->getBlockID($x+1, $y+1, $z-1) == 2) return true;
		if($level->level->getBlockID($x, $y+1, $z+1) == 2) return true;
		if($level->level->getBlockID($x, $y+1, $z-1) == 2) return true;
		if($level->level->getBlockID($x-1, $y+1, $z+1) == 2) return true;
		if($level->level->getBlockID($x-1, $y+1, $z) == 2) return true;
		if($level->level->getBlockID($x-1, $y+1, $z-1) == 2) return true;

		return false;
	}

}