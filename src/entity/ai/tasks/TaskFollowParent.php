<?php

class TaskFollowParent extends TaskBase
{
	public $speedMultiplier;
	public function __construct($speed){
		$this->speedMultiplier = $speed;
	}
	
	public function onStart(EntityAI $ai)
	{
		$this->selfCounter = 1;
	}

	public function onEnd(EntityAI $ai)
	{
		$this->selfCounter = 0;
	}

	public function onUpdate(EntityAI $ai)
	{
		if(!$ai->entity->isBaby() || !($ai->entity->parent instanceof Entity) || $ai->entity->parent->dead){
			$this->reset();
			$ai->entity->parent = null;
			return false;
		}
		
		$dist = $ai->entity->distanceSquared($ai->entity->parent);
		
		if($dist > 256 || $dist < 9){ //3-16 blocks
			$this->reset();
			return false;
		}
		$parent = $ai->entity->parent;
		
		$ai->mobController->setLookPosition($parent->x, $parent->y + $parent->getEyeHeight(), $parent->z, 10, $ai->entity->getVerticalFaceSpeed());
		$ai->mobController->setMovingTarget($parent->x, $parent->y, $parent->z, $this->speedMultiplier);
	}
	
	public function canBeExecuted(EntityAI $ai)
	{
		return $ai->entity->isBaby() && ($ai->entity->parent instanceof Entity) && !$ai->entity->parent->dead; //TODO find a new parent if the current one is dead
	}
}

