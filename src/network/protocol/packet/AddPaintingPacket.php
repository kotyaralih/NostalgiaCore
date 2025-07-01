<?php

class AddPaintingPacket extends RakNetDataPacket{
	public $eid;
	public $x;
	public $y;
	public $z;
	public $direction;
	public $title;
	
	public function pid(){
		return ProtocolInfo::ADD_PAINTING_PACKET;
	}
	
	public function decode(){

	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putInt($this->x);
		$this->putInt($this->y);
		$this->putInt($this->z);
		$this->putInt($this->direction);
		$this->putString($this->title);
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