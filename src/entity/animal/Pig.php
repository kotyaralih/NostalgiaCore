<?php
class Pig extends Animal implements Rideable{
	const TYPE = MOB_PIG;
	
	public function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->setSize(0.9, 0.9);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 10, "generic");
		$this->server = ServerAPI::request();
		$this->setName("Pig");
		$this->setSpeed(0.25);
		
		$this->ai->addTask(new TaskRandomWalk(1.0));
		$this->ai->addTask(new TaskLookAtPlayer(6));
		$this->ai->addTask(new TaskPanic(1.5));
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskTempt(1.0));
		$this->ai->addTask(new TaskMate(1.0));
		$this->ai->addTask(new TaskFollowParent(1.1));
	}
	/**
	 * @return boolean
	 */
	public function isSaddled(){
		return (boolean) $this->getState();
	}
	
	/**
	 * @param boolean $value
	 */
	public function setSaddled($value = null){
		$this->setState($value === null ? !$this->getState() : $value);
	}
	
	public function canRide($e){
		return $this->isSaddled() && $this->linkedEntity == 0 && $e->linkedEntity == 0;
	}
	
	public function updateEntityMovement(){
		/*if($this->linkedEntity != 0){
			$e = $this->level->entityList[$this->linkedEntity] ?? false;
			if($e instanceof Entity){
				$this->setAIMoveSpeed($this->getSpeed());
				$this->moveStrafing = $e->player->moveStrafe;
				$this->moveForward = $e->player->moveForward;
				$this->yaw = $e->headYaw;
			}
		}*/
		
		parent::updateEntityMovement();
	}
	
	public function interactWith(Entity $e, $action)
	{
		if($e->isPlayer() && $action === InteractPacket::ACTION_HOLD){
			$slot = $e->player->getHeldItem();
			if($this->canRide($e)){
				$e->setRiding($this);
				return true;
			}
			
			if($slot->getID() === SADDLE){
				if(!$this->isSaddled()){
					$e->player->removeItem($slot->getID(), 0, 1, send: true);
					$this->setSaddled(1);
				}
				return true; //avoid further interactions
			}
			
		}
		return parent::interactWith($e, $action);
	}
	
	public function isFood($id){
		return $id === POTATO || $id === CARROT || $id === BEETROOT;
	}
	
	public function getDrops(){
		return $this->isBaby() ? parent::getDrops() : ($this->isSaddled() ? [
			[($this->fire > 0 ? COOKED_PORKCHOP : RAW_PORKCHOP), 0, mt_rand(0,2)],
			[SADDLE, 0, 1]
		] : [
			[($this->fire > 0 ? COOKED_PORKCHOP : RAW_PORKCHOP), 0, mt_rand(0,2)]
		]);
	}
}
