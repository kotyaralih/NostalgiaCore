<?php

class PrimedTNT extends Entity{
	const TYPE = OBJECT_PRIMEDTNT;
	const CLASS_TYPE = ENTITY_OBJECT;
	public function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		if(!isset($this->data["fuse"])){
			$this->data["fuse"] = 0;
		}
		$this->hasGravity = true;
		$this->gravity = 0.04;
		$this->setSize(0.98, 0.98);
		$this->yOffset = $this->height / 2;
		$this->setHealth(10000000, "generic", allowHarm: false);
	}
	
	public function isPickable(){
		return !$this->dead;
	}
	
	public function getMetadata(){
		$d = parent::getMetadata();
		$d[16]["value"] = (int) $this->data["fuse"];
		return $d;
	}
	public function createSaveData(){
		$data = parent::createSaveData();
		
		$data["fuse"] = $this->data["fuse"];
		$data["power"] = $this->data["power"];
		
		return $data;
	}
	
	public function update($now){
		if($this->closed) return;
		$this->lastX = $this->x;
		$this->lastY = $this->y;
		$this->lastZ = $this->z;
		
		$this->speedY -= 0.04;
		$this->move($this->speedX, $this->speedY, $this->speedZ);
		$this->speedX *- 0.98;
		$this->speedY *= 0.98;
		$this->speedZ *= 0.98;
		
		if($this->onGround){
			$this->speedX *= 0.7;
			$this->speedZ *= 0.7;
			
			$this->speedY *= -0.5;
		}
		
		$tickDiff = ($now - $this->lastUpdate) / 0.05;
		
		$this->data["fuse"] -= $tickDiff;
		$this->updateMetadata();
		if($this->data["fuse"] <= 0){
			$this->close();
			$explosion = new Explosion($this, $this->data["power"]);
			$explosion->explode();
		}
		
		$this->lastUpdate = $now;
	}
	
	public function spawn(Player $player){
		if(!$player->hasEntity($this)){
			$player->addEntity($this);
			$pk = new AddEntityPacket;
			$pk->eid = $this->eid;
			$pk->type = $this->type;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->did = 1;
			$pk->speedX = $this->speedX;
			$pk->speedY = $this->speedY;
			$pk->speedZ = $this->speedZ;
			$player->entityQueueDataPacket($pk);
			$this->sendLinkPackets($player);
			return true;
		}
		return false;
		
	}
}