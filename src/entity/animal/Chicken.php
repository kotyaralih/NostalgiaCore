<?php

class Chicken extends Animal{
	const TYPE = MOB_CHICKEN;
	public $timeUntilEgg;
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->timeUntilEgg = mt_rand(0,6000) + 6000;
		$this->setSize(0.4, 0.7);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 4, "generic", allowHarm: false);
		$this->setName('Chicken');
		$this->setSpeed(0.25);
		
		$this->ai->addTask(new TaskRandomWalk(1.0));
		$this->ai->addTask(new TaskLookAtPlayer(6));
		$this->ai->addTask(new TaskPanic(1.5));
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskTempt(1.0));
		$this->ai->addTask(new TaskMate(1.0));
		$this->ai->addTask(new TaskFollowParent(1.0));
	}
	public function isFood($id){
		return $id === PUMPKIN_SEEDS || $id === MELON_SEEDS || $id === BEETROOT_SEEDS || $id === WHEAT_SEEDS;
	}
	
	public function updateEntityMovement(){
		parent::updateEntityMovement();
		
		if(!$this->onGround && $this->speedY < 0) $this->speedY *= 0.6;
	
		if($this->timeUntilEgg-- <= 0 && !$this->isBaby()){
			$this->dropAnEgg();
			$this->timeUntilEgg = mt_rand(0,6000) + 6000;
		}
	}
	
	public function fall(){} //chickens have no fall dmg?
	
	public function dropAnEgg(){
		if($this->closed){
			return;
		}
		ServerAPI::request()->api->entity->drop(new Position($this->x + 0.5, $this->y, $this->z + 0.5, $this->level), BlockAPI::getItem(EGG, 0, 1));
	}
	
	public function getDrops(){
		return $this->isBaby() ? parent::getDrops() : [
			[FEATHER, 0, mt_rand(0,2)],
			[$this->fire > 0 ? COOKED_CHICKEN : RAW_CHICKEN, 0, 1]
		];
	}

}
