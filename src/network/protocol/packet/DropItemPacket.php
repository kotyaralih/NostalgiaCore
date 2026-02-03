<?php

class DropItemPacket extends RakNetDataPacket{
	public $eid;
	public $randomly;
	public $item;
	
	public function pid(){
		return ProtocolInfo::DROP_ITEM_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();
		$this->randomly = $this->getByte();
		$this->item = $this->getSlot();
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
