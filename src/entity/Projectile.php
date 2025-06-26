<?php
abstract class Projectile extends Entity{
	const CLASS_TYPE = ENTITY_OBJECT;
	public $shooterEID;
	public $shake = 0;
	public $inGround = false;
	public $ticksInAir = 0, $ticksInGround = 0;
	public $xTile = 0, $yTile = 0, $zTile = 0;
	public $inTile = false;
	
	public function __construct(Level $level, $eid, $class, $type = 0, $data = [])
	{
		parent::__construct($level, $eid, $class, $type, $data);
		$this->gravity = 0.03;
		$this->setSize(0.25, 0.25);
		
		$this->x -= cos($this->yaw / 180 * M_PI) * 0.16;
		$this->y -= 0.1;
		$this->z -= cos($this->yaw / 180 * M_PI) * 0.16;
		$this->boundingBox->setBounds($this->x - $this->radius, $this->y - $this->yOffset /*+ $this->ySize*/, $this->z - $this->radius, $this->x + $this->radius, $this->y - $this->yOffset + $this->height /*+ $this->ySize*/, $this->z + $this->radius);
		$shooter = $data["shooter"] ?? false;
		if($shooter !== false) $shooter = $this->level->entityList[$shooter] ?? false;
		$this->inTile = $data["inTile"] ?? $this->inTile;
		$this->inGround = $data["inGround"] ?? $this->inGround;
		$this->xTile = $data["xTile"] ?? $this->xTile;
		$this->yTile = $data["yTile"] ?? $this->yTile;
		$this->zTile = $data["zTile"] ?? $this->zTile;
		
		if($shooter instanceof Entity){
			$this->shooterEID = $shooter->eid;
			if($shooter->isPlayer()){
				$shootX = $data["shootX"] ?? 0;
				$shootY = $data["shootY"] ?? 0;
				$shootZ = $data["shootZ"] ?? 0;
				$throwPower = 1.5;
				$this->shoot($shootX, $shootY, $shootZ, $throwPower, 1.0);
			}else{
				/*
				 * TODO shoot if not player
				 * $pitch = $this->pitch;
				$v18 = cos($pitch/180 * 3.1416);
				$v19 = cos($pitch/180 * 3.1416); //this one uses $pitch+getThrowUpAngleOffset, but last is 0
				$throwPower = 1.5;
				$this->shoot(, , , $throwPower, 1.0);*/
			}
		}
		
	}
	
	public function shoot($d, $d1, $d2, $f, $f1){ //unchecked, may be not same as arrow
		$f2 = sqrt($d * $d + $d1 * $d1 + $d2 * $d2);
		$d /= $f2;
		$d1 /= $f2;
		$d2 /= $f2;
		$d += $this->random->nextGaussian() * 0.0075 * $f1;
		$d1 += $this->random->nextGaussian() * 0.0075 * $f1;
		$d2 += $this->random->nextGaussian() * 0.0075 * $f1;
		$d *= $f;
		$d1 *= $f;
		$d2 *= $f;
		$this->speedX = $d;
		$this->speedY = $d1;
		$this->speedZ = $d2;
		$f3 = sqrt($d * $d + $d2 * $d2);
		$this->yaw = (atan2($d, $d2) * 180) / M_PI;
		$this->pitch = (atan2($d1, $f3) * 180) / M_PI;
		$this->updatePosition();
	}
	
	public function update($now){
		$this->lastX = $this->x;
		$this->lastY = $this->y;
		$this->lastZ = $this->z;
		$this->lastPitch = $this->pitch;
		$this->lastYaw = $this->yaw;
		$this->lastSpeedX = $this->speedX;
		$this->lastSpeedY = $this->speedY;
		$this->lastSpeedZ = $this->speedZ;
		$this->needsUpdate = true;
		$this->handleWaterMovement(); //TODO: maybe just call parent::update(); ?
		if($this->fire > 0){
			if(!$this->isImmuneToFire){
				if(($this->fire % 20) == 0){
					$this->harm(1, "burning");
				}
				--$this->fire;
			}else{
				$this->fire -= 4;
				if($this->fire <= 0) $this->fire = 0;
			}
			
			if($this->fire <= 0){
				$this->updateMetadata();
			}
		}
		
		if($this->handleLavaMovement()){
			if(!$this->isImmuneToFire){
				$this->harm(4, "fire");
				$oldOnFire = $this->fire;
				if($oldOnFire < 20*30) $this->fire = 20*30; //30 seconds
			}
		}
		
		if($this->isInVoid()){
			$this->outOfWorld();
		}
		//Entity::update end
		
		if($this->shake > 0) --$this->shake;
		
		if(!$this->inGround){
			++$this->ticksInAir;
			goto CHECK_COLLISIONS; //TODO dont use gotos
		}
		
		$blockID = $this->level->level->getBlockID($this->xTile, $this->yTile, $this->zTile);
		if($this->inTile != $blockID){
			$this->speedX += (lcg_value() * 0.2);
			$this->speedY += (lcg_value() * 0.2);
			$this->speedZ += (lcg_value() * 0.2);
			$this->inGround = false;
			$this->ticksInAir = $this->ticksInGround = 0;
			CHECK_COLLISIONS:
			
			$start = new Vector3($this->x, $this->y, $this->z);
			$end = new Vector3($this->x + $this->speedX, $this->y + $this->speedY, $this->z + $this->speedZ);
			/**
			 * @var MovingObjectPosition $v4
			 */
			$v4 = $this->level->rayTraceBlocks($start, $end);
			$start = new Vector3($this->x, $this->y, $this->z); //TODO remove?
			if($v4 != null){
				$end = new Vector3($v4->hitVector->x, $v4->hitVector->y, $v4->hitVector->z);
			}else{
				$end = new Vector3($this->x + $this->speedX, $this->y + $this->speedY, $this->z + $this->speedZ);
			}
			
			$entities = $this->level->getEntitiesInAABB($this->boundingBox->addCoord($this->speedX, $this->speedY, $this->speedZ)->expand(1, 1, 1));
			$bestDist = 0;
			$bestEnt = null;
			
			foreach($entities as $eid => $ent){
				if($eid != $this->eid && $ent->isPickable() && ($eid != $this->shooterEID || $this->ticksInAir >= 5)){
					
					$v12 = $ent->boundingBox->expand(0.3, 0.3, 0.3);
					$v13 = $v12->calculateIntercept($start, $end);
					
					if($v13 != null){
						$dist = $start->distance($v13->hitVector);
						
						if($dist < $bestDist || $bestDist == 0){
							$bestEnt = $ent;
						}
					}
				}
			}
			
			if($bestEnt != null){
				$v4 = MovingObjectPosition::fromEntity($bestEnt);
			}
			
			if($v4 != null){
				$this->onHit($v4);
			}
			$this->x += $this->speedX;
			$this->y += $this->speedY;
			$this->z += $this->speedZ;
			$v21 = sqrt($this->speedX*$this->speedX + $this->speedZ*$this->speedZ);
			$this->yaw = atan2($this->speedX, $this->speedZ) * 180 / M_PI;
			$this->pitch = atan2($this->speedY, $v21) * 180 / M_PI;
			
			$this->pitch = $this->lastPitch + ($this->pitch - $this->lastPitch) * 0.2;
			$this->yaw = $this->lastYaw + ($this->yaw - $this->lastYaw) * 0.2;
			
			$v24 = $this->inWater ? 0.8 : 0.99;
			$this->speedX *= $v24;
			$this->speedY *= $v24;
			$this->speedZ *= $v24;
			$this->speedY -= $this->gravity;
			$v7 = $this->width / 2;
			$v8 = $this->height;
			$this->boundingBox->setBounds($this->x - $v7, $this->y - $this->yOffset /*+ $this->ySize*/, $this->z - $v7, $this->x + $v7, $this->y - $this->yOffset + $v8 /*+ $this->ySize*/, $this->z + $v7);
		}
	}
	
	public function onHit(MovingObjectPosition $hitResult){
		
	}
	public function createSaveData(){
		$data = parent::createSaveData();
		
		$data["inTile"] = $this->inTile;
		$data["inGround"] = $this->inGround;
		$data["xTile"] = $this->xTile;
		$data["yTile"] = $this->yTile;
		$data["zTile"] = $this->zTile;
		
		return $data;
	}
	
	public function spawn($player)
	{
		$pk = new AddEntityPacket();
		$pk->eid = $this->eid;
		$pk->type = $this->type;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->speedX;
		$pk->speedY = $this->speedY;
		$pk->speedZ = $this->speedZ;
		$pk->did = 0;
		$player->entityQueueDataPacket($pk);
		return true;
	}
	
}