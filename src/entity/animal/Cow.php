<?php

class Cow extends Animal{
	const TYPE = MOB_COW;
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->setSize(0.9, 1.3);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 10, "generic");
		$this->setName("Cow");
		$this->setSpeed(0.2);
		$this->ai->addTask(new TaskRandomWalk(0.8));
		$this->ai->addTask(new TaskLookAtPlayer(6));
		$this->ai->addTask(new TaskPanic(1.5));
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskTempt(1.0));
		$this->ai->addTask(new TaskMate(1.0));
		$this->ai->addTask(new TaskFollowParent(1.1));
	}
	
	public function isFood($id){
		return $id === WHEAT;
	}
	
	public function interactWith(Entity $e, $action){
		if($e->isPlayer() && $action === InteractPacket::ACTION_HOLD){
			$slot = $e->player->getHeldItem();
			if($slot->getID() === BUCKET && $slot->getMetadata() === 0){
				$e->player->consumeSingleItem();
				$e->player->addItem(BUCKET, 1, 1, true);
				return true;
			}
		}
		parent::interactWith($e, $action);
	}
	
	public function getDrops(){
		return $this->isBaby() ? parent::getDrops() : [	
			[LEATHER, 0, mt_rand(0,2)],
			[($this->fire > 0 ? STEAK : BEEF), 0, mt_rand(0, 3)]
		];
	}

}