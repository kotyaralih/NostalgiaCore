<?php

class TripodCamera extends Entity{
	const TYPE = OBJECT_TRIPOD_CAMERA;
	
	function __construct(Level $level, $eid, $class, $type = 0, $data = []){
		parent::__construct($level, $eid, $class, $type, $data);
		$this->setHealth(PHP_INT_MAX, "generic", allowHarm: false);
	}
	
	public function isPickable(){
		return !$this->dead;
	}
	
	public function interactWith(Entity $e, $action){
		if($e->isPlayer() and $action === InteractPacket::ACTION_HOLD){
			//todo
			$this->server->schedule(40, [$this, "close"]);
		}
		parent::interactWith($e, $action);
	}
}