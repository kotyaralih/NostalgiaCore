<?php

class Arrow extends Entity{
	const TYPE = OBJECT_ARROW;
	const CLASS_TYPE = ENTITY_OBJECT;
	
	public $shooterEID = 0;
	public $xTile, $yTile, $zTile;
	public $inTile, $inData;
	public $shake;
	public $inGround;
	public $airTicks = 0;
	public $criticial = false;
	public $groundTicks = 0;
	public $shotByPlayer = false;
	public $shotByEntity = false;
	
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		$this->gravity = 0.05;
		$this->setSize(0.5, 0.5);
		$this->setName("Arrow");
		$shooter = $data["shooter"] ?? false;
		if($shooter !== false){
			$this->shooterEID = $shooter;
			$this->shotByPlayer = $level->entityList[$shooter]->isPlayer();
		}
		$this->airTicks = $this->groundTicks = 0;
		$this->inTile = $data["inTile"] ?? $this->inTile;
		$this->inData = $data["inData"] ?? $this->inData;
		$this->inGround = $data["inGround"] ?? $this->inGround;
		$this->xTile = $data["xTile"] ?? $this->xTile;
		$this->yTile = $data["yTile"] ?? $this->yTile;
		$this->zTile = $data["zTile"] ?? $this->zTile;
		$this->shotByPlayer = $data["shotByPlayer"] ?? $this->shotByPlayer;
		
		//$this->server->schedule(1210, array($this, "update")); //Despawn
	}
	public function createSaveData(){
		$data = parent::createSaveData();
		
		$data["inTile"] = $this->inTile;
		$data["inData"] = $this->inData;
		$data["inGround"] = $this->inGround;
		$data["xTile"] = $this->xTile;
		$data["yTile"] = $this->yTile;
		$data["zTile"] = $this->zTile;
		$data["shotByPlayer"] = $this->shotByPlayer;
		return $data;
	}
	
	public function shoot($d, $d1, $d2, $f, $f1){ //original name from 0.8.1 IDA decompilation, var names are taken from b1.7.3
		$f2 = sqrt($d * $d + $d1 * $d1 + $d2 * $d2);
		$d /= $f2;
		$d1 /= $f2;
		$d2 /= $f2;
		$d += $this->random->nextGaussian() * 0.0075 * $f1; //0.0074999998323619366 replaced with 0.0075
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
		//$this->update();
		$this->groundTicks = 0;
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
		
		
		if($this->lastPitch == $this->lastYaw && $this->lastYaw == 0){
			$v1 = sqrt($this->speedX*$this->speedX + $this->speedZ*$this->speedZ);
			$this->lastYaw = $this->yaw = atan2($this->speedX, $this->speedZ) * 180 / M_PI;
			$this->lastPitch = $this->pitch = atan2($this->speedY, $v1) * 180 / M_PI;
		}
		
		if($this->shake > 0){
			--$this->shake;
		}
		
		if($this->inGround){
			[$blockID, $blockMeta] = $this->level->level->getBlock($this->xTile, $this->yTile, $this->zTile);
			$this->speedZ = $this->speedX = $this->speedY = 0;
			
			if($blockID == $this->inTile && $blockMeta == $this->inData){
				++$this->groundTicks;
				if($this->groundTicks >= 1200){ //TODO customizeable?
					$this->close();
				}
			}else{
				$this->inGround = false;
				$this->speedX *= lcg_value() * 0.2;
				$this->speedY *= lcg_value() * 0.2;
				$this->speedZ *= lcg_value() * 0.2;
				$this->groundTicks = $this->airTicks = 0;
			}
			
		}else{
			++$this->airTicks;
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
				if($eid != $this->eid && $ent->isPickable() && ($eid != $this->shooterEID || $this->airTicks >= 5)){
					
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
				if($v4->entityHit != null){
					$v49 = sqrt($this->speedY*$this->speedY + $this->speedX*$this->speedX + $this->speedZ*$this->speedZ);
					$damage = ceil($v49+$v49);
					
					if($this->criticial){
						$damage += mt_rand(0, $damage / 2 + 1);
					}
					
					
					if($v4->entityHit->harm($damage, $this->eid)){
						//vanilla seems to increase arrow count if $v4->entity is mob
						$this->close();
					}else{
						$this->speedX *= -0.1;
						$this->speedY *= -0.1;
						$this->speedZ *= -0.1;
						$this->yaw += 180;
						$this->lastYaw += 180;
						$this->airTicks = 0;
					}
				}else{
					$this->xTile = $v4->blockX;
					$this->yTile = $v4->blockY;
					$this->zTile = $v4->blockZ;
					
					[$this->inTile, $this->inData] = $this->level->level->getBlock($this->xTile, $this->yTile, $this->zTile);
					
					$this->speedX = $v4->hitVector->x - $this->x;
					$this->speedY = $v4->hitVector->y - $this->y;
					$this->speedZ = $v4->hitVector->z - $this->z;
					
					$v21 = sqrt($this->speedX*$this->speedX + $this->speedY*$this->speedY + $this->speedZ*$this->speedZ);
					if($v21 != 0){
						$this->x -= $this->speedX / $v21 * 0.05;
						$this->y -= $this->speedY / $v21 * 0.05;
						$this->z -= $this->speedZ / $v21 * 0.05;
					}
					
					$this->inGround = true;
					$this->shake = 7;
					$this->criticial = false;
				}
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
			$this->speedY -= 0.05;
			//$this->setP
			$v7 = $this->width / 2;
			$v8 = $this->height;
			$this->boundingBox->setBounds($this->x - $v7, $this->y - $this->yOffset /*+ $this->ySize*/, $this->z - $v7, $this->x + $v7, $this->y - $this->yOffset + $v8 /*+ $this->ySize*/, $this->z + $v7);
			
			$this->doBlocksCollision();
		}
	}
	
	public function spawn(Player $player){
		if(!($this->type == OBJECT_ARROW)) return false;
		if(!$player->hasEntity($this)){
			$player->addEntity($this);
			$pk = new AddEntityPacket;
			$pk->eid = $this->eid;
			$pk->type = $this->type;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->did = 1;		
			$pk->speedX = $this->speedX;
			$pk->speedY = $this->speedY;
			$pk->speedZ = $this->speedZ;
			$player->entityQueueDataPacket($pk);
			$this->sendLinkPackets($player);
			return true;
		}
		return false;
	}
}
