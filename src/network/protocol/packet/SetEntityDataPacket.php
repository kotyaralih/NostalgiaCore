<?php

class SetEntityDataPacket extends RakNetDataPacket{
	public $eid;
	public $metadata;
	
	public function pid(){
		return ProtocolInfo::SET_ENTITY_DATA_PACKET;
	}
	
	public function decode(){

	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->put(Utils::writeMetadata($this->metadata));
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