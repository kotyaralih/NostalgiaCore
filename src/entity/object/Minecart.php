<?php

class Minecart extends Vehicle{
	const TYPE = OBJECT_MINECART;
	/**
	 * A minecart rotation matrix
	 * @var int[][][] 
	 */
	private static $matrix = [
		[
			[0, 0, -1],
			[0, 0, 1]
		],
		[
			[-1, 0, 0],
			[1, 0, 0]
		],
		[
			[-1, -1, 0],
			[1, 0, 0]
		],
		[
			[-1, 0, 0],
			[1, -1, 0]
		],
		[
			[0, 0, -1],
			[0, -1, 1]
		],
		[
			[0, -1, -1],
			[0, 0, 1]
		],
		[
			[0, 0, 1],
			[1, 0, 0]
		],
		[
			[0, 0, 1],
			[-1, 0, 0]
		],
		[
			[0, 0, -1],
			[-1, 0, 0]
		],
		[
			[0, 0, -1],
			[1, 0, 0]
		]
	];
	
	private $hurtTime = 0; //syncentdata 17 int
	private $damage = 0; //syncentdata 19 float
	
	private $minecartX = 0, $minecartY = 0, $minecartZ = 0;
	private $turnProgress = 0;
	public $isInReverse = false;
	
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		$this->canBeAttacked = true;
		$this->x = $this->data["TileX"] ?? $this->x;
		$this->y = $this->data["TileY"] ?? $this->y;
		$this->z = $this->data["TileZ"] ?? $this->z;
		$this->setHealth(1, "generic"); //orig: 3
		$this->setSize(0.98, 0.7);
		$this->yOffset = $this->height / 2;
		$this->stepHeight = 0;
	}
	
	public function isPickable(){
		return !$this->dead;
	}
	
	public function getDrops(){
		return [
			[MINECART, 0, 1]
		];
	}
	
	public function getPos($x, $y, $z){
		$blockX = floor($x);
		$blockY = floor($y);
		$blockZ = floor($z);
		
		if(RailBaseBlock::isRailBlock($this->level, $blockX, $blockY - 1, $blockZ)) --$blockY;
		
		[$id, $meta] = $this->level->level->getBlock($blockX, $blockY, $blockZ);
		if(RailBaseBlock::isRailID($id)){
			
			if($id == POWERED_RAIL) $meta &= 7;
			
			$mat = self::$matrix[$meta];
			$v13 = 0;
			$v15 = $blockX + 0.5 + $mat[0][0] * 0.5;
			$v17 = $blockY + 0.5 + $mat[0][1] * 0.5;
			$v19 = $blockZ + 0.5 + $mat[0][2] * 0.5;
			
			$v21 = $blockX + 0.5 + $mat[1][0] * 0.5;
			$v23 = $blockY + 0.5 + $mat[1][1] * 0.5;
			$v25 = $blockZ + 0.5 + $mat[1][2] * 0.5;
			
			$v27 = $v21 - $v15;
			$v29 = ($v23 - $v17) * 2;
			$v31 = $v25 - $v19;
			
			if($v27 == 0){
				$x = $blockX + 0.5;
				$v13 = $z - $blockZ;
			}else if($v31 == 0){
				$z = $blockZ + 0.5;
				$v13 = $x - $blockX;
			}else{
				$v33 = $x - $v15;
				$v35 = $z - $v19;
				$v13 = ($v33*$v27 + $v35*$v31) * 2;
			}
			
			$x = $v15 + $v27 * $v13;
			$y = $v17 + $v29 * $v13;
			$z = $v19 + $v31 * $v13;
			
			if($v29 < 0) ++$y;
			if($v29 > 0) $y += 0.5;
			return [$x, $y, $z];
		}
		
		return false;
	}
	
	public function moveAlongTrack($x, $y, $z, $maxSpeed, $boost, $id, $meta){
		$this->fallDistance = 0;
		$vec = $this->getPos($this->x, $this->y, $this->z);
		$this->y = $y;
		
		if($id == POWERED_RAIL) $meta &= 7;
		
		if($meta >= 2 && $meta <= 5) $this->y = $y + 1;
		
		switch($meta){
			case 2:
				$this->speedX -= $boost;
				break;
			case 3:
				$this->speedX += $boost;
				break;
			case 4:
				$this->speedZ += $boost;
				break;
			case 5:
				$this->speedZ -= $boost;
				break;
		}
		
		$mat = self::$matrix[$meta];
		$matXDiff = $mat[1][0] - $mat[0][0];
		$matZDiff = $mat[1][2] - $mat[0][2];
		$matDiffTotal = sqrt($matXDiff*$matXDiff + $matZDiff*$matZDiff);
		
		if(($this->speedZ * $matZDiff + $this->speedX * $matXDiff) < 0){
			$matXDiff = -$matXDiff;
			$matZDiff = -$matZDiff;
		}
		$speedTotal = sqrt($this->speedZ*$this->speedZ + $this->speedX*$this->speedX);
		if($speedTotal > 2) $speedTotal = 2;
		$this->speedX = ($speedTotal * $matXDiff) / $matDiffTotal;
		$this->speedZ = ($speedTotal * $matZDiff) / $matDiffTotal;
		
		if($this->linkedEntity != 0 && !$this->isRider){
			$rider = $this->level->entityList[$this->linkedEntity] ?? false;
			if($rider instanceof Entity && ($rider->isPlayer() || $rider->class == ENTITY_MOB)){
				if($rider->moveForward > 0){
					$v32 = sin($this->yaw * M_PI / 180);
					$v33 = cos($this->yaw * M_PI / 180);
					
					if($this->speedZ*$this->speedZ + $this->speedX*$this->speedX < 0.01){
						$this->speedX -= $v32*0.1;
						$this->speedZ += $v33*0.1;
					}
				}
			}
		}
		
		$v38 = ($x + 0.5) + ($mat[0][0] * 0.5);
		$v39 = ($z + 0.5) + ($mat[0][2] * 0.5);
		$v40 = (($x + 0.5) + ($mat[1][0] * 0.5)) - $v38;
		$v41 = (($z + 0.5) + ($mat[1][2] * 0.5)) - $v39;
		
		if($v40 == 0){
			$v42 = $this->z - $z;
		}else if($v41 == 0){
			$v42 = $this->x - $x;
		}else{
			$v44 = (($this->z - $v39) * $v41) + (($this->x - $v38) * $v40);
			$v42 = $v44 + $v44;
		}
		
		$this->x = $v38 + ($v40 * $v42);
		$this->z = $v39 + ($v41 * $v42);
		
		$this->setPos($this->x, $this->y + $this->yOffset + 0.00001, $this->z);
		$dx = $this->speedX;
		$dz = $this->speedZ;
		if($this->linkedEntity != 0 && !$this->isRider){
			$dx *= 0.75;
			$dz *= 0.75;
		}
		
		if($dx < -$maxSpeed) $dx = -$maxSpeed;
		else if($dx > $maxSpeed) $dx = $maxSpeed;
		
		if($dz < -$maxSpeed) $dz = -$maxSpeed;
		else if($dz > $maxSpeed) $dz = $maxSpeed;
		
		$this->move($dx, 0, $dz);
		
		if($mat[0][1]){
			if((floor($this->x) - $x) == $mat[0][0] && (floor($this->z) - $z) == $mat[0][2]){
				$this->setPos($this->x, $mat[0][1] + $this->y, $this->z);
			}else if($mat[1][1]){
				goto mat_1_1;
			}
		}else if($mat[1][1]){
			mat_1_1:
			if((floor($this->x) - $x) == $mat[1][0] && (floor($this->z) - $z) == $mat[1][2]){
				$this->setPos($this->x, $mat[1][1] + $this->y, $this->z);
			}
		}
		
		$this->applyNaturalSlowdown();
		$vec2 = $this->getPos($this->x, $this->y, $this->z);
		if($vec2 !== false && $vec !== false){
			$yDiff = ($vec[1] - $vec2[1]) * 0.05;
			$totalSpeed = sqrt($this->speedZ*$this->speedZ + $this->speedX*$this->speedX);
			
			if($totalSpeed > 0){
				$this->speedX = ($this->speedX / $totalSpeed) * ($totalSpeed + $yDiff);
				$this->speedZ = ($this->speedZ / $totalSpeed) * ($totalSpeed + $yDiff);	
			}
			$this->setPos($this->x, $vec2[1], $this->z);
		}
		
		if(floor($this->x) != $x || floor($this->z) != $z){ //this breaks everything
			$totalSpeed = sqrt($this->speedZ*$this->speedZ + $this->speedX*$this->speedX);
			$this->speedX = (floor($this->x) - $x) * $totalSpeed;
			$this->speedZ = (floor($this->z) - $z) * $totalSpeed;
		}
		
		if($id == POWERED_RAIL){
			$totalSpeed = sqrt($this->speedZ*$this->speedZ + $this->speedX*$this->speedX);
			if($totalSpeed > 0.01){
				$this->speedX += (($this->speedX / $totalSpeed) * 0.06);
				$this->speedZ += (($this->speedZ / $totalSpeed) * 0.06);
				return;
			}
			
			if($meta == 1){
				//TODO Level::isSolidBlockingTile
				if(StaticBlock::getIsSolid($this->level->level->getBlockID($x - 1, $y, $z))){
					$v63 = 0.02;
				}else{
					if(!StaticBlock::getIsSolid($this->level->level->getBlockID($x + 1, $y, $z))){
						return;
					}
					$v63 = -0.02;
				}
				$this->speedX = $v63;
			}else if($meta == 0){
				//TODO Level::isSolidBlockingTile
				if(StaticBlock::getIsSolid($this->level->level->getBlockID($x, $y, $z - 1))){
					return;
				}else{
					if(!StaticBlock::getIsSolid($this->level->level->getBlockID($x, $y, $z + 1))){
						return;
					}
					$v64 = -0.02;
				}
				$this->speedZ = $v64;
			}
		}
		
	}
	
	
	public function applyNaturalSlowdown(){
		$mult = $this->linkedEntity != 0 && !$this->isRider ? 0.997 : 0.96;
		
		$this->speedX *= $mult;
		$this->speedY = 0;
		$this->speedZ *= $mult;
	}
	
	public function comeOffTrack($topSpeed){
		if($this->speedX < -$topSpeed) $this->speedX = -$topSpeed;
		else if($this->speedX > $topSpeed) $this->speedX = $topSpeed;
		
		if($this->speedZ < -$topSpeed) $this->speedZ = -$topSpeed;
		else if($this->speedZ > $topSpeed) $this->speedZ = $topSpeed;
		
		if($this->onGround){
			$this->speedX *= 0.5;
			$this->speedY *= 0.5;
			$this->speedZ *= 0.5;
		}
		
		$this->move($this->speedX, $this->speedY, $this->speedZ);
		
		if(!$this->onGround){
			$this->speedX *= 0.95;
			$this->speedY *= 0.95;
			$this->speedZ *= 0.95;
		}
		
	}
	public function applyCollision(Entity $collided){
		$diffX = $collided->x - $this->x;
		$diffZ = $collided->z - $this->z;
		$dist = $diffX*$diffX + $diffZ*$diffZ;
		if($dist >= 0.0001){
			$sqrtMax = sqrt($dist);
			$diffX /= $sqrtMax;
			$diffZ /= $sqrtMax;
			
			$col = (($v = 1 / $sqrtMax) > 1 ? 1 : $v);
			$diffX *= $col;
			$diffZ *= $col;
			$diffX *= 0.1;
			$diffZ *= 0.1;
			
			$diffX *= 0.5;
			$diffZ *= 0.5;
			
			$this->addVelocity(-$diffX, 0, -$diffZ);
			$collided->addVelocity($diffX / 4, 0, $diffZ / 4);
		}
		//parent::applyCollision($collided);
	}
	public function update($now){
		if($this->closed === true){
			return false;
		}
		$this->updateLast();
		//$this->updatePosition();
		
		$this->speedY -= 0.04;
		//TODO port stuff
		
		$blockX = floor($this->x);
		$blockY = floor($this->y);
		$blockZ = floor($this->z);
		
		if(RailBaseBlock::isRailBlock($this->level, $blockX, $blockY - 1, $blockZ)){
			--$blockY;
		}
		
		[$id, $meta] = $this->level->level->getBlock($blockX, $blockY, $blockZ);
		if(RailBaseBlock::isRailID($id)){
			 $this->moveAlongTrack($blockX, $blockY, $blockZ, 0.4, 0.0078125, $id, $meta);
			 //activatorRail is a cake
		}else{
			$this->comeOffTrack(0.4);
		}

		$this->doBlocksCollision();
		
		$this->pitch = 0;
		$diffX = $this->lastX - $this->x;
		$diffZ = $this->lastZ - $this->z;
		
		if($diffX*$diffX + $diffZ*$diffZ > 0.001){
			$this->yaw = atan2($diffZ, $diffX) * 180 / M_PI;
			
			if($this->isInReverse) $this->yaw += 180;
		}
		
		$yw = fmod($this->yaw - $this->lastYaw, 360);
		if($yw >= 180) $yw -= 360;
		if($yw < 180) $yw += 360;
		
		if($yw < -170 || $yw >= 170){
			$this->isInReverse = !$this->isInReverse;
			$this->yaw = $this->yaw + 180;
		}
		
		$bb = $this->boundingBox->expand(0.2, 0, 0.2);
		$minChunkX = ((int)($bb->minX - 2)) >> 4;
		$minChunkZ = ((int)($bb->minZ - 2)) >> 4;
		$maxChunkX = ((int)($bb->minX + 2)) >> 4;
		$maxChunkZ = ((int)($bb->minZ + 2)) >> 4;
		
		//TODO also index by chunkY?
		for($chunkX = $minChunkX; $chunkX <= $maxChunkX; ++$chunkX){
			for($chunkZ = $minChunkZ; $chunkZ <= $maxChunkZ; ++$chunkZ){
				$ind = "$chunkX $chunkZ";
				foreach($this->level->entityListPositioned[$ind] ?? [] as $entid){
					$e = ($this->level->entityList[$entid] ?? null);
					if($e instanceof Entity && $e->eid != $this->eid && $e->eid != $this->linkedEntity){
						if($e->isPushable() && $e->boundingBox->intersectsWith($bb)){
							if($e->isPlayer()){
								$this->applyCollision($e);
							}else{
								$e->applyCollision($this);
							}
							
						}
					}
				}
			}
		}
	}
	
	public function close()
	{
		parent::close();
		if($this->linkedEntity != 0){
			$ent = $this->level->entityList[$this->linkedEntity] ?? false;
			if($ent instanceof Entity){
				$ent->stopRiding();
			}else{
				ConsoleAPI::warn("$this is being ridden by invalid entity {$this->linkedEntity}");
			}
		}
	}
	
	public function isPushable(){
		return false; //TODO replace with true
	}
	
	public function spawn($player){
		$pk = new AddEntityPacket;
		$pk->eid = $this->eid;
		$pk->type = $this->type;
		$pk->x = $this->x;
		$pk->y = $this->y; //+ $this->yOffset;
		$pk->z = $this->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$player->dataPacketAlwaysRecover($pk);
		
		$pk = new SetEntityMotionPacket;
		$pk->eid = $this->eid;
		$pk->speedX = $this->speedX;
		$pk->speedY = $this->speedY;
		$pk->speedZ = $this->speedZ;
		$player->dataPacket($pk);
	}
	
	public function interactWith(Entity $e, $action){
		if($action === InteractPacket::ACTION_HOLD && $e->isPlayer() && $this->canRide($e)){
			$e->setRiding($this);
			return true;
		}
		if($action === InteractPacket::ACTION_ATTACK && $e->eid == $this->linkedEntity){
			return false; //TODO more vanilla way?
		}
		parent::interactWith($e, $action);
	}
	public function canRide($e)
	{
		return $this->linkedEntity == 0 && $e->linkedEntity == 0;
	}

}
