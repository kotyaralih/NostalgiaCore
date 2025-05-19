<?php

class Painting extends Entity{
	const CLASS_TYPE = ENTITY_OBJECT;
	const TYPE = OBJECT_PAINTING;
	
	public $motive;
	public $direction;
	public $xPos, $yPos, $zPos;
	
	public $isValid = true;
	
	public function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		$this->x = $this->data["TileX"] ?? $this->x;
		$this->y = $this->data["TileY"] ?? $this->y;
		$this->z = $this->data["TileZ"] ?? $this->z;
		
		$this->xPos = $this->data["xPos"] ?? (int)$this->x;
		$this->zPos = $this->data["zPos"] ?? (int)$this->z;
		$this->yPos = $this->data["yPos"] ?? (int)$this->y;
		
		$this->health = 1;
		$this->canBeAttacked = true;
		$this->width = 1;
		$this->setSize(0.5, 0.5);
		$this->isStatic = true;
		$mot = $this->data["Motive"] ?? false;
		$dir = $this->data["Direction"] ?? false;
		if($dir === false){
			$dir = floor($this->yaw / 90);
		}
		
		if($mot === false){
			$this->setRandomMotive($dir);
			
		}else{
			$this->motive = $mot;
			$this->direction = $dir;
			$this->setDirection($dir);
		}
	}
	
	public $scounter = 0;
	public function update($now){
		if($this->closed === true){
			return false;
		}
		
		if(++$this->scounter >= 100){
			$this->scounter = 0;
			if(!$this->survives() && !$this->dead){ //maybe dont check entity collision?
				$this->makeDead("nosurvive");
			}
		}
		
		return true;
	}

	public function setRandomMotive($dir){
		$tochoose = [];
		foreach(PaintingItem::$motives as $name => $motive){
			$this->motive = $name;
			$this->setDirection($dir);
			
			if($this->survives()){
				$tochoose[] = $name;
			}
		}
		if(count($tochoose) > 0){
			$ind = mt_rand(0, count($tochoose)-1);
		}else{
			$this->isValid = false;
			return;
		}
		
		$this->motive = $tochoose[$ind];
		$this->setDirection($dir);
	}
	
	public static function offs($n){
		if($n == 32 || $n == 64) return 0.5;
		return 0;
	}
	
	const DIRECTION_OPPOSITE = [2, 3, 0, 1];
	public function setDirection($dir){
		$this->direction = $dir;
		$this->lastYaw = $this->yaw = $dir * 90;
		$size = PaintingItem::$motives[$this->motive] ?? array(1, 1);
		$width = $size[0]*16;
		$height = $size[1]*16;
		if($dir == 2 || $dir == 0){
			$v10 = 2;
			$v9 = $width;
			$this->yaw = $this->lastYaw = 90 * self::DIRECTION_OPPOSITE[$dir];
		}else{
			$v9 = 2;
			$v10 = $width;
		}
		
		$v12 = $v9 * 0.03125;
		$v13 = $height * 0.03125;
		$v14 = $v10 * 0.03125;
		$v15 = $this->xPos + 0.5;
		$v16 = $this->yPos + 0.5;
		$v17 = $this->zPos + 0.5; //XXX check
		
		if($dir == 2){
			$v17 = $v17 - 0.5625;
			$v15 = $v15 - self::offs($width);
		}else if($dir == 1){
			$v15 = $v15 - 0.5625;
			$v17 = $v17 + self::offs($width);
		}else if($dir == 3){
			$v15 = $v15 + 0.5625;
			$v17 = $v17 - self::offs($width);
		}else{
			$v17 = $v17 + 0.5625;
			$v15 = $v15 + self::offs($width);
		}
		
		$v23 = $v16 + self::offs($height);
		$this->setPos($v15, $v23, $v17);
		$this->boundingBox->minX = ($v15 - $v12) + 0.03125;
		$this->boundingBox->minY = ($v23 - $v13) + 0.03125;
		$this->boundingBox->minZ = ($v17 - $v14) + 0.03125;
		$this->boundingBox->maxX = ($v15 + $v12) - 0.03125;
		$this->boundingBox->maxY = ($v23 + $v13) - 0.03125;
		$this->boundingBox->maxZ = ($v17 + $v14) - 0.03125;
	}
	
	public function getDrops(){
		return [
			[PAINTING, 0, 1]
		];
	}
	
	public function isPickable(){
		return true;
	}
	
	public function survives(){
		$cubes = $this->level->getCubes($this, $this->boundingBox);
		if(count($cubes) == 0){
			
			$size = PaintingItem::$motives[$this->motive] ?? array(1, 1);
			$width = $size[0];
			$width2 = $width*16/32;
			$height = $size[1];
			$height2 = $height*16/32;
			if($width < 1) $width = 1;
			if($height < 1) $height = 1;
			$positionX = $this->xPos;
			$positionZ = $this->zPos;
			if($this->direction == 2 || $this->direction == 0) $positionX = floor($this->x - $width2);
			if($this->direction == 1 || $this->direction == 3) $positionZ = floor($this->z - $width2);
			
			$minY = floor($this->y - $height2);
			
			for($off = 0; $off < $width; ++$off){
				for($yoff = 0; $yoff < $height; ++$yoff){
					$y = $minY + $yoff;
					if($this->direction == 1 || $this->direction == 3){
						$id = $this->level->level->getBlockId($this->xPos, $y, $positionZ + $off);
					}else{
						$id = $this->level->level->getBlockId($positionX + $off, $y, $this->zPos);
					}
					
					if(!StaticBlock::getIsSolid($id)){
						return false;
					}
				}
			}
			
			$ents = $this->level->getEntitiesInAABBOfType($this->boundingBox, ENTITY_OBJECT);
			foreach($ents as $e){
				if($e->eid == $this->eid) continue;
				if($e->type == OBJECT_PAINTING){
					return false;
				}
			}
			return true;
		}
		
		return false;
	}
	
	public function createSaveData(){
		$data = parent::createSaveData();
		
		$data["Motive"] = $this->motive;
		$data["Direction"] = $this->direction;
		$data["xPos"] = $this->xPos;
		$data["yPos"] = $this->yPos;
		$data["zPos"] = $this->zPos;
		return $data;
	}

	public function spawn($player){
		$pk = new AddPaintingPacket;
		$pk->eid = $this->eid;
		$pk->x = (int) $this->xPos;
		$pk->y = (int) $this->yPos;
		$pk->z = (int) $this->zPos;
		$pk->direction = $this->direction;
		$pk->title = $this->motive;
		$player->dataPacketAlwaysRecover($pk);
	}
}
