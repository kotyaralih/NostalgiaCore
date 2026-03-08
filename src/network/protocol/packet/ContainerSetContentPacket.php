<?php

class ContainerSetContentPacket extends RakNetDataPacket{
	public $windowid;
	public $slots = array();
	public $hotbar = array();
	
	public function pid(){
		return ProtocolInfo::CONTAINER_SET_CONTENT_PACKET;
	}
	
	public function decode(){
		$this->windowid = $this->getByte();
		$count = $this->getShort();
		for($s = 0; $s < $count and !$this->feof(); ++$s){
			$this->slots[$s] = $this->getSlot();
		}
		if($this->windowid === 0){
			$count = $this->getShort();
			for($s = 0; $s < $count and !$this->feof(); ++$s){
				$this->hotbar[$s] = $this->getInt();
			}
		}
	}
	
	public function encode(){
		$this->reset();
		$this->putByte($this->windowid);
		$this->putShort(count($this->slots));
		foreach($this->slots as $slot){
			$this->putSlot($slot);
		}
		if($this->windowid === 0 and count($this->hotbar) > 0){
			$this->putShort(count($this->hotbar));
			foreach($this->hotbar as $slot){
				$this->putInt($slot);
			}
		}else{
			$this->putShort(0);
		}
	}

}
