<?php
/*
Lets hope it will work
*/
class SetEntityLinkPacket extends RakNetDataPacket{

	const TYPE_REMOVE = 0;
	const TYPE_RIDE = 1;
	const TYPE_PASSENGER = 2;

	public $rider;
	public $riding;
	public $type;
	public function decode() {
		$this->rider = $this->getInt();
		$this->riding = $this->getInt();
		$this->type = $this->getInt();
	}
	public function encode() {
		$this->reset();
		$this->putInt($this->rider);
		$this->putInt($this->riding);
		$this->putInt($this->type);
	}


	public function pid(){
		return ProtocolInfo::SET_ENTITY_LINK_PACKET;
	}

	public function eidsToGlobal(Player $p){
		if($this->localEids){
			$this->localEids = false;
			$this->rider = $p->local2GlobalEID[$this->rider] ?? false;
			$this->riding = $p->local2GlobalEID[$this->riding] ?? false;
			if($this->rider === false || $this->riding === false) return false;
		}
		return true;
	}
	
	public function eidsToLocal(Player $p){
		if(!$this->localEids){
			$this->localEids = true;
			$this->rider = $p->global2localEID[$this->rider] ?? false;
			$this->riding = $p->global2localEID[$this->riding] ?? false;
			if($this->rider === false || $this->riding === false) return false;
		}
		return true;
	}
}