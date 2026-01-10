<?php

class TaskMate extends TaskBase
{
	public $speedMultiplier;
	public function __construct($speed){
		$this->speedMultiplier = $speed;
	}
	
	public $targetMateID;
	
	public function onStart(EntityAI $ai)
	{
		$this->selfCounter = 60;
	}

	public function onEnd(EntityAI $ai)
	{
		$this->selfCounter = 0;
		$this->targetMateID = null;
	}

	public function onUpdate(EntityAI $ai)
	{
		$movTo = $ai->entity->level->entityList[$this->targetMateID] ?? false;
		if(!($movTo instanceof Entity) || $movTo->dead || !$movTo->isInLove()){
			$this->reset();
			$this->onEnd($ai);
			return;
		}
		
		
		$ai->mobController->setLookPosition($movTo->x, $movTo->y + $movTo->getEyeHeight(), $movTo->z, 10, $ai->entity->getVerticalFaceSpeed());
		$ai->mobController->setMovingTarget($movTo->x, $movTo->y, $movTo->z, $this->speedMultiplier);
		
		--$this->selfCounter;
		if($this->selfCounter <= 0 && $ai->entity->distanceSquared($movTo) < 9){
			$this->createBaby($ai, $movTo);
		}
	}
	
	public function createBaby(EntityAI $ai, Entity $entpar2){
		/**
		 * @var Animal $baby
		 */
		$baby = $ai->entity->breed($entpar2);
		//this.theAnimal.setGrowingAge(6000); TODO growing age?
		//this.targetMate.setGrowingAge(6000);
		
		$ai->entity->loveTimeout = 6000;
		$entpar2->loveTimeout = 6000;
		
		$ai->entity->resetInLove();
		$entpar2->resetInLove();
		$baby->setAge(-24000);
	}
	
	public function findMate(EntityAI $ai){
		$distance = 8.0*8.0;
		$minDist = $distance; //TODO check bb intersection?
		$matedEID = null;
		
		foreach($ai->entity->level->entitiesInLove as $eid => $_){
			$e = $ai->entity->level->entityList[$eid] ?? false;
			if($e instanceof Entity){
				$d = $ai->entity->distanceSquared($e);
				if($ai->entity->canMate($e) && $d < $minDist){
					$minDist = $d;
					$matedEID = $e->eid;
				}
			}
		}
		return $matedEID;
	}
	
	public function canBeExecuted(EntityAI $ai)
	{
		return $ai->entity->isInLove() && ($this->targetMateID = $this->findMate($ai)) != null;
	}
}

