<?php

class LiquidBlockDynamic extends LiquidBlock{
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}
	
	public static $blockID = 0;
	public static $sourcesAround = 0;
	public static $spread = [0, 0, 0,  0];
	
	public static function getHighest(Level $level, $x, $y, $z, $highest){
		$depth = static::getDepth($level, $x, $y, $z);
		if($depth < 0) return $highest;
		if($depth == 0) ++static::$sourcesAround;
		if($depth >= 8) $depth = 0;
		
		return $highest >= 0 && $depth >= $highest ? $highest : $depth;
	}
	
	public static function setStatic(Level $level, $x, $y, $z){
		$meta = $level->level->getBlockDamage($x, $y, $z);
		
		$level->fastSetBlockUpdate($x, $y, $z, static::$blockID + 1, $meta);
	}
	
	public static function isWaterBlocking(Level $level, $x, $y, $z){
		$id = $level->level->getBlockID($x, $y, $z);
		
		if($id == AIR) return false;
		
		if($id == DOOR_BLOCK || $id == SIGN_POST || $id == WALL_SIGN || $id == LADDER || $id == SUGARCANE_BLOCK) return true;
		
		if($id == CARPET || $id == SNOW_LAYER || $id == RAIL || $id == POWERED_RAIL) return false; //TODO Tile::getThickness() > 0
		
		$mat = StaticBlock::getMaterial($id);
		if($mat->blocksMotion) return true;
		return $mat->isSolid;
	}
	
	public static function canSpreadTo(Level $level, $x, $y, $z){
		$id = $level->level->getBlockID($x, $y, $z);
		if($id == LAVA || $id == STILL_LAVA) return false;
		if((static::$blockID == WATER) && ($id == WATER || $id == STILL_WATER)) return false;
		
		return static::isWaterBlocking($level, $x, $y, $z) ^ 1;
	}
	
	public static function getSpread(Level $level, $x, $y, $z){
		for($i = 0; $i < 4; ++$i){
			static::$spread[$i] = 1000;
			$xs = $x;
			$ys = $y;
			$zs = $z;
			
			switch($i){
				case 0:
					--$xs;
					break;
				case 1:
					++$xs;
					break;
				case 2:
					--$zs;
					break;
				case 3:
					++$zs;
					break;
			}
			
			if(static::isWaterBlocking($level, $xs, $ys, $zs)) continue;
			[$id, $meta] = $level->level->getBlock($xs, $ys, $zs);
			if(((static::$blockID == WATER && ($id == WATER || $id == STILL_WATER)) || (static::$blockID == LAVA && ($id == LAVA || $id == STILL_LAVA))) && $meta == 0){
				continue;
			}
			
			if(!static::isWaterBlocking($level, $xs, $ys - 1, $zs)) static::$spread[$i] = 0;
			else static::$spread[$i] = static::getSlopeDistance($level, $xs, $ys, $zs, 1, $i);
		}
		$i1 = static::$spread[0];
		for($k1 = 1; $k1 < 4; ++$k1){
			if(static::$spread[$k1] < $i1) $i1 = static::$spread[$k1];
		}
		$ba = [];
		for($i = 0; $i < 4; ++$i) $ba[$i] = ($i1 == static::$spread[$i]);
		return $ba;
	}
	
	public static function getSlopeDistance(Level $level, $x, $y, $z, $l, $i1){
		$j1 = 1000;
		for($k1 = 0; $k1 < 4; ++$k1){
			if($k1 == 0 && $i1 == 1 || $k1 == 1 && $i1 == 0 || $k1 == 2 &&  $i1 == 3 || $k1 == 3 && $i1 == 2){
				continue;
			}
			
			$xs = $x;
			$ys = $y;
			$zs = $z;
			
			switch($k1){
				case 0:
					--$xs;
					break;
				case 1:
					++$xs;
					break;
				case 2:
					--$zs;
					break;
				case 3:
					++$zs;
					break;
			}
			
			if(static::isWaterBlocking($level, $xs, $ys, $zs)) continue;
			[$id, $meta] = $level->level->getBlock($xs, $ys, $zs);
			
			if(
				((static::$blockID == WATER && ($id == WATER || $id == STILL_WATER)) ||
				(static::$blockID == LAVA && ($id == LAVA || $id == STILL_LAVA))) &&
				$meta == 0
			){
				continue;
			}
			
			if(!static::isWaterBlocking($level, $xs, $ys - 1, $zs)) return $l;
			
			if($l >= 4) continue;
			$k2 = static::getSlopeDistance($level, $xs, $ys, $zs, $l + 1, $k1);
			if($k2 < $j1) $j1 = $k2;
		}
		return $j1;
	}
	
	public static function trySpreadTo(Level $level, $x, $y, $z, $meta){
		if(static::canSpreadTo($level, $x, $y, $z)){
			[$id, $meta2] = $level->level->getBlock($x, $y, $z);
			
			if($id > 0){
				if(($id == LAVA || $id == STILL_LAVA) && (static::$blockID == LAVA || static::$blockID == STILL_LAVA)); //fizz
				else{
					//TODO better way to spawn resources
					$drop = BlockAPI::get($id, $meta2, 1)->getDrops(BlockAPI::getItem(0, 0, 0), PlayerNull::$INSTANCE);
					$pos = new Position($x, $y, $z, $level);
					foreach($drop as $item){
						ServerAPI::request()->api->entity->drop($pos, BlockAPI::getItem($item[0], $item[1], $item[2]));
					}
				}
			}
			
			$level->fastSetBlockUpdate($x, $y, $z, static::$blockID, $meta, true);
		}
	}
	
	public static function onPlace(Level $level, $x, $y, $z){
		static::updateLiquid($level, $x, $y, $z);
		$id = $level->level->getBlockID($x, $y, $z);
		if($id == static::$blockID) ServerAPI::request()->api->block->scheduleBlockUpdateXYZ($level, $x, $y, $z, BLOCK_UPDATE_SCHEDULED, static::getTickDelay());
	}
	
	public static function onUpdate(Level $level, $x, $y, $z, $type){
		$id = $level->level->getBlockID($x, $y, $z);
		$depth = static::getDepth($level, $x, $y, $z);
		$flowAdd = (static::$blockID == LAVA) ?  2 : 1;
		$flag = true;
		
		if($depth > 0){
			static::$sourcesAround = 0;
			$highest = static::getHighest($level, $x - 1, $y, $z, -100);
			$highest = static::getHighest($level, $x + 1, $y, $z, $highest);
			$highest = static::getHighest($level, $x, $y, $z - 1, $highest);
			$highest = static::getHighest($level, $x, $y, $z + 1, $highest);
			$j1 = $highest + $flowAdd;
			if($j1 >= 8 || $highest < 0) $j1 = -1;
			$l1 = static::getDepth($level, $x, $y + 1, $z);
			if($l1 >= 0) $j1 = ($l1 >= 8 ? $l1 : $l1 + 8);
			
			if(static::$sourcesAround >= 2 && static::$blockID == WATER){
				$idBot = $level->level->getBlockID($x, $y - 1, $z);
				if($idBot > 0){
					if(StaticBlock::getIsSolid($idBot) || (($idBot == WATER || $idBot == STILL_WATER) && $level->level->getBlockDamage($x, $y, $z) == 0)) $j1 = 0;
				}
			}
			
			//if(static::$blockID == LAVA && $depth < 8 && $j1 < 8 && $j1 > $depth && mt_rand(0, 4) != 0){ TODO fix later
			//	$j1 = $depth;
			//	$flag = false;
			//}
			
			if($j1 != $depth){
				$depth = $j1;
				if($depth < 0) $level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
				else{
					$level->fastSetBlockUpdateMeta($x, $y, $z, $depth, true);
					ServerAPI::request()->api->block->scheduleBlockUpdateXYZ($level, $x, $y, $z, BLOCK_UPDATE_SCHEDULED, static::getTickDelay());
					$level->updateNeighborsAt($x, $y, $z, static::$blockID); //TODO check is needed
				}
			}else if($flag){
				static::setStatic($level, $x, $y, $z);
			}
		}else{
			static::setStatic($level, $x, $y, $z);
		}
		
		if(static::canSpreadTo($level, $x, $y - 1, $z)){
			$level->fastSetBlockUpdate($x, $y - 1, $z, static::$blockID, ($depth >= 8 ? $depth : $depth + 8) & 0xf, true);
		}else if($depth >= 0 && ($depth == 0 || static::isWaterBlocking($level, $x, $y - 1, $z))){
			$flags = static::getSpread($level, $x, $y, $z);
			$k1 = $depth >= 8 ? 1 : ($depth + $flowAdd);
			if($k1 >= 8) return;

			if($flags[0]) static::trySpreadTo($level, $x - 1, $y, $z, $k1);
			if($flags[1]) static::trySpreadTo($level, $x + 1, $y, $z, $k1);
			if($flags[2]) static::trySpreadTo($level, $x, $y, $z - 1, $k1);
			if($flags[3]) static::trySpreadTo($level, $x, $y, $z + 1, $k1);
		}
	}
}