<?php

class InteractPacket extends RakNetDataPacket{
	const ACTION_HOLD = 1; //HOLD CLICK ON ENTITY
	const ACTION_ATTACK = 2; //SIMPLE CLICK(ATTACK)
	const ACTION_VEHICLE_EXIT = 3; //EXIT FROM ENTITY(MINECART)
	public $action;
	public $eid;
	public $target;

	public function pid(){
		return ProtocolInfo::INTERACT_PACKET;
	}
	
	public function decode(){
		$this->action = $this->getByte();
		$this->eid = $this->getInt();
		$this->target = $this->getInt();
	}
	
	public function encode(){
		$this->reset();
		$this->putByte($this->action);
		$this->putInt($this->eid);
		$this->putInt($this->target);
	}
	
	public function eidsToLocal(Player $p){
		if(!$this->localEids){
			$this->localEids = true;
			$this->eid = $p->global2localEID[$this->eid] ?? false;
			$this->target = $p->global2localEID[$this->target] ?? false;
			if($this->eid === false || $this->target === false) return false;
		}
		return true;
	}
	
	public function eidsToGlobal(Player $p){
		if($this->localEids){
			$this->localEids = false;
			$this->eid = $p->local2GlobalEID[$this->eid] ?? false;
			$this->target = $p->local2GlobalEID[$this->target] ?? false;
			if($this->eid === false || $this->target === false) return false;
		}
		return true;
	}
}