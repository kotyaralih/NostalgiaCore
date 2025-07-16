<?php
class AxisAlignedBB{
	public $minX;
	public $minY;
	public $minZ;
	public $maxX;
	public $maxY;
	public $maxZ;
	
	public function __construct($minX, $minY, $minZ, $maxX, $maxY, $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;
	}

	public function setBounds($minX, $minY, $minZ, $maxX, $maxY, $maxZ){
		$this->minX = $minX;
		$this->minY = $minY;
		$this->minZ = $minZ;
		$this->maxX = $maxX;
		$this->maxY = $maxY;
		$this->maxZ = $maxZ;

		return $this;
	}

	public function addMinMax($minX, $minY, $minZ, $maxX, $maxY, $maxZ){
		return new AxisAlignedBB($this->minX + $minX, $this->minY + $minY, $this->minZ + $minZ, $this->maxX + $maxX, $this->maxY + $maxY, $this->maxZ + $maxZ);
	}
	
	public function addCoord($x, $y, $z){
		$minX = $this->minX;
		$minY = $this->minY;
		$minZ = $this->minZ;
		$maxX = $this->maxX;
		$maxY = $this->maxY;
		$maxZ = $this->maxZ;

		if($x < 0){
			$minX += $x;
		}elseif($x > 0){
			$maxX += $x;
		}

		if($y < 0){
			$minY += $y;
		}elseif($y > 0){
			$maxY += $y;
		}

		if($z < 0){
			$minZ += $z;
		}elseif($z > 0){
			$maxZ += $z;
		}

		return new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
	}

	public function grow($x, $y, $z){
		return new AxisAlignedBB($this->minX - $x, $this->minY - $y, $this->minZ - $z, $this->maxX + $x, $this->maxY + $y, $this->maxZ + $z);
	}

	public function expand($x, $y, $z){
		return new AxisAlignedBB($this->minX - $x, $this->minY - $y, $this->minZ - $z, $this->maxX + $x, $this->maxY + $y, $this->maxZ + $z);
	}

	public function offset($x, $y, $z){
		$this->minX += $x;
		$this->minY += $y;
		$this->minZ += $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;
		
		if($this->minX < 1e-6 && $this->minX > -1e-6)$this->minX = 0;
		if($this->minY < 1e-6 && $this->minY > -1e-6)$this->minY = 0;
		if($this->minZ < 1e-6 && $this->minZ > -1e-6)$this->minZ = 0;
		if($this->maxX < 1e-6 && $this->maxX > -1e-6)$this->maxX = 0;
		if($this->maxY < 1e-6 && $this->maxY > -1e-6)$this->maxY = 0;
		if($this->maxZ < 1e-6 && $this->maxZ > -1e-6)$this->maxZ = 0;
		
		return $this;
	}

	public function shrink($x, $y, $z){
		return new AxisAlignedBB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX - $x, $this->maxY - $y, $this->maxZ - $z);
	}

	public function contract($x, $y, $z){
		return new AxisAlignedBB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX - $x, $this->maxY - $y, $this->maxZ - $z);
	}

	public function setBB(AxisAlignedBB $bb){
		$this->minX = $bb->minX;
		$this->minY = $bb->minY;
		$this->minZ = $bb->minZ;
		$this->maxX = $bb->maxX;
		$this->maxY = $bb->maxY;
		$this->maxZ = $bb->maxZ;
		return $this;
	}

	public function getOffsetBoundingBox($x, $y, $z){
		return new AxisAlignedBB($this->minX + $x, $this->minY + $y, $this->minZ + $z, $this->maxX + $x, $this->maxY + $y, $this->maxZ + $z);
	}

	public function calculateXOffset(AxisAlignedBB $bb, $x){
		if($bb->maxY <= $this->minY or $bb->minY >= $this->maxY){
			return $x;
		}
		if($bb->maxZ <= $this->minZ or $bb->minZ >= $this->maxZ){
			return $x;
		}
		if($x > 0 and $bb->maxX <= $this->minX){
			$x1 = $this->minX - $bb->maxX;
			if($x1 < $x){
				$x = $x1;
			}
		}
		if($x < 0 and $bb->minX >= $this->maxX){
			$x2 = $this->maxX - $bb->minX;
			if($x2 > $x){
				$x = $x2;
			}
		}

		return $x;
	}

	public function calculateYOffset(AxisAlignedBB $bb, $y){
		if($bb->maxX <= $this->minX or $bb->minX >= $this->maxX){
			return $y;
		}
		if($bb->maxZ <= $this->minZ or $bb->minZ >= $this->maxZ){
			return $y;
		}
		if($y > 0 and $bb->maxY <= $this->minY){
			$y1 = $this->minY - $bb->maxY;
			if($y1 < $y){
				$y = $y1;
			}
		}
		if($y < 0 and $bb->minY >= $this->maxY){
			$y2 = $this->maxY - $bb->minY;
			if($y2 > $y){
				$y = $y2;
			}
		}

		return $y;
	}

	public function calculateZOffset(AxisAlignedBB $bb, $z){
		if($bb->maxX <= $this->minX or $bb->minX >= $this->maxX){
			return $z;
		}
		if($bb->maxY <= $this->minY or $bb->minY >= $this->maxY){
			return $z;
		}
		if($z > 0 and $bb->maxZ <= $this->minZ){
			$z1 = $this->minZ - $bb->maxZ;
			if($z1 < $z){
				$z = $z1;
			}
		}
		if($z < 0 and $bb->minZ >= $this->maxZ){
			$z2 = $this->maxZ - $bb->minZ;
			if($z2 > $z){
				$z = $z2;
			}
		}

		return $z;
	}
	
	public function intersectsWith_(AxisAlignedBB $bb){
		return $bb->maxX > $this->minX && $bb->minX < $this->maxX && $bb->maxY > $this->minY && $bb->minY < $this->maxY && $bb->maxZ > $this->minZ && $bb->minZ < $this->maxZ;
	}
	
	public function intersectsWith(AxisAlignedBB $bb){
		return $bb->maxX >= $this->minX && $bb->minX <= $this->maxX && $bb->maxZ >= $this->minZ && $bb->minZ <= $this->maxZ && $bb->maxY >= $this->minY && $bb->minY <= $this->maxY;
	}
	public function isXYZInsideNS($x, $y, $z){
		if($x < $this->minX or $x > $this->maxX){
			return false;
		}
		if($y < $this->minY or $y > $this->maxY){
			return false;
		}
		
		return $z > $this->minZ and $z < $this->maxZ;
	}
	public function isXYZInside($x, $y, $z){
		if($x < $this->minX or $x > $this->maxX){
			return false;
		}
		if($y < $this->minY or $y > $this->maxY){
			return false;
		}
		
		return $z > $this->minZ and $z < $this->maxZ;
	}
	public function isVectorInside(Vector3 $vector){
		if($vector->x < $this->minX or $vector->x > $this->maxX){
			return false;
		}
		if($vector->y < $this->minY or $vector->y > $this->maxY){
			return false;
		}

		return $vector->z > $this->minZ and $vector->z < $this->maxZ;
	}

	public function getAverageEdgeLength(){
		return ($this->maxX - $this->minX + $this->maxY - $this->minY + $this->maxZ - $this->minZ) / 3;
	}

	public function isVectorInYZ(Vector3 $vector){
		return $vector->y >= $this->minY and $vector->y <= $this->maxY and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	public function isVectorInXZ(Vector3 $vector){
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->z >= $this->minZ and $vector->z <= $this->maxZ;
	}

	public function isVectorInXY(Vector3 $vector){
		return $vector->x >= $this->minX and $vector->x <= $this->maxX and $vector->y >= $this->minY and $vector->y <= $this->maxY;
	}
	
	/**
	 * @param Vector3 $pos1
	 * @param Vector3 $pos2
	 * @return NULL|MovingObjectPosition
	 */
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2){
		$v1 = $pos1->clipX($pos2, $this->minX);
		$v2 = $pos1->clipX($pos2, $this->maxX);
		$v3 = $pos1->clipY($pos2, $this->minY);
		$v4 = $pos1->clipY($pos2, $this->maxY);
		$v5 = $pos1->clipZ($pos2, $this->minZ);
		$v6 = $pos1->clipZ($pos2, $this->maxZ);

		if($v1 !== null and !$this->isVectorInYZ($v1)){
			$v1 = null;
		}

		if($v2 !== null and !$this->isVectorInYZ($v2)){
			$v2 = null;
		}

		if($v3 !== null and !$this->isVectorInXZ($v3)){
			$v3 = null;
		}

		if($v4 !== null and !$this->isVectorInXZ($v4)){
			$v4 = null;
		}

		if($v5 !== null and !$this->isVectorInXY($v5)){
			$v5 = null;
		}

		if($v6 !== null and !$this->isVectorInXY($v6)){
			$v6 = null;
		}

		$vector = $v1;

		if($v2 !== null and ($vector === null or $pos1->distanceSquared($v2) < $pos1->distanceSquared($vector))){
			$vector = $v2;
		}

		if($v3 !== null and ($vector === null or $pos1->distanceSquared($v3) < $pos1->distanceSquared($vector))){
			$vector = $v3;
		}

		if($v4 !== null and ($vector === null or $pos1->distanceSquared($v4) < $pos1->distanceSquared($vector))){
			$vector = $v4;
		}

		if($v5 !== null and ($vector === null or $pos1->distanceSquared($v5) < $pos1->distanceSquared($vector))){
			$vector = $v5;
		}

		if($v6 !== null and ($vector === null or $pos1->distanceSquared($v6) < $pos1->distanceSquared($vector))){
			$vector = $v6;
		}

		if($vector === null){
			return null;
		}

		$f = -1;

		if($vector === $v1){
			$f = 4;
		}elseif($vector === $v2){
			$f = 5;
		}elseif($vector === $v3){
			$f = 0;
		}elseif($vector === $v4){
			$f = 1;
		}elseif($vector === $v5){
			$f = 2;
		}elseif($vector === $v6){
			$f = 3;
		}

		return MovingObjectPosition::fromBlock(0, 0, 0, $f, $vector);
	}
	
	public function __toString(){
		return "AABB min: {$this->minX}, {$this->minY}, {$this->minZ}, max:  {$this->maxX}, {$this->maxY}, {$this->maxZ}";
	}
	
}