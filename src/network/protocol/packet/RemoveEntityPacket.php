<?php

class RemoveEntityPacket extends RakNetDataPacket{
	public $eid;

	public function pid(){
		return ProtocolInfo::REMOVE_ENTITY_PACKET;
	}
	
	public function decode(){

	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
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