<?php

class AnimatePacket extends RakNetDataPacket{
	const ANIM_SWING_HAND = 0x1;
	const ANIM_STOP_SLEEP = 0x3;
	
	public $action;
	public $eid;
	
	public function pid(){
		return ProtocolInfo::ANIMATE_PACKET;
	}
	
	public function decode(){
		$this->action = $this->getByte();
		$this->eid = $this->getInt();
	}
	
	public function encode(){
		$this->reset();
		$this->putByte($this->action);
		$this->putInt($this->eid);
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