<?php

class MovePlayerPacket extends RakNetDataPacket{
	public $eid;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $pitch;
	public $bodyYaw;
	
	public function pid(){
		return ProtocolInfo::MOVE_PLAYER_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();
		$this->x = $this->getFloat();
		$this->y = $this->getFloat();
		$this->z = $this->getFloat();
		$this->yaw = $this->getFloat();
		$this->pitch = $this->getFloat();
		$this->bodyYaw = $this->getFloat();
	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putFloat($this->x);
		$this->putFloat($this->y);
		$this->putFloat($this->z);
		$this->putFloat($this->yaw);
		$this->putFloat($this->pitch);
		$this->putFloat($this->bodyYaw);
	}

	public function eidsToLocal(Player $p){
		if(!$this->localEids){
			$this->localEids = true;
			$this->eid = $p->global2localEID[$this->eid] ?? false;
			if($this->eid === false) return false;
		}
		return true;
	}
	public function eidsToGlobal(Player $p){
		if($this->localEids){
			$this->localEids = false;
			$this->eid = $p->local2GlobalEID[$this->eid] ?? false;
			if($this->eid === false) return false;
		}
		return true;
	}
}