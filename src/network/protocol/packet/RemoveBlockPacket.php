<?php

class RemoveBlockPacket extends RakNetDataPacket{
	public $eid;
	public $x;
	public $y;
	public $z;
	
	public function pid(){
		return ProtocolInfo::REMOVE_BLOCK_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();
		$this->x = $this->getInt();
		$this->z = $this->getInt();
		$this->y = $this->getByte();
	}
	
	public function encode(){

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