<?php

class TakeItemEntityPacket extends RakNetDataPacket{
	public $target;
	public $eid;

	public function pid(){
		return ProtocolInfo::TAKE_ITEM_ENTITY_PACKET;
	}
	
	public function decode(){

	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->target);
		$this->putInt($this->eid);
	}

	//TODO convert
	public function eidsToLocal(Player $p){
		if(!$this->localEids){
			$this->localEids = true;
			$this->eid = $p->global2localEID[$this->eid] ?? false;
			$this->target = $p->global2localEID[$this->eid] ?? false;
			if($this->eid === false || $this->target === false) return false;
		}
		return true;
	}
}