<?php

class TaskRangedAttack extends \TaskBase
{
	public $server;
	public $attackCounter = 0;
	public $seenTicks = 0;
	public function __construct($speed, $range){
		$this->speedMultiplier = $speed;
		$this->rangeSquared = $range*$range;
		$this->server = ServerAPI::request();
	}
	
	public function onStart(EntityAI $ai)
	{
		$this->selfCounter = 1;
		$this->ticksNoSeen = 0;
	}

	public function onEnd(EntityAI $ai)
	{
		$ai->entity->target = false;
		
	}

	public function onUpdate(EntityAI $ai)
	{
		if(!$this->isTargetValid($ai)){
			$this->reset();
			$this->onEnd($ai);
			return false;
		}
		--$this->attackCounter;
		if($ai->canSee($ai->entity->target)){
			++$this->seenTicks;
		}else{
			$this->seenTicks = 0;
		}
		
		
		$dist = $ai->entity->distanceSquared($ai->entity->target);
		
		if($dist > 100 || $this->seenTicks < 20){
			$ai->mobController->setMovingTarget($ai->entity->target->x, $ai->entity->target->y, $ai->entity->target->z, $this->speedMultiplier);
		}else{
			$ai->mobController->headYawIsYaw = true;
		}
		
		$ai->mobController->setLookPosition($ai->entity->target->x, $ai->entity->target->y + 0.12, $ai->entity->target->z, 10, $ai->entity->getVerticalFaceSpeed());
		
		if($this->attackCounter <= 0 && $this->seenTicks > 0){
			$this->rangedAttack($ai->entity, $ai->entity->target);
			$this->attackCounter = 60;
		}
	}
	
	public function rangedAttack($selfEntity, $target){
		$d = [
			"x" => $selfEntity->x,
			"y" => $selfEntity->y + 1.6,
			"z" => $selfEntity->z,
			"yaw" => $selfEntity->yaw,
			"pitch" => $selfEntity->pitch
		];
		/**
		 * @var Arrow $arrow
		 */
		$arrow = $this->server->api->entity->add($selfEntity->level, ENTITY_OBJECT, OBJECT_ARROW, $d);
		$arrow->shotByEntity = true;
		$arrow->shooterEID = $selfEntity->eid;
		$posY = ($selfEntity->y + $selfEntity->getEyeHeight() - 0.1);
		$diffX = $target->x - $selfEntity->x;
		$diffY = ($target->boundingBox->minY + ($target->height / 3)) - $posY;
		$diffZ = $target->z - $selfEntity->z;
		$v12 = sqrt($diffX * $diffX + $diffZ * $diffZ);
		if($v12 >= 0.0000001){
			$yaw = ((atan2($diffZ, $diffX) * 180) / M_PI) - 90;
			$pitch = -((atan2($diffY, $v12) * 180) / M_PI);
			$v16 = $diffX / $v12;
			$v18 = $diffZ / $v12;
			$arrow->x = $selfEntity->x + $v16;
			$arrow->y = $posY;
			$arrow->z = $selfEntity->z + $v18;
			$arrow->yaw = $yaw;
			$arrow->pitch = $pitch;
			$v20 = $v12 * 0.2;
			$arrow->shoot($diffX, $diffY + $v20, $diffZ, 1.6, 12);
			$this->server->api->entity->spawnToAll($arrow);
		}
	}
	
	
	public function isTargetValid(EntityAI $ai){
		$e = $ai->entity;
		if($e->target instanceof Entity && !$e->target->closed && !$e->target->dead){
			$t = $e->target;
			$xDiff = ($t->x - $e->x);
			$yDiff = ($t->y - $e->y);
			$zDiff = ($t->z - $e->z);
			return ($xDiff*$xDiff + $yDiff*$yDiff + $zDiff*$zDiff) <= $this->rangeSquared;
		}
		return false;
	}
	
	public function tryTargeting(EntityAI $ai){
		$e = $ai->entity;
		if($e->target instanceof Entity){
			$t = $e->target;
			$xDiff = ($t->x - $e->x);
			$yDiff = ($t->y - $e->y);
			$zDiff = ($t->z - $e->z);
			if(($xDiff*$xDiff + $yDiff*$yDiff + $zDiff*$zDiff) <= $this->rangeSquared){
				return true;
			}
		}

		$closestTarget = $e->closestPlayerToAttackDist <= $this->rangeSquared ? $e->level->entityList[$e->closestPlayerToAttackEID] : null;
		
		if($closestTarget != null){
			$e->target = $closestTarget; //TODO dont save entity object ?
			return true;
		}
		return false;
	}
	
	
	public function canBeExecuted(EntityAI $ai)
	{
		return $this->tryTargeting($ai);
	}

}

