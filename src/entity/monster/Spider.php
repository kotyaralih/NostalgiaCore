<?php
class Spider extends Monster{
	const TYPE = MOB_SPIDER;
	
	function __construct(Level $level, $eid, $class, $type = 0, $data = array()){
		$this->setSize(1.4, 0.9);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 8, "generic", allowHarm: false);
		$this->setName("Spider");
		$this->setSpeed(0.3);
		$this->ai->addTask(new TaskRandomWalk(1.2));
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskAttackPlayer(1.2, 16)); 
	}
	
	public function getAttackDamage(){
		return 2;
	}
	
	public function attackEntity($entity, $distance){
		if($distance > 2.0 && $distance < 6.0){
			if($this->onGround){
				$diffX = $entity->x - $this->x;
				$diffZ = $entity->z - $this->z;
				$dist = sqrt($diffX*$diffX + $diffZ*$diffZ);
				
				$this->speedX = $diffX / $dist*0.5*0.8 + $this->speedX*0.2;
				$this->speedZ = $diffZ / $dist*0.5*0.8 + $this->speedZ*0.2;
				$this->speedY = 0.4;
			}
			return false;
		}else{
			return parent::attackEntity($entity, $distance);
		}
		
	}
	
	public function isOnLadder(){
		return $this->isCollidedHorizontally;
	}
	
	public function getDrops(){
		return [
			[STRING, 0, mt_rand(0,2)]
		];
	}
}