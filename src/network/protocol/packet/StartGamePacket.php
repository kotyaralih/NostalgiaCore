<?php

class StartGamePacket extends RakNetDataPacket{
	public $seed;
	public $generator;
	public $gamemode;
	public $eid;
	public $x;
	public $y;
	public $z;
	
	public function pid(){
		return ProtocolInfo::START_GAME_PACKET;
	}
	
	public function decode(){

	}	
	
	public function encode(){
		$this->reset();
		$this->putInt($this->seed);
		$this->putInt($this->generator);
		$this->putInt($this->gamemode);
		$this->putInt($this->eid);
		$this->putFloat($this->x);
		$this->putFloat($this->y);
		$this->putFloat($this->z);
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