<?php

class PlayerActionPacket extends RakNetDataPacket{
	public $action;
	public $x;
	public $y;
	public $z;
	public $face;
	public $eid;
	
	public function pid(){
		return ProtocolInfo::PLAYER_ACTION_PACKET;
	}
	
	public function decode(){
		$this->action = $this->getInt();
		$this->x = $this->getInt();
		$this->y = $this->getInt();
		$this->z = $this->getInt();
		$this->face = $this->getInt();
		$this->eid = $this->getInt();
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