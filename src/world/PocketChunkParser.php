<?php

class PocketChunkParser{

	var $sectorLength = 4096;
	var $chunkLength = 86016; //16 * 16 * 16
	var $map; //21 * $sectorLength
	private $location, $raw = b"", $file;

	public function __destruct(){
		unset($this->location);
		unset($this->raw);
		unset($this->file);
		unset($this->map);
	}
	
	public function loadFile($file){
		if(file_exists($file . ".gz")){
			$this->raw = gzinflate(file_get_contents($file . ".gz"));
			$r = @gzinflate($this->raw);
			if($r !== false and $r != ""){
				$this->raw = $r;
			}
			@unlink($file . ".gz");
			file_put_contents($file, $this->raw);
		}elseif(!file_exists($file)){
			return false;
		}else{
			$this->raw = file_get_contents($file);
		}
		$this->file = $file;
		$this->chunkLength = $this->sectorLength * ord($this->raw[0]);
		return true;
	}

	public function loadRaw($raw, $file){
		$this->file = $file;
		$this->raw = $raw;
		$this->chunkLength = $this->sectorLength * ord($this->raw[0]);
		return true;
	}

	public function getChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		return substr($this->raw, $this->getOffset($X, $Z), $this->chunkLength);
	}

	private function getOffset($X, $Z){
		return $this->location[$X + ($Z << 5)];
	}

	public function loadMap(){
		if($this->raw == ""){
			return false;
		}
		$this->loadLocationTable();
		console("[DEBUG] Loading chunks...", true, true, 2);
		for($x = 0; $x < 16; ++$x){
			$this->map[$x] = [];
			for($z = 0; $z < 16; ++$z){
				$this->map[$x][$z] = $this->parseChunk($x, $z);
			}
		}
		$this->raw = b"";
		console("[DEBUG] Chunks loaded!", true, true, 2);
	}

	private function loadLocationTable(){
		$this->location = [];
		console("[DEBUG] Loading Chunk Location table...", true, true, 2);
		for($offset = 0; $offset < 0x1000; $offset += 4){
			$data = Utils::readLInt(substr($this->raw, $offset, 4));
			$sectors = $data & 0xff;
			if($sectors === 0){
				continue;
			}
			$sectorLocation = $data >> 8;
			$this->location[$offset >> 2] = $sectorLocation * $this->sectorLength;//$this->getOffset($X, $Z, $sectors);
		}
	}

	public function parseChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		$offset = $this->getOffset($X, $Z);
		$len = Utils::readLInt(substr($this->raw, $offset, 4));
		$offset += 4;
		$chunk = [
			0 => [], //Block
			1 => [], //Data
			2 => [], //SkyLight
			3 => [], //BlockLight
		];
		foreach($chunk as $section => &$data){
			$l = $section === 0 ? 128 : 64;
			for($i = 0; $i < 256; ++$i){
				$data[$i] = substr($this->raw, $offset, $l);
				$offset += $l;
			}
		}
		return $chunk;
	}

	public function saveMap($final = false){
		console("[DEBUG] Saving chunks...", true, true, 2);

		$fp = fopen($this->file, "r+b");
		flock($fp, LOCK_EX);
		foreach($this->map as $x => $d){
			foreach($d as $z => $chunk){
				fseek($fp, $this->getOffset($x, $z));
				fwrite($fp, $this->writeChunk($x, $z), $this->chunkLength);
			}
		}
		flock($fp, LOCK_UN);
		fclose($fp);
		$original = filesize($this->file);
		file_put_contents($this->file . ".gz", gzdeflate(gzdeflate(file_get_contents($this->file), 9), 9)); //Double compression for flat maps
		$compressed = filesize($this->file . ".gz");
		console("[DEBUG] Saved chunks.dat.gz with " . round(($compressed / $original) * 100, 2) . "% (" . round($compressed / 1024, 2) . "KB) of the original size", true, true, 2);
		if($final === true){
			@unlink($this->file);
		}
	}

	public function writeChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!isset($this->map[$X][$Z])){
			return false;
		}
		$chunk = "";
		foreach($this->map[$X][$Z] as $section => $data){
			for($i = 0; $i < 256; ++$i){
				$chunk .= $data[$i];
			}
		}
		return Utils::writeLInt(strlen($chunk)) . $chunk;
	}

	public function getFloor($x, $z){
		$X = $x >> 4;
		$Z = $z >> 4;
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$index = $aZ + ($aX << 4);
		for($y = 127; $y <= 0; --$y){
			if($this->map[$X][$Z][0][$index][$y] !== "\x00"){
				break;
			}
		}
		return $y;
	}

	public function getBlock($x, $y, $z){
		$x = (int) $x;
		$y = (int) $y;
		$z = (int) $z;
		$X = $x >> 4;
		$Z = $z >> 4;
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$index = $aZ + ($aX << 4);
		$block = ord($this->map[$X][$Z][0][$index][$y]);
		$meta = ord($this->map[$X][$Z][1][$index][$y >> 1]);
		if(($y & 1) === 0){
			$meta = $meta & 0x0F;
		}else{
			$meta = $meta >> 4;
		}
		return [$block, $meta];
	}

	public function getChunkColumn($X, $Z, $x, $z, $type = 0){
		$index = $z + ($x << 4);
		return $this->map[$X][$Z][$type][$index];
	}

	public function setBlock($x, $y, $z, $block, $meta = 0){
		$x = (int) $x;
		$y = (int) $y;
		$z = (int) $z;
		$X = $x >> 4;
		$Z = $z >> 4;
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$index = $aZ + ($aX << 4);
		$this->map[$X][$Z][0][$index][$y] = chr($block);
		$old_meta = ord($this->map[$X][$Z][1][$index][$y >> 1]);
		if(($y & 1) === 0){
			$meta = ($old_meta & 0xF0) | ($meta & 0x0F);
		}else{
			$meta = (($meta << 4) & 0xF0) | ($old_meta & 0x0F);
		}
		$this->map[$X][$Z][1][$index][$y >> 1] = chr($meta);
	}

}