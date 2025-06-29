<?php

class AddItemEntityPacket extends RakNetDataPacket{
	public $eid;
	public $item;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $pitch;
	public $roll;
	
	public function pid(){
		return ProtocolInfo::ADD_ITEM_ENTITY_PACKET;
	}
	
	public function decode(){

	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putSlot($this->item);
		$this->putFloat($this->x);
		$this->putFloat($this->y);
		$this->putFloat($this->z);
		$this->putByte($this->yaw);
		$this->putByte($this->pitch);
		$this->putByte($this->roll);
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