<?php

class IronBarsBlock extends TransparentBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(IRON_BARS, 0, "Iron Bars");
		$this->isFullBlock = false;
		$this->isSolid = false;
		$this->breakTime = 5;
		$this->hardness = 30;
		$this->material = Material::$metal;
	}
	
	public static function updateShape(Level $level, $x, $y, $z){
		$id = $level->level->getBlockID($x, $y, $z);
		
		$minX = 0.4375;
		$maxX = 0.5625;
		$minZ = 0.4375;
		$maxZ = 0.5625;
		
		$var9 = self::canConnectTo($level->level->getBlockID($x, $y, $z - 1));
		$var10 = self::canConnectTo($level->level->getBlockID($x, $y, $z + 1));
		$var11 = self::canConnectTo($level->level->getBlockID($x - 1, $y, $z));
		$var12 = self::canConnectTo($level->level->getBlockID($x + 1, $y, $z));
		
		if((!$var11 || !$var12) && ($var11 || $var12 || $var9 || $var10)){
			if($var11 && !$var12) $minX = 0;
			else if(!$var11 && $var12) $maxX = 1;
		}else{
			$minX = 0;
			$maxX = 1;
		}
		
		if((!$var9 || !$var10) && ($var11 || $var12 || $var9 || $var10)){
			if($var9 && !$var10) $minZ = 0;
			else if(!$var9 && $var10) $maxZ = 1;
		}else{
			$minZ = 0;
			$maxZ = 1;
		}
		
		StaticBlock::setBlockBounds($id, $minX, 0, $minZ, $maxX, 1, $maxZ);
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		$var8 = self::canConnectTo($level->level->getBlockID($x, $y, $z - 1));
		$var9 = self::canConnectTo($level->level->getBlockID($x, $y, $z + 1));
		$var10 = self::canConnectTo($level->level->getBlockID($x - 1, $y, $z));
		$var11 = self::canConnectTo($level->level->getBlockID($x + 1, $y, $z));
		$aabb = new AxisAlignedBB($x, $y, $z, $x, $y, $z);
		$arr = [];
		if((!$var10 || !$var11) && ($var10 || $var11 || $var8 || $var9)){
			if($var10 && !$var11) $arr[] = $aabb->addMinMax(0, 0, 0.4375, 0.5, 1, 0.5625);
			elseif(!$var10 && $var11) $arr[] = $aabb->addMinMax(0.5, 0, 0.4375, 1, 1, 0.5625);
		}else{
			$arr[] = $aabb->addMinMax(0, 0, 0.4375, 1, 1, 0.5625);
		}
		
		if((!$var8 || !$var9) && ($var10 || $var11 || $var8 || $var9)){
			if($var8 && !$var9) $arr[] = $aabb->addMinMax(0.4375, 0, 0, 0.5625, 1, 0.5);
			elseif(!$var8 && $var9) $arr[] = $aabb->addMinMax(0.4375, 0, 0.5, 0.5625, 1, 1);
		}else{
			$arr[] = $aabb->addMinMax(0.4375, 0, 0, 0.5625, 1, 1);
		}
		return $arr;
	}
	
	public static function canConnectTo($blockID) : bool{
		return StaticBlock::getIsSolid($blockID) || $blockID == IRON_BARS || $blockID == GLASS;
	}
	
}