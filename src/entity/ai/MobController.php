<?php

class MobController
{
	public static $AUTOJUMP = false;
	public static $landed = false;
	public static $DANGEROUS_BLOCKS = [
		LAVA => true,
		STILL_LAVA => true,
		FIRE => true,
	];
	/**
	 * @var Living
	 */
	public $entity;
	
	public $finalYaw, $finalPitch, $finalHeadYaw;
	
	public $headYaw;
	public $someTicker = 0;
	
	protected $jumping;
	
	public $moveToX, $moveToY, $moveToZ;
	public $speedMultiplier;
	public $updateMove = false;
	
	public $lookX, $lookY, $lookZ;
	public $deltaLookYaw, $deltaLookPitch;
	public $isLooking = false;
	
	public $headYawIsYaw = false;
	
	public function __construct($e){
		$this->entity = $e;
	}
	public function isDangerous($id){
		return isset(self::$DANGEROUS_BLOCKS[$id]);
	}
	public function isJumping(){
		return $this->jumping;
	}
	
	public function setJumping($b){
		$this->jumping = $b;
	}
	
	public function isRotationCompleted(){
		return $this->finalHeadYaw === $this->entity->headYaw;
	}
	public function setMovingTarget($x, $y, $z, $speed){
		$this->moveToX = $x;
		$this->moveToY = $y;
		$this->moveToZ = $z;
		$this->speedMultiplier = $speed;
		$this->updateMove = true;
	}
	
	public function setMovingOffset($x, $y, $z, $speed){
		$this->moveToX = $this->entity->x + ($x);
		$this->moveToY = $this->entity->y + ($y);
		$this->moveToZ = $this->entity->z + ($z);
		$this->speedMultiplier = $speed;
		$this->updateMove = true;
	}
	
	public function canJump(){
		return $this->isJumping() && $this->jumpTimeout <= 0 && $this->entity->onGround;
	}
	public static function limitAngle2($old, $newA, $limit){
		$v4 = Utils::wrapAngleTo180($old - $newA);
		
		if($v4 < -$limit) $v4 = -$limit;
		if($v4 >= $limit) $v4 = $limit;
		
		return $old - $v4;
	}
	public static function limitAngle($old, $newA, $limit){
		$v4 = Utils::wrapAngleTo180($newA - $old);
		
		if($v4 > $limit) $v4 = $limit;
		if($v4 < -$limit) $v4 = -$limit;
		
		return $old + $v4;
	}
	
	public function jumpTick(){
		$this->entity->jumping = $this->jumping;
		$this->jumping = false;
	}
	
	public function movementTick(){
		$this->entity->moveForward = 0;
		
		if($this->updateMove){
			$this->updateMove = false;
			$v1 = floor($this->entity->boundingBox->minY + 0.5);
			
			$diffX = $this->moveToX - $this->entity->x;
			$diffZ = $this->moveToZ - $this->entity->z;
			$diffY = $this->moveToY - $v1;
			
			$v8 = $diffX*$diffX + $diffY*$diffY + $diffZ*$diffZ;
			
			if($v8 >= 0.00000025){
				$v10 = (atan2($diffZ, $diffX) * 180 / M_PI) - 90;
				$this->entity->yaw = self::limitAngle($this->entity->yaw, $v10, 30);
				$this->entity->setAIMoveSpeed($this->speedMultiplier * $this->entity->getSpeed() * $this->entity->getSpeedModifer());
				
				if($diffY > 0 && $diffX*$diffX + $diffZ*$diffZ < 1 && $this->entity->isCollidedHorizontally){
					 $this->setJumping(true);
				}
			}
		}
		//TODO handle jumps somewhere
		//if($this->canJump()){
		//	$this->jumpTimeout = 10;
		//	$this->entity->speedY = 0.50;
		//}
		//if($this->jumpTimeout > 0) --$this->jumpTimeout;
	}
	
	public function updateHeadYaw(){
		$diffX = $this->entity->x - $this->entity->lastX;
		$diffZ = $this->entity->z - $this->entity->lastZ;
		if(($diffX*$diffX + $diffZ*$diffZ) > 0.00000025){
			$this->entity->renderYawOffset = $this->entity->yaw;
			$this->entity->headYaw = self::limitAngle2($this->entity->renderYawOffset, $this->entity->headYaw, 75);
			$this->headYaw = $this->entity->headYaw;
			$this->someTicker = 0;
		}else{
			$v5 = 75;
			if(abs($this->entity->headYaw - $this->headYaw) > 15){
				$this->someTicker = 0;
				$this->headYaw = $this->entity->headYaw;
			}else{
				++$this->someTicker;
				
				if($this->someTicker > 10){
					$v5 = max(1 - ($this->someTicker - 10) / 10, 0) * 75;
				}
			}
			$this->entity->renderYawOffset = self::limitAngle2($this->entity->headYaw, $this->entity->renderYawOffset, $v5);
		}
	}
	
	public function rotateTick(){ //TODO handle more rotation
		
		$this->entity->pitch = 0;
		if($this->isLooking){
			$this->isLooking = false;
			
			$diffX = $this->lookX - $this->entity->x;
			$diffY = $this->lookY - ($this->entity->y + $this->entity->getEyeHeight());
			$diffZ = $this->lookZ - $this->entity->z;
			$distance = sqrt($diffX*$diffX + $diffZ*$diffZ);
			
			$v9 = (atan2($diffZ, $diffX)*180 / M_PI) - 90;
			$v10 = -(atan2($diffY, $distance)*180 / M_PI);
			
			$this->entity->pitch = self::limitAngle($this->entity->pitch, $v10, $this->deltaLookPitch);
			$this->entity->headYaw = self::limitAngle($this->entity->headYaw, $v9, $this->deltaLookYaw);
		}else{
			$this->entity->headYaw = self::limitAngle($this->entity->headYaw, $this->entity->renderYawOffset, 10);
		}
		
		if($this->headYawIsYaw) $this->entity->yaw = $this->entity->headYaw;
		$this->headYawIsYaw = false;
		
		/* Some stuff for pathfinder - nc doesnt have it now
		 * float var11 = MathHelper.wrapAngleTo180_float(this.entity.rotationYawHead - this.entity.renderYawOffset);

		if (!this.entity.getNavigator().noPath())
		{
			if (var11 < -75.0F)
			{
				this.entity.rotationYawHead = this.entity.renderYawOffset - 75.0F;
			}

			if (var11 > 75.0F)
			{
				this.entity.rotationYawHead = this.entity.renderYawOffset + 75.0F;
			}
		}
		 */
		
		//$this->entity->lastHeadYaw = $this->entity->headYaw;
		//$w180 = Utils::wrapAngleTo180($this->finalHeadYaw - $this->entity->headYaw); 
		//$w180min = min(abs($w180), 20)*Utils::getSign($w180);
		//$this->entity->headYaw = Utils::wrapAngleTo360($this->entity->headYaw + $w180min);
	}
	
	public function setLookPosition($posX, $posY, $posZ, $lookYaw, $lookPitch){
		$this->lookX = $posX;
		$this->lookY = $posY;
		$this->lookZ = $posZ;
		$this->deltaLookYaw = $lookYaw;
		$this->deltaLookPitch = $lookPitch;
		$this->isLooking = true;
	}
	
	public function faceEntity($x, $y, $z){
		$len = sqrt($x*$x + $z*$z + $y*$y);
		//$d = $len == 0 ?//$v->subtract($this->entity)->normalize();
		if($len == 0){
			$dx = 0;
			$dz = 0;
		}else{
			$dx = $x / $len;
			$dz = $z / $len;
		}
		
		
		$tan = $dz == 0 ? ($dx < 0 ? 180 : 0) : (90 - rad2deg(atan($dx / $dz))); 
		$thetaOffset = $dz < 0 ? 90 : 270;
		$calcYaw = ($thetaOffset + $tan);
		$this->finalHeadYaw = $this->entity->yaw = Utils::wrapAngleTo360($calcYaw);
	}
	
	public function lookOffset($x, $y, $z, $pitch = true){
		$tan = $z == 0 ? ($x < 0 ? 180 : 0) : (90 - rad2deg(atan($x / $z))); /*arctan(infinity) = pi/2 = (90deg) - 90 = 0*/
		$thetaOffset = $z < 0 ? 90 : 270;
		$calcYaw = $tan + $thetaOffset;
		
		$this->entity->yaw = $this->finalHeadYaw = Utils::wrapAngleTo360($calcYaw);
		
		if($pitch){
			$diff = sqrt($x * $x + $z * $z);
			$calcPitch = $diff == 0 ? ($y < 0 ? -90 : 90) : rad2deg(atan($y / $diff));
			$this->entity->pitch = $calcPitch;
		}
		
		//$this->entity->server->query("UPDATE entities SET pitch = ".$this->entity->pitch.", yaw = ".$this->entity->yaw." WHERE EID = ".$this->entity->eid.";");
		return true;
	}
	
	public function lookOn($x, $y = 0, $z = 0, $pitch = true){
		if($x instanceof Vector3){
			return $this->lookOn($x->x, $x->y + $x->getEyeHeight(), $x->z, $pitch);
		}
		return $this->lookOffset($x - $this->entity->x, ($this->entity->y + $this->entity->height) - $y, $z - $this->entity->z, $pitch);
	}
	
	public function __destruct(){
		unset($this->entity);
	}
}

