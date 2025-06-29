<?php

class UseItemPacket extends RakNetDataPacket{
	public $x;
	public $y;
	public $z;
	public $face;
	public $item;
	public $meta;
	public $eid;
	public $fx;
	public $fy;
	public $fz;
	public $posX;
	public $posY;
	public $posZ;
	
	public function pid(){
		return ProtocolInfo::USE_ITEM_PACKET;
	}
	
	public function decode(){
		$this->x = $this->getInt();
		$this->y = $this->getInt();
		$this->z = $this->getInt();
		$this->face = $this->getInt();
		$this->item = $this->getShort();
		$this->meta = $this->getByte();
		$this->eid = $this->getInt();
		$this->fx = $this->getFloat();
		$this->fy = $this->getFloat();
		$this->fz = $this->getFloat();
		$this->posX = $this->getFloat();
		$this->posY = $this->getFloat();
		$this->posZ = $this->getFloat();
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
