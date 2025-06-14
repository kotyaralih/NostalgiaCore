<?php

class StoneWallBlock extends TransparentBlock{
	public static $blockID;
	//TODO collision box
	public function __construct($meta = 0){
		parent::__construct(STONE_WALL, $meta & 0x01, $meta === 1 ? "Mossy Cobblestone Wall" : "Cobblestone Wall");
		$this->isFullBlock = false;
		$this->isSolid = false;
		$this->hardness = 50;
		$this->breakTime = 10;
		$this->material = Material::$stone;
	}
	public function getDrops(Item $item, Player $player){
		if($item->isPickaxe()){
			return [[STONE_WALL, $this->getMetadata(),1]];
		}
		return [];
	}
	
	public static function connectsTo(Level $level, $x, $y, $z){
		$id = $level->level->getBlockID($x, $y, $z);
		
		if($id == STONE_WALL || $id == FENCE_GATE) return true;
		if($id == 0) return false;
		
		if(StaticBlock::getIsSolid($id) && !StaticBlock::getIsTransparent($id)){ //XXX in vanilla it uses Material->isSolidBlocking() and Tile->isCubeShaped()
			return true; //XXX in vanilla it returns v7->material != Material::vegetable;
		}
		return false;
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [static::getAABB($level, $x, $y, $z)];
	}
	
	public static function getAABB(Level $level, $x, $y, $z){
		static::updateShape($level, $x, $y, $z);
		StaticBlock::$maxYs[$level->level->getBlockID($x, $y, $z)] = 1.5;
		return parent::getAABB($level, $x, $y, $z);
	}
	
	public static function updateShape(Level $level, $x, $y, $z){
		$zNeg = self::connectsTo($level, $x, $y, $z - 1);
		$zPos = self::connectsTo($level, $x, $y, $z + 1);
		$xNeg = self::connectsTo($level, $x - 1, $y, $z);
		$xPos = self::connectsTo($level, $x + 1, $y, $z);
		
		$minX = 0.25;
		$maxY = 1.0;
		$maxX = 0.75;
		
		$minZ = $zNeg ? 0 : 0.25;
		$maxZ = $zPos ? 1.0 : 0.75;
		
		if($xNeg) $minX = 0;
		if($xPos) $maxX = 1.0;
		
		if($zNeg){
			if($zPos && !$xNeg){
				if(!$xPos){
					$maxY = 0.8125;
					$maxX = 0.6875;
					$minX = 0.3125;
				}
			}
		}else if(!$zPos && $zNeg){
			if($xPos){
				$maxY = 0.8125;
				$maxZ = 0.6875;
				$minZ = 0.3125;
			}
		}
		$id = $level->level->getBlockID($x, $y, $z);
		StaticBlock::setBlockBounds($id, $minX, 0, $minZ, $maxX, $maxY, $maxZ);
	}
}