<?php

class BedBlock extends TransparentBlock{
	public static $blockID;
	public static $faces = [
		0 => 3,
		1 => 4,
		2 => 2,
		3 => 5,
	];
	const HEAD_DIRECTION_OFFSETS = [
		[0, 1],
		[-1, 0],
		[0, -1],
		[1, 0]
	];
	public function __construct($type = 0){
		parent::__construct(BED_BLOCK, $type, "Bed Block");
		$this->isActivable = true;
		$this->isFullBlock = false;
		$this->hardness = 1;
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [static::getAABB($level, $x, $y, $z)];
	}
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		
		$meta = $level->level->getBlockDamage($x, $y, $z);
		$dir = $meta & 3;
		$isHead = ($meta >> 3) & 1;
		
		$offX = self::HEAD_DIRECTION_OFFSETS[$dir][0];
		$offZ = self::HEAD_DIRECTION_OFFSETS[$dir][1];
		
		if($isHead){
			if($level->level->getBlockID($x - $offX, $y, $z - $offZ) != BED_BLOCK){
				$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
			}
		}else if($level->level->getBlockID($x + $offX, $y, $z + $offZ) != BED_BLOCK){
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
			
			ServerAPI::request()->api->entity->dropRawPos(
				$level, 
				$x + (lcg_value() * 0.7) + 0.15, 
				$y + (lcg_value() * 0.7) + 0.15, 
				$z + (lcg_value() * 0.7) + 0.15, 
				BlockAPI::getItem(BED, 0, 1), 
				lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1
			);
			
		}
		
	}
	public static function findStandUpPosition(Level $level, $x, $y, $z){
		$blockMeta = $level->level->getBlockDamage($x, $y, $z);
		$direction = $blockMeta & 0x3;
		for($v7 = 0; $v7 <= 1; ++$v7){
			$minX = $x - self::HEAD_DIRECTION_OFFSETS[$direction][0] * $v7 - 1;
			$minZ = $z - self::HEAD_DIRECTION_OFFSETS[$direction][1] * $v7 - 1;
			$maxX = $minX + 2;
			$maxZ = $minZ + 2;
			
			for($xCheck = $minX; $xCheck <= $maxX; ++$xCheck){
				for($zCheck = $minZ; $zCheck <= $maxZ; ++$zCheck){
					$idCheck = $level->level->getBlockID($xCheck, $y - 1, $zCheck);
					if(!StaticBlock::getIsTransparent($idCheck) && $level->level->getBlockID($xCheck, $y, $zCheck) == 0 && $level->level->getBlockID($xCheck, $y + 1, $zCheck) == 0){
						return new Position($xCheck, $y, $zCheck, $level);
					}
				}
			}
		}
		
		return null;
	}
	
	public function onActivate(Item $item, Player $player){
		if(ServerAPI::request()->api->time->getPhase($player->level) !== "night"){
			$player->sendChat("You can only sleep at night");
			return true;
		}
		
		$blockNorth = $this->getSide(2); //Gets the blocks around them
		$blockSouth = $this->getSide(3);
		$blockEast = $this->getSide(5);
		$blockWest = $this->getSide(4);
		if(($this->meta & 0x08) === 0x08){ //This is the Top part of bed	
			$b = $this;
		}else{ //Bottom Part of Bed
			if($blockNorth->getID() === $this->id and ($blockNorth->meta & 0x08) === 0x08){
				$b = $blockNorth;
			}elseif($blockSouth->getID() === $this->id and ($blockSouth->meta & 0x08) === 0x08){
				$b = $blockSouth;
			}elseif($blockEast->getID() === $this->id and ($blockEast->meta & 0x08) === 0x08){
				$b = $blockEast;
			}elseif($blockWest->getID() === $this->id and ($blockWest->meta & 0x08) === 0x08){
				$b = $blockWest;
			}else{
				$player->sendChat("This bed is incomplete");
				return true;
			}
		}

		if($player->sleepOn($b) === false){
			$player->sendChat("This bed is occupied");
		}
		return true;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$down = $this->getSide(0);
		if($down->isTransparent === false){
			
			$d = $player->entity->getDirection();
			$next = $this->getSide(self::$faces[(($d + 3) % 4)]);
			$downNext = $next->getSide(0);
			if($next->isReplaceable === true and $downNext->isTransparent === false){
				$meta = (($d + 3) % 4) & 0x03;
				$this->level->fastSetBlockUpdate($block->x, $block->y, $block->z, $this->id, $meta, true);
				$this->level->fastSetBlockUpdate($next->x, $next->y, $next->z, $this->id, $meta | 0x08, true);
				return true;
			}
		}
		return false;
	}	
	
	public function onBreak(Item $item, Player $player){
		$blockNorth = $this->getSide(2); //Gets the blocks around them
		$blockSouth = $this->getSide(3);
		$blockEast = $this->getSide(5);
		$blockWest = $this->getSide(4);
		
		if(($this->meta & 0x08) === 0x08){ //This is the Top part of bed			
			switch($this->meta & 0x7){
				case 0:
					if($blockNorth->id === $this->id) $this->level->setBlock($blockNorth, new AirBlock(), true, false, true);
					break;
				case 1:
					if($blockEast->id === $this->id) $this->level->setBlock($blockEast, new AirBlock(), true, false, true);
					break;
				case 2:
					if($blockSouth->id === $this->id) $this->level->setBlock($blockSouth, new AirBlock(), true, false, true);
					break;
				case 3:
					if($blockWest->id === $this->id) $this->level->setBlock($blockWest, new AirBlock(), true, false, true);
					break;
			}
		}else{ //Bottom Part of Bed
			switch($this->meta & 0x7){
				case 0:
					if($blockSouth->id === $this->id) $this->level->setBlock($blockSouth, new AirBlock(), false, false, true);
					break;
				case 1:
					if($blockWest->id === $this->id) $this->level->setBlock($blockWest, new AirBlock(), false, false, true);
					break;
				case 2:
					if($blockNorth->id === $this->id) $this->level->setBlock($blockNorth, new AirBlock(), false, false, true);
					break;
				case 3:
					if($blockEast->id === $this->id) $this->level->setBlock($blockEast, new AirBlock(), false, false, true);
					break;
			}
		}
		$this->level->setBlock($this, new AirBlock(), true, false, true);
		return true;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(BED, 0, 1),
		);
	}
	
}