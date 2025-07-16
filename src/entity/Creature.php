<?php

abstract class Creature extends Living{
	const CLASS_TYPE = ENTITY_MOB;
	
	public $inPanic;
	public $closestPlayerEID = false;
	public $closestPlayerDist = INF;
	
	
	public function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->inPanic = false; //force for now
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 1, "generic", allowHarm: false);
		$this->enableAutojump = true;
		$this->searchForClosestPlayers = true;
	}
	
	public function update($now){
		$this->handlePrePlayerSearcher();
		return parent::update($now);
	}
	
	
	public function handlePrePlayerSearcher(){
		parent::handlePrePlayerSearcher();
		if($this->closestPlayerEID !== false){
			$player = $this->level->entityList[$this->closestPlayerEID] ?? false;
			if($player === false){
				$this->closestPlayerEID = false;
				$this->closestPlayerDist = INF;
			}else{
				$dist = ($this->x - $player->x)*($this->x - $player->x) + ($this->y - $player->y)*($this->y - $player->y) + ($this->z - $player->z)*($this->z - $player->z);
				$this->closestPlayerDist = $dist;
			}
		}
	}
	
	public function handlePlayerSearcher(Player $player, $dist){
		parent::handlePlayerSearcher($player, $dist);
		if($this->closestPlayerDist >= $dist){
			$this->closestPlayerEID = $player->entity->eid;
			$this->closestPlayerDist = $dist;
		}
	}
	
	public function createSaveData(){
		$data = parent::createSaveData();
		$data["State"] = @$this->getState();
		return $data;
	}
	
	public function getSpeedModifer(){
		return 0.7;
	}
	public function getArmorValue(){
		return 0;
	}
	public function spawn(Player $player){
		if($player->eid === $this->eid or $this->closed !== false or ($player->level !== $this->level and $this->class !== ENTITY_PLAYER)){
			return false;
		}
		if(!$player->hasEntity($this)){
			$player->addEntity($this);
			console("ADD MOB");
			$pk = new AddMobPacket;
			$pk->eid = $this->eid;
			$pk->type = $this->type;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->metadata = $this->getMetadata();
			$player->entityQueueDataPacket($pk);
			
			$pk = new SetEntityMotionPacket;
			$pk->eid = $this->eid;
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
