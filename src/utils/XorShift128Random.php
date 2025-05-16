<?php

class XorShift128Random{
	public $x, $y, $z, $w;
	
	private $haveNextNextGaussian = false;
	private $nextNextGaussian = 0;
	
	public function __construct($seed = null){
		$this->setSeed($seed === null ? microtime(true) : $seed);
	}

	public function nextGaussian(){
		if($this->haveNextNextGaussian){
			$this->haveNextNextGaussian = false;
			return $this->nextNextGaussian;
		}else{
			do{
				$v1 = 2 * $this->nextFloat() - 1;   // between -1.0 and 1.0
				$v2 = 2 * $this->nextFloat() - 1;   // between -1.0 and 1.0
				$s = $v1 * $v1 + $v2 * $v2;
			}while($s >= 1 || $s == 0);
			$multiplier = sqrt(-2 * log($s) / $s);
			$this->nextNextGaussian = $v2 * $multiplier;
			$this->haveNextNextGaussian = true;
			return $v1 * $multiplier;
		}
	}
	
	public function setSeed($seed){
		$this->x = 123456789 ^ $seed;
		$this->y = 362436069 ^ ($seed << 17) | (($seed >> 15) & 0x7fffffff) & 0xffffffff;
		$this->z = 521288629 ^ ($seed << 31) | (($seed >> 1 ) & 0x7fffffff) & 0xffffffff;
		$this->w = 88675123 ^  ($seed << 18) | (($seed >> 14) & 0x7fffffff) & 0xffffffff;
	}
	
	public function gen(){
		$t = ($this->x ^ ($this->x << 11)) & 0xffffffff;
		$this->x = $this->y;
		$this->y = $this->z;
		$this->z = $this->w;
		$this->w = ($this->w ^ (($this->w >> 19) & 0x7fffffff) ^ ($t ^ (($t >> 8) & 0x7fffffff))) & 0xffffffff;
		
		return $this->w << 32 >> 32;
	}
	
	public function nextInt($bound = null){
		if($bound == null) return $this->gen();
		return (($this->gen() & 0x7fffffff) % $bound);
	}

	public function nextFloat(){
		return (($this->gen() & 0x7fffffff) / 0x7fffffff);
	}
}
