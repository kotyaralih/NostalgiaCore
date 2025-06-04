<?php
class PigZombie extends Monster{ //TODO extends zombie
	const TYPE = MOB_PIGMAN;
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->setSize(0.3, 1.95);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 12, "generic", allowHarm: false);
		$this->setName("Pigman");
		$this->setSpeed(0.25);
		$this->isImmuneToFire = true;
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskRandomWalk(1.0));
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskAttackPlayer(1.0, 16));
	}
	public function getAttackDamage(){
		return 5;
	}
	public function getDrops(){
		return [
			[COOKED_PORKCHOP, 0, mt_rand(0,2)],
			[GOLD_INGOT, 0, mt_rand(0,1)]
		];
	}
}