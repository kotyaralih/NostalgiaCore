<?php

class FarmlandBlock extends TransparentBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(FARMLAND, $meta, "Farmland");
		$this->hardness = 3;
		$this->breakTime = 0.6;
		$this->material = Material::$dirt;
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array(DIRT, 0, 1),
		);
	}
	public function hasCrops(){
		//TODO vanilla 0.8.1 detection method
		$b = $this->getSide(1);
		return $b->isTransparent && $b->id != 0;
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [new AxisAlignedBB($x, $y, $z, $x + 1, $y + 1, $z + 1)];
	}
	
	public static function fallOn(Level $level, $x, $y, $z, Entity $entity, $fallDistance){
		$rv = lcg_value();
		if($rv < ($fallDistance - 0.5)){
			$level->fastSetBlockUpdate($x, $y, $z, DIRT, 0, true);
		}
	}
	
	public static function onRandomTick(Level $level, $x, $y, $z){
		for($xx = $x-4; $xx <= $x+4; ++$xx){
			for($yy = $y; $yy <= $y+1; ++$yy){
				for($zz = $z-4; $zz <= $z+4; ++$zz){
					$id = $level->level->getBlockID($xx, $yy, $zz);
					if($id == WATER || $id == STILL_WATER){
						$dt = 7;
						goto set_wet_level;
					}
				}
			}
		}
		$dt = $level->level->getBlockDamage($x, $y, $z);
		if($dt > 0){
			$dt -= 1;
			set_wet_level:
			$level->fastSetBlockUpdateMeta($x, $y, $z, $dt, true);
			return;
		}
		$ab = $level->level->getBlockID($x, $y+1, $z);
		if($ab != WHEAT_BLOCK){
			//nc: added carrot/potato/beetroot checks
			if($ab != CARROT_BLOCK && $ab != POTATO_BLOCK && $ab != BEETROOT_BLOCK){
				$level->fastSetBlockUpdate($x, $y, $z, DIRT, true);
			}
		}
	}

	public static function checkWaterStatic(Level $level, $x, $y, $z)
	{
		for ($bx = $x - 4; $bx <= $x + 4; $bx++) {
			for ($by = $y; $by <= $y + 1; $by++) {
				for ($bz = $z - 4; $bz <= $z + 4; $bz++) {
					$id = $level->level->getBlockID($bx, $by, $bz);
					if ($id === WATER || $id === STILL_WATER) {
						return true;
					}
				}
			}
		}
	}
	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if(!StaticBlock::getIsTransparent($level->level->getBlockID($x, $y + 1, $z))){
			$level->fastSetBlockUpdate($x, $y, $z, DIRT, 0, true);
		}
	}

	public function getBlockID($x, $y, $z){
		return $this->level->level->getBlockID($x, $y, $z); //PMFLevel method
	}

	public function checkWater(){

		for($x = $this->x - 4; $x <= $this->x + 4; $x++){
			for($y = $this->y; $y <= $this->y + 1; $y++){
				for($z = $this->z - 4; $z <= $this->z + 4; $z++){
					$id = $this->getBlockID($x, $y, $z);
					if($id === 8 || $id === 9){
						return true;
					}
				}
			}
		}
		return false;

	}
}
