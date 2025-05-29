<?php

abstract class Animal extends Creature implements Ageable, Breedable{
	
	public $parent;
	public $inLove; //do NOT add it into metadata, it doesnt send it to player
	public $age;
	public $loveTimeout;
	
	
	
	public $closestPlayerThatCanFeedEID = false;
	public $closestPlayerThatCanFeedDist = INF;
	
	public function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setAge($data["Age"] ?? 0);
		if(isset($this->data["IsBaby"]) && $this->data["IsBaby"] && $this->getAge() >= 0){
			$this->setAge(-24000);
		}
		$this->searchForClosestPlayers = true;
	}
	
	public function isPlayerValid(Player $player){
		return $player->spawned && $this->isFood($player->getHeldItem()->id) && !$player->entity->dead;
	}
	
	public function handlePrePlayerSearcher(){
		parent::handlePrePlayerSearcher();
		if($this->closestPlayerThatCanFeedEID !== false){
			$player = $this->level->entityList[$this->closestPlayerThatCanFeedEID] ?? false;
			if($player === false || !$this->isPlayerValid($player->player)){
				$this->closestPlayerThatCanFeedEID = false;
				$this->closestPlayerThatCanFeedDist = INF;
			}else{
				$dist = ($this->x - $player->x)*($this->x - $player->x) + ($this->y - $player->y)*($this->y - $player->y) + ($this->z - $player->z)*($this->z - $player->z);
				$this->closestPlayerThatCanFeedDist = $dist;
			}
		}
	}
	
	public function handlePlayerSearcher(Player $player, $dist){
		parent::handlePlayerSearcher($player, $dist);
		
		if($this->closestPlayerThatCanFeedDist >= $dist){
			if($this->isPlayerValid($player)){
				$this->closestPlayerThatCanFeedDist = $dist;
				$this->closestPlayerThatCanFeedEID = $player->entity->eid;
			}
		}
	}
	
	public function harm($dmg, $cause = "generic", $force = false){
		$ret = parent::harm($dmg, $cause, $force);
		$this->inPanic |= ($ret && is_numeric($cause));
		$this->inLove = false;
		return $ret;
	}
	
	public function isBaby(){
		return $this->getAge() < 0;
	}
	
	public function breed(){
		if($this->server->dhandle("entity.animal.breed", ["parent" => $this]) !== false){
			$c = $this->spawnChild();
			$c->parent = $this; //TODO save eid to avoid memory leaks?
			$this->server->api->entity->spawnToAll($c);
			return $c;
		}
		return false;
	}
	
	public function resetInLove(){
		$this->inLove = 0;
	}
	
	public function canMate($ent){
		return $ent->eid != $this->eid && $this->type === $ent->type && $this->isInLove() && $ent->isInLove();
	}
	
	public function update($now){
		parent::update($now);
		
		if($this->loveTimeout > 0){
			--$this->loveTimeout;
		}
		
		$age = $this->getAge() + 1; //100 - fast. debug, 1 - normal
		if($age >= 0 && $this->isBaby()){
			$this->setAge($age);
			$this->updateMetadata();
		}else{
			$this->setAge($age);
		}
		if($this->isInLove() && !isset($this->level->entitiesInLove[$this->eid])){
			$this->level->entitiesInLove[$this->eid] = true;
		}elseif(!$this->isInLove() && isset($this->level->entitiesInLove[$this->eid])){
			unset($this->level->entitiesInLove[$this->eid]);
		}
	}
	
	public function getAge(){
		return $this->age;
	}
	
	public function setAge($i){
		$this->age = $i;
	}
	
	public function spawnChild()
	{
		return $this->server->api->entity->add($this->level, $this->class, $this->type, [
			"x" => $this->x + lcg_value() * mt_rand(-1, 1),
			"y" => $this->y,
			"z" => $this->z + lcg_value() * mt_rand(-1, 1),
			"IsBaby" => true,
			"Age" => -24000,
		]);
	}
	
	public function getMetadata(){
		$d = parent::getMetadata();
		$d[14]["value"] = $this->isBaby();
		return $d;
	}
	
	public function createSaveData(){
		$data = parent::createSaveData();
		$data["IsBaby"] = $this->isBaby();
		$data["Age"] = $this->getAge();
		return $data;
	}
	
	public function isInLove(){
		return $this->inLove > 0;
	}
	
	public function interactWith(Entity $e, $action){
		if($e->isPlayer() && $action === InteractPacket::ACTION_HOLD){
			$slot = $e->player->getHeldItem();
			if($this->isFood($slot->getID()) && $this->inLove <= 0 && !$this->isBaby() && $this->loveTimeout <= 0){
				$e->player->consumeSingleItem();
				$this->inLove = 600;
				return true;
			}
		}
		parent::interactWith($e, $action);
	}
	
	public function counterUpdate(){
		parent::counterUpdate();
		if($this->isInLove()){
			--$this->inLove;
		}
	}
	
	public function getDrops(){
		if($this->isBaby()){
			return [];
		}
		return parent::getDrops();
	}
}
