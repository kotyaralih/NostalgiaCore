<?php

class ItemEntity extends Entity{
	const TYPE = ENTITY_ITEM_TYPE;
	const CLASS_TYPE = ENTITY_ITEM;
	public static $searchRadiusX = 0.5, $searchRadiusY = 0.0, $searchRadiusZ = 0.5;
	
	public $meta, $stack, $itemID;
	
	public function __construct(Level $level, $eid, $class, $type = 0, $data = array())
	{
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setSize(0.25, 0.25);
		$this->yOffset = $this->height / 2;
		if(isset($data["item"]) and ($data["item"] instanceof Item)){
			$this->meta = $data["item"]->getMetadata();
			$this->stack = $data["item"]->count;
			$this->itemID = $data["item"]->getID();
		} else{
			$this->meta = (int) $data["meta"];
			$this->stack = (int) $data["stack"];
			$this->itemID = (int) $data["itemID"];
		}
		$this->hasGravity = true;
		$this->setHealth(5, "generic");
		$this->gravity = 0.04;
		$this->delayBeforePickup = 40; //in vanilla it is 0 by default
		$this->stepHeight = 0;
	}
	
	public function counterUpdate(){
		parent::counterUpdate();
		if($this->delayBeforePickup > 0) --$this->delayBeforePickup;
	}
	
	public function searchForOtherItemsNearby(){
		$ents = $this->level->getEntitiesInAABBOfType($this->boundingBox->expand(self::$searchRadiusX, self::$searchRadiusY, self::$searchRadiusZ), ENTITY_ITEM);
		
		foreach($ents as $e){
			$this->tryCombining($e);
		}
		
	}
	
	public function spawn($player)
	{
		$pk = new AddItemEntityPacket();
		$pk->eid = $this->eid;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->roll = 0;
		$pk->item = BlockAPI::getItem($this->itemID, $this->meta, $this->stack);
		$pk->metadata = $this->getMetadata();
		$player->dataPacketAlwaysRecover($pk);
		
		$pk = new SetEntityMotionPacket();
		$pk->eid = $this->eid;
		$pk->speedX = $this->speedX;
		$pk->speedY = $this->speedY;
		$pk->speedZ = $this->speedZ;
		$player->dataPacket($pk);
	}
	
	
	public function tryCombining(Entity $another){
		
		if($another->eid == $this->eid) return false;
		
		if(!$another->closed && !$this->closed){
			if($another->itemID == $this->itemID && $another->meta == $this->meta){
				if(($another->stack + $this->stack) > 64) return false; //TODO dynamic stack size
				
				$another->stack += $this->stack;
				$another->age = min($this->age, $another->age);
				$this->close();
				//TODO respawn another entity?
			}
		}
	}
	public function checkInTile($x, $y, $z){
		$xFloor = floor($x);
		$yFloor = floor($y);
		$zFloor = floor($z);
		
		$id = $this->level->level->getBlockID($xFloor, $yFloor, $zFloor);
		
		if(StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id)){
			$xDiff = $x - $xFloor;
			
			$id = $this->level->level->getBlockID($xFloor - 1, $yFloor, $zFloor);
			$xNeg = StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id);
			
			$id = $this->level->level->getBlockID($xFloor + 1, $yFloor, $zFloor);
			$xPos = StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id);
			
			$id = $this->level->level->getBlockID($xFloor, $yFloor - 1, $zFloor);
			$yNeg = StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id);
			
			$id = $this->level->level->getBlockID($xFloor, $yFloor + 1, $zFloor);
			$yPos = StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id);
			
			$id = $this->level->level->getBlockID($xFloor, $yFloor, $zFloor - 1);
			$zNeg = StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id);
			
			$id = $this->level->level->getBlockID($xFloor, $yFloor, $zFloor + 1);
			$zPos = StaticBlock::getIsSolid($id) && StaticBlock::getIsFullBlock($id);
			
			if($xNeg || $xDiff >= 9999.0){ //TODO not needed check?
				$v15 = 9999.0;
				$v16 = -1;
			}else{
				$v15 = $xDiff;
				$v16 = 0;
			}
			
			if(!$xPos){
				$v17 = 1 - $xDiff;
				
				if($v17 < $v15){
					$v15 = $v17;
					$v16 = 1;
				}
			}
			
			$yDiff = $y - $yFloor;
			if(!$yNeg && $yDiff < $v15){
				$v15 = $yDiff;
				$v16 = 2;
			}
			
			if(!$yPos){
				$v19 = 1.0 - $yDiff;
				if($v19 < $v15){
					$v15 = $v19;
					$v16 = 3;
				}
			}
			
			$zDiff = $z - $zFloor;
			if(!$zNeg && $zDiff < $v15){
				$v15 = $zDiff;
				$v16 = 4;
			}
			
			if(!$zPos && ((1.0 - $zDiff) < $v15)){
				$v16 = 5;
			}
			
			$v21 = lcg_value() * 0.2 + 0.1;
			switch($v16){
				case 0:
					$v21 = -$v21;
				case 1:
					$this->speedX = $v21;
					return 0;
				case 2:
					$v21 = -$v21;
				case 3:
					$this->speedY = $v21;
					return 0;
				case 4:
					$v21 = -$v21;
				case 5:
					$this->speedZ = $v21;
					return 0;
			}
			
			return 0;
		}
	}
	
	public function handleWaterMovement(){
		return $this->level->handleMaterialAcceleration($this->boundingBox, 0, $this);
	}
	
	public function updateEntityMovement(){
		//TODO custom update( method
		$this->speedY -= 0.04;
		//$this->noClip = false;
		$this->checkInTile($this->x, $this->y, $this->z);
		$this->move($this->speedX, $this->speedY, $this->speedZ);
		
		$var1 = (int)$this->x != (int)$this->lastX || (int)$this->y != (int)$this->lastY || (int)$this->z != (int)$this->lastZ;
		
		if($var1 || $this->counter % 25 == 0){
			$blockIDAt = $this->level->level->getBlockID(floor($this->x), floor($this->y), floor($this->z));
			if($blockIDAt == LAVA || $blockIDAt == STILL_LAVA){
				$this->speedY = 0.2;
				$this->speedX = (lcg_value() - lcg_value()) * 0.2;
				$this->speedZ = (lcg_value() - lcg_value()) * 0.2;
			}
			
			//$this->searchForOtherItemsNearby(); //not in vanilla 0.8.1
		}
		
		if($this->closed) return;
		
		$friction = 0.98;
		if($this->onGround){
			$friction = 0.588;
			$v3 = $this->level->level->getBlockID($this->x, floor($this->boundingBox->minY) - 1, $this->z);
			if($v3 > 0) $friction = StaticBlock::getSlipperiness($v3);
		}
		
		$this->speedX *= $friction;
		$this->speedY *= 0.98;
		$this->speedZ *= $friction;
		
		if($this->onGround) $this->speedY *= -0.5;
		
		if(abs($this->speedX) < self::MIN_POSSIBLE_SPEED) $this->speedX = 0;
		if(abs($this->speedZ) < self::MIN_POSSIBLE_SPEED) $this->speedZ = 0;
		if(abs($this->speedY) < self::MIN_POSSIBLE_SPEED) $this->speedY = 0;
		
		++$this->age;
		//TODO despawn after age >= 6000 ?; 
	}
}
