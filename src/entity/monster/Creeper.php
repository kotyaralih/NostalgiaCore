<?php
class Creeper extends Monster{
	const TYPE = MOB_CREEPER;
	const EXPL_TIME = 30;
	public $timeUntilExplode;
	public $activatedByPlayer = false;
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->setSize(0.6, 1.7);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 16, "generic");
		$this->setName("Creeper");
		$this->ignited = 0;
		$this->setSpeed(0.25);
		$this->timeUntilExplode = $this->isIgnited() ? self::EXPL_TIME : 0;
		
		$this->ai->addTask(new TaskRandomWalk(1.0));
		$this->ai->addTask(new TaskAttackPlayer(1.25, 16));
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
	}
	
	public function getAttackDamage(){
		return 0;
	}
	
	public function setIgnited($v = null){
		$this->setState($v === null ? !$this->getState() : $v);	
	}
	
	/**
	 * @return boolean
	 */
	public function isIgnited(){
		return $this->getState() > 0;
	}
	
	public function interactWith(Entity $e, $action){
		if($e->isPlayer() && $action === InteractPacket::ACTION_HOLD){
			$slot = $e->player->getHeldItem();
			if($slot->getID() === FLINT_AND_STEEL && !$this->isIgnited()){
				$slot->hurtAndBreak(1, $e->player);
				$this->ignite();
				return true;
			}
		}
		return parent::interactWith($e, $action);
	}
	
	public function ignite(){
		$this->setIgnited(1);
		$this->timeUntilExplode = self::EXPL_TIME;
	}
	
	public function attackEntity($entity, $distance){
		if(Utils::distance_noroot($entity, $this) <= 49 && !$this->isIgnited() && $this->ai->canSee($entity)){
			$this->ignite();
			return true;
		}
		return false;
	}
	public function update($now){
		if($this->isIgnited() && $this->target instanceof Entity && Utils::distance_noroot($this->target, $this) > 49){ //TODO move somewhere else
			$this->setIgnited(-1); //broken in vanilla too
			$this->timeUntilExplode = 0;
		}
		if($this->timeUntilExplode === 1){
			$this->explode();
		}
		if($this->timeUntilExplode >= 0){
			--$this->timeUntilExplode;
		}
		parent::update($now);
	}

	public function explode()
	{
		if($this->closed || $this->dead){
			return false;
		}
		$this->setIgnited(0);
		$explosion = new Explosion($this, 3);
		$this->close();
		$explosion->explode();
	}
	
	public function getDrops(){
		return [
			[GUNPOWDER, 0, mt_rand(0,2)]
		];
	}
}
