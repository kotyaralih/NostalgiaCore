<?php

abstract class RakNetDataPacket extends stdClass{

	public $buffer = b"";
	public $reliability = 0;
	public $hasSplit = false;
	public $messageIndex;
	public $orderIndex;
	public $orderChannel;
	public $splitCount;
	public $splitID;
	public $splitIndex;
	public $seqIndex;
	private $offset = 0;
	
	public abstract function encode();

	public abstract function decode();

	public $localEids = false;
	public function eidsToLocal(Player $p){
		return true;
	}
	public function eidsToGlobal(Player $p){
		return true;
	}
	
	public function getBuffer(){
		return $this->buffer;
	}
	
	public function getOffset(){
		return $this->offset;
	}
	
	public function setBuffer($buffer = ""){
		$this->buffer = $buffer;
		$this->offset = 0;
	}

	protected function reset(){
		$this->setBuffer(chr($this->pid()));
	}

	abstract public function pid();

	protected function getLong($unsigned = false){
		return Utils::readLong($this->get(8), $unsigned);
	}

	protected function get($len){
		if($len <= 0){
			$this->offset = strlen($this->buffer) - 1;
			return "";
		}elseif($len === true){
			return substr($this->buffer, $this->offset);
		}
		$this->offset += $len;
		return substr($this->buffer, $this->offset - $len, $len);
	}

	protected function putLong($v){
		$this->buffer .= Utils::writeLong($v);
	}

	protected function getInt($unsigned = false){
		return Utils::readInt($this->get(4), $unsigned);
	}

	protected function putInt($v){
		$this->buffer .= Utils::writeInt($v);
	}

	protected function getFloat(){
		return Utils::readFloat($this->get(4));
	}

	protected function putFloat($v){
		$this->buffer .= Utils::writeFloat($v);
	}

	protected function getLTriad(){
		return Utils::readTriad(strrev($this->get(3)));
	}

	protected function putLTriad($v){
		$this->buffer .= Utils::writeLTriad($v);
	}

	protected function getDataArray($len = 10){
		$data = [];
		for($i = 1; $i <= $len and !$this->feof(); ++$i){
			$data[] = $this->get($this->getTriad());
		}
		return $data;
	}

	protected function feof(){
		return !isset($this->buffer[$this->offset]);
	}

	protected function getTriad(){
		return Utils::readTriad($this->get(3));
	}

	protected function putDataArray(array $data = []){
		foreach($data as $v){
			$this->putTriad(strlen($v));
			$this->put($v);
		}
	}

	protected function putTriad($v){
		$this->buffer .= Utils::writeTriad($v);
	}

	protected function put($str){
		$this->buffer .= $str;
	}

	protected function getSlot(){
		$id = $this->getShort();
		$cnt = $this->getByte();
		return BlockAPI::getItem(
			$id,
			$this->getShort(),
			$cnt
		);
	}

	protected function getShort($unsigned = false){
		return Utils::readShort($this->get(2), $unsigned);
	}
	
	public function getSignedByte(){
		$b = ord($this->get(1));
		return $b >= 0x80 ? ($b-256) : $b;
	}
	
	protected function getByte(){
		return ord($this->get(1));
	}

	protected function putSlot(Item $item){
		$this->putShort($item->getID());
		$this->putByte($item->count);
		$this->putShort($item->getMetadata());
	}

	protected function putShort($v){
		$this->buffer .= Utils::writeShort($v);
	}

	protected function putByte($v){
		$this->buffer .= chr((int)$v);
	}

	protected function getString(){
		return $this->get($this->getShort(true));
	}

	protected function putString($v){
		$this->putShort(strlen($v));
		$this->put($v);
	}
}
