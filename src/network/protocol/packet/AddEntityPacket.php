<?php

class AddEntityPacket extends RakNetDataPacket{
	public $eid;
	public $type;
	public $x;
	public $y;
	public $z;
	public $did;
	public $speedX;
	public $speedY;
	public $speedZ;
	
	public function pid(){
		return ProtocolInfo::ADD_ENTITY_PACKET;
	}
	
	public function decode(){

	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putByte($this->type);
		$this->putFloat($this->x);
		$this->putFloat($this->y);
		$this->putFloat($this->z);
		$this->putInt($this->did);
		if($this->did > 0){
			$this->putShort((int)($this->speedX * 8000));
			$this->putShort((int)($this->speedY * 8000));
			$this->putShort((int)($this->speedZ * 8000));
		}
	}
	
	public function eidsToLocal(Player $p){
		if(!$this->localEids){
			$this->localEids = true;
			$this->eid = $p->global2localEID[$this->eid] ?? false;
			if($this->eid === false) return false;
		}
		return true;
	}

}