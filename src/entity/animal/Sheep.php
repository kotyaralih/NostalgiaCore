<?php

class Sheep extends Animal{
	public $color;
	const TYPE = MOB_SHEEP;
	
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		$this->setSize(0.9, 1.3);
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth($this->data["Health"] ?? 8, "generic");
		$this->setName("Sheep");
		$this->data["Sheared"] = $this->data["Sheared"] ?? 0;
		$this->data["Color"] = $this->data["Color"] ?? $this->sheepColor();
		$this->setSpeed(0.25);
		
		$this->ai->addTask(new TaskRandomWalk(1.0));
		$this->ai->addTask(new TaskLookAtPlayer(6));
		$this->ai->addTask(new TaskPanic(1.5));
		$this->ai->addTask(new TaskEatTileGoal());
		$this->ai->addTask(new TaskLookAround());
		$this->ai->addTask(new TaskSwimming());
		$this->ai->addTask(new TaskTempt(1.0));
		$this->ai->addTask(new TaskMate(1.0));
		$this->ai->addTask(new TaskFollowParent(1.0));
	}
	
	public function createSaveData(){
		$data = parent::createSaveData();
		$data["Color"] = @$this->data["Color"];
		$data["Sheared"] = @$this->data["Sheared"];
		return $data;
	}
	
	public function eatGrass(){
		$this->setSheared(0);
		if($this->isBaby()){
			$age = $this->getAge() + 1200;
			if($age > 0) $age = 0; //TODO simplify
			$this->setAge($age);
		}
	}
	
	public function setSheared($v = null){
		$this->data["Sheared"] = $v === null ? !$this->isSheared() : $v;
		$this->updateMetadata();
	}
	
	public function isSheared(){
		return $this->data["Sheared"];
	}
	
	public function getColor(){
		return $this->data["Color"]; //color === 16 -> color = 0, color === 17 -> color = 1 ...
	}
	
	public function switchColorMeta($meta){
		return abs($meta - 15);
	}
	
	public function setColor($meta){
		$this->data["Color"] = $meta;
	}
	public function getDrops(){
		return ($this->isBaby() || $this->isSheared()) ? parent::getDrops() : [
			[WOOL, $this->getColor(), 1]
		];
	}
	
	public function isFood($id){
		return $id === WHEAT;
	}
	
	public function interactWith(Entity $e, $action){
		if($e->isPlayer() && $action === InteractPacket::ACTION_HOLD){
			$slot = $e->player->getHeldItem();
			if($slot->getID() === SHEARS){
				if(!$this->isSheared() && !$this->isBaby()){
					$this->setSheared(1);
					$speedX = (lcg_value() * 0.2 - 0.1) + (lcg_value() - lcg_value()) * 0.1;
					$speedZ = (lcg_value() * 0.2 - 0.1) + (lcg_value() - lcg_value()) * 0.1;
					$speedY =  0.2 + (lcg_value()) * 0.05;
					$this->server->api->entity->dropRawPos($this->level, $this->x, $this->y + 1, $this->z, BlockAPI::getItem(WOOL, $this->getColor(), mt_rand(1, 3)), $speedX, $speedY, $speedZ);
					$slot->hurtAndBreak(1, $e->player);
				}
				return true;
			}elseif($slot->getID() === DYE){
				$this->setColor($this->switchColorMeta($slot->getMetadata()));
				
				$e->player->consumeSingleItem();
			}
		}
		return parent::interactWith($e, $action);
	}
	
	public function getMetadata(){
		$d = parent::getMetadata();
		if(!isset($this->data["Sheared"])){
			$this->data["Sheared"] = 0;
		}
		$d[16]["value"] = ($this->data["Sheared"] << 4) | ($this->getColor() & 0x0F);
		return $d;
	}
	
	public function sheepColor(){ //a method from 0.8.1
		$c = mt_rand(0,100);
		if($c <= 4){
			return 0xF; //white
		}
		if($c <= 9){
			return 0x7;
		}
		if($c <= 14){
			return 0x8;
		}
		if($c <= 17){
			return 0xC;
		}
		if(mt_rand(0, 500)){
			return 0x0;
		}
		return 0x6;
	}
}