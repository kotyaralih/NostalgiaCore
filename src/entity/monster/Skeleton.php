<?php
class Skeleton extends Monster{
	const TYPE = MOB_SKELETON;
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->setSize(0.6, 1.99);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 10, "generic", allowHarm: false);
		$this->setName("Skeleton");
		$this->ai->removeTask("TaskAttackPlayer");
		$this->setSpeed(0.25);
		
		$this->ai->addTask(new TaskRandomWalk(1.0));
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskRangedAttack(1.0, 16));
	}
	
	public function getAttackDamage(){
		return 0;
	}
	
	public function updateBurning(){
		if($this->fire > 0 || !$this->level->isDay() || $this->inWater){
			return false;
		}
		//bad fix for burning in water
		[$block, $meta] = $this->level->level->getBlock((int)$this->x, (int)$this->y, (int)$this->z);
		if($block == WATER || $block == STILL_WATER){
			$v16 = ((int)$this->y + 1) - LiquidBlock::getPercentAir($meta);
			if($this->boundingBox->intersectsWith(new AxisAlignedBB((int)$this->x, (int)$this->y, (int)$this->z, (int)$this->x + 1, $v16, (int)$this->z + 1))){
				return false;
			}
		}
		
		for($y = (int)$this->y; $y < 129; $y++){
			$block = $this->level->level->getBlockID($this->x, $y, $this->z);
			if(StaticBlock::getIsSolid($block)){
				return false;
			}
		}
		if($block === AIR){
			$oldFire = $this->fire;
			$this->fire = 160; //Value from 0.8.1
			if(($oldFire > 0 && $this->fire <= 0) || ($oldFire <= 0 && $this->fire > 0)){
				$this->updateMetadata(); //TODO rewrite metadata
			}
			return true;
		}else{
			return false;
		}
	}
	
	public function updateEntityMovement(){
		$this->updateBurning();
		return parent::updateEntityMovement();
	}
	
	public function getDrops(){
		return [
			[ARROW, 0, mt_rand(0,2)],
			[BONE, 0, mt_rand(0,2)]
		];
	}
}