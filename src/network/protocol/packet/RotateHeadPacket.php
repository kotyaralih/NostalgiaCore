<?php

class RotateHeadPacket extends RakNetDataPacket{
	public $eid;
	public $yaw;
	/**
	 * Should yaw be modifed<br>
	 * false = <b>yaw  / 360 / 0.0039062</b><br>
	 * true = <b>yaw</b>
	 * @var boolean
	 */
	public $rawYaw = false;
	public function pid(){
		return ProtocolInfo::ROTATE_HEAD_PACKET;
	}
	
	public function decode(){
	   $this->get(7); //id + data
	   $this->eid = $this->getInt();
	   $this->yaw = $this->getByte();
	}
	
	public function encode(){
		$this->reset();
		$this->putInt($this->eid);
		$this->putByte($this->rawYaw ? $this->yaw : ($this->yaw / 360 / 0.0039062)); //wraps 360 angle to 0xff
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