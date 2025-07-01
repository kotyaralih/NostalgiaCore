<?php

class PlayerEquipmentPacket extends RakNetDataPacket{
	public $eid;
	public $item;
	public $meta;
	public $slot;
	
	public function pid(){
		return ProtocolInfo::PLAYER_EQUIPMENT_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();
		$this->item = $this->getShort();
		$this->meta = $this->getShort();
		$this->slot = $this->getSignedByte();
	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putShort($this->item);
		$this->putShort($this->meta);
		$this->putByte($this->slot);
	}
	
	public function eidsToGlobal(Player $p){
		if($this->localEids){
			$this->localEids = false;
			$this->eid = $p->local2GlobalEID[$this->eid] ?? false;
			if($this->eid === false) return false;
		}
		return true;
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