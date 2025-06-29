<?php

class DoorBlock extends TransparentBlock{
	/**
	 * @param int $id
	 * @param int $meta
	 * @param string $name
	 */
	public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
		$this->isSolid = false;
	}
	public static function updateShape(Level $level, $x, $y, $z){
		$id = $level->level->getBlockID($x, $y, $z);
		StaticBlock::setBlockBounds($id, 0.0, 0.0, 0.0, 1.0, 2.0, 1.0);
		$fullMeta = self::getCompositeData($level, $x, $y, $z);
		switch($fullMeta & 3){
			case 0:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) StaticBlock::setBlockBounds($id, 0, 0, 0, 1, 1, 0.1875);
					else StaticBlock::setBlockBounds($id, 0, 0, 1 - 0.1875, 1, 1, 1);
				}else{
					StaticBlock::setBlockBounds($id, 0, 0, 0, 0.1875, 1, 1);
				}
				break;
			case 1:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) StaticBlock::setBlockBounds($id, 1 - 0.1875, 0, 0, 1, 1, 1);
					else StaticBlock::setBlockBounds($id, 0, 0, 0, 0.1875, 1, 1);
				}else{
					StaticBlock::setBlockBounds($id, 0, 0, 0, 1, 1, 0.1875);
				}
				break;
			case 2:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) StaticBlock::setBlockBounds($id, 0, 0, 1 - 0.1875, 1, 1, 1);
					else StaticBlock::setBlockBounds($id, 0, 0, 0, 1, 1, 0.1875);
				}else{
					StaticBlock::setBlockBounds($id, 1 - 0.1875, 0, 0, 1, 1, 1);
				}
				break;
			case 3:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) StaticBlock::setBlockBounds($id, 0, 0, 0, 0.1875, 1, 1);
					else StaticBlock::setBlockBounds($id, 1 - 0.1875, 0, 0, 1, 1, 1);
				}else{
					StaticBlock::setBlockBounds($id, 0, 0, 1 - 0.1875, 1, 1, 1);
				}
				break;
		}
	}
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		$aabb = new AxisAlignedBB(0, 0, 0, 1, 2, 1);
		$fullMeta = self::getCompositeData($level, $x, $y, $z);
		switch($fullMeta & 3){
			case 0:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) $aabb->setBounds(0, 0, 0, 1, 1, 0.1875);
					else $aabb->setBounds(0, 0, 1 - 0.1875, 1, 1, 1);
				}else{
					$aabb->setBounds(0, 0, 0, 0.1875, 1, 1);
				}
				break;
			case 1:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) $aabb->setBounds(1 - 0.1875, 0, 0, 1, 1, 1);
					else $aabb->setBounds(0, 0, 0, 0.1875, 1, 1);
				}else{
					$aabb->setBounds(0, 0, 0, 1, 1, 0.1875);
				}
				break;
			case 2:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) $aabb->setBounds(0, 0, 1 - 0.1875, 1, 1, 1);
					else $aabb->setBounds(0, 0, 0, 1, 1, 0.1875);
				}else{
					$aabb->setBounds(1 - 0.1875, 0, 0, 1, 1, 1);
				}
				break;
			case 3:
				if(($fullMeta & 4) != 0){
					if(($fullMeta & 16) == 0) $aabb->setBounds(0, 0, 0, 0.1875, 1, 1);
					else $aabb->setBounds(1 - 0.1875, 0, 0, 1, 1, 1);
				}else{
					$aabb->setBounds(0, 0, 1 - 0.1875, 1, 1, 1);
				}
				break;
		}
		
		
		return [$aabb->offset($x, $y, $z)];
	}
	
	public static function getCompositeData(Level $level, $x, $y, $z){
		$myMeta = $level->level->getBlockDamage($x, $y, $z);
		
		if(($myMeta & 8) != 0){
			$metaLower = $level->level->getBlockDamage($x, $y - 1, $z);
			$metaUpper = $myMeta;
		}else{
			$metaLower = $myMeta;
			$metaUpper = $level->level->getBlockDamage($x, $y + 1, $z);
		}
		
		return $metaLower & 7 | (($myMeta & 8) != 0 ? 8 : 0) | (($metaUpper & 1 != 0) ? 16 : 0);
	}

	
	public static function neighborChanged(Level $level, $x, $y, $z, $nX, $nY, $nZ, $oldID){
		if($level->level->getBlockID($x, $y - 1, $z) == AIR){
			
			[$id, $meta] = $level->level->getBlock($x, $y, $z);
			
			$level->fastSetBlockUpdate($x, $y, $z, 0, 0);
			if(($meta & 8) == 0){
				if($id == DOOR_BLOCK) ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem(WOODEN_DOOR, 0, 1));
				elseif($id == IRON_DOOR_BLOCK) ServerAPI::request()->api->entity->drop(new Position($x+0.5, $y, $z+0.5, $level), BlockAPI::getItem(IRON_DOOR, 0, 1));
			}
			
			$top = $level->level->getBlockID($x, $y + 1, $z);
			if($top == IRON_DOOR_BLOCK || $top == DOOR_BLOCK){
				$level->fastSetBlockUpdate($x, $y + 1, $z, 0, 0);
			}
		}
	}


	/**
	 * @param Item $item
	 * @param Player $player
	 * @param Block $block
	 * @param Block $target
	 * @param integer $face
	 * @param integer $fx
	 * @param integer $fy
	 * @param integer $fz
	 *
	 * @return boolean
	 */
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($face === 1){
			$blockUp = $this->getSide(1);
			$blockDown = $this->getSide(0);
			if($blockUp->isReplaceable === false or $blockDown->isTransparent === true){
				return false;
			}
			$direction = $player->entity->getDirection();
			$face = array(
				0 => 3,
				1 => 4,
				2 => 2,
				3 => 5,
			);
			$next = $this->getSide($face[(($direction + 2) % 4)]);
			$next2 = $this->getSide($face[$direction]);
			$metaUp = 0x08;
			if($next->getID() === $this->id or ($next2->isTransparent === false and $next->isTransparent === true)){ //Door hinge
				$metaUp |= 0x01;
			}
			$this->level->setBlock($blockUp, BlockAPI::get($this->id, $metaUp), true, false, true); //Top
			
			$this->meta = $direction & 0x03;
			$this->level->setBlock($block, $this, true, false, true); //Bottom
			return true;			
		}
		return false;
	}

	/**
	 * @param Item $item
	 * @param Player $player
	 *
	 * @return boolean
	 */
	public function onBreak(Item $item, Player $player){
		if(($this->meta & 0x08) === 0x08){
			$down = $this->getSide(0);
			if($down->getID() === $this->id){
				$this->level->setBlock($down, new AirBlock(), true, false, true);
			}
		}else{
			$up = $this->getSide(1);
			if($up->getID() === $this->id){
				$this->level->setBlock($up, new AirBlock(), true, false, true);
			}
		}
		$this->level->setBlock($this, new AirBlock(), true, false, true);
		return true;
	}

	/**
	 * @param Item $item
	 * @param Player $player
	 *
	 * @return boolean
	 */
	public function onActivate(Item $item, Player $player){
		if(($this->meta & 0x08) === 0x08){ //Top
			$down = $this->getSide(0);
			if($down->getID() === $this->id){
				$meta = $down->getMetadata() ^ 0x04;
				$this->level->setBlock($down, BlockAPI::get($this->id, $meta), true, false, true);
				$players = ServerAPI::request()->api->player->getAll($this->level);
				unset($players[$player->CID]);
				$pk = new LevelEventPacket;
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->evid = 1003;
				$pk->data = 0;
				ServerAPI::request()->api->player->broadcastPacket($players, $pk); //TODO use block queue?
				return true;
			}
			return false;
		}else{
			$this->meta ^= 0x04;
			$this->level->setBlock($this, $this, true, false, true);
			$players = ServerAPI::request()->api->player->getAll($this->level);
			unset($players[$player->CID]);
			$pk = new LevelEventPacket;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->evid = 1003;
			$pk->data = 0;
			ServerAPI::request()->api->player->broadcastPacket($players, $pk);
		}
		return true;
	}
}