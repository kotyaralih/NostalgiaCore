<?php

class FallingSand extends Entity{
	const TYPE = FALLING_SAND;
	const CLASS_TYPE = ENTITY_FALLING;
	
	public $fallTime = 0;
	
	public function __construct($level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth(PHP_INT_MAX, "generic", allowHarm: false);
		$this->height = 0.98;
		$this->width = 0.98;
		$this->yOffset = $this->height / 2;
		$this->hasGravity = true;
		$this->gravity = 0.04;
	}
	
	public function isPickable(){
		return !$this->dead;
	}
	public function spawn($player)
	{
		$pk = new AddEntityPacket();
		$pk->eid = $this->eid;
		$pk->type = $this->type;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->speedX;
		$pk->speedY = $this->speedY;
		$pk->speedZ = $this->speedZ;
		$pk->did = -$this->data["Tile"];
		$player->dataPacketAlwaysRecover($pk);
	}
	
	public function update($now){
		if($this->closed) return;
		
		if( $this->data["Tile"] == AIR) {
			$this->close();
			return;
		}
		//$level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
		if($this->fallTime == 0){
			$x = floor($this->x);
			$y = floor($this->y);
			$z = floor($this->z);
			if($this->level->level->getBlockID($x, $y, $z) != $this->data["Tile"]){
				$this->close();
				return;
			}
			$this->level->fastSetBlockUpdate($x, $y, $z, 0, 0, true);
		}
		
		$this->lastX = $this->x;
		$this->lastY = $this->y;
		$this->lastZ = $this->z;
		++$this->fallTime;
		$this->speedY -= 0.04;
		$this->move($this->speedX, $this->speedY, $this->speedZ);
		
		$this->speedX *= 0.98;
		$this->speedY *= 0.98;
		$this->speedZ *= 0.98;
		
		$x = floor($this->x);
		$y = floor($this->y);
		$z = floor($this->z);
		
		if(($blockAt = $this->level->level->getBlockID($x, $y, $z)) != 0){
			//TODO vanilla-like checking?
			if(StaticBlock::getIsTransparent($blockAt) && !StaticBlock::getIsLiquid($blockAt)){
				$this->close();
				ServerAPI::request()->api->entity->drop($this, BlockAPI::getItem($this->data["Tile"], 0, 1)); //TODO add metadata
				return;
			}
		}
		if($this->onGround){
			$this->speedX *= 0.7;
			$this->speedZ *= 0.7;
			$this->speedY *= -0.5;
			$this->close();
			//TODO vanilla-like checking?
			$this->level->fastSetBlockUpdate($x, $y, $z, $this->data["Tile"], 0, true); //TODO add metadata
		}
	}
}
