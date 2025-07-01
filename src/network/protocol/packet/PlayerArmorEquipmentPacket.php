<?php

class PlayerArmorEquipmentPacket extends RakNetDataPacket{
	public $eid;
	public $slots = array();
	
	public function pid(){
		return ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET;
	}
	
	public function decode(){
		$this->eid = $this->getInt();		
		$this->slots[0] = $this->getByte();
		$this->slots[1] = $this->getByte();
		$this->slots[2] = $this->getByte();
		$this->slots[3] = $this->getByte();
	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putByte($this->slots[0]);
		$this->putByte($this->slots[1]);
		$this->putByte($this->slots[2]);
		$this->putByte($this->slots[3]);
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