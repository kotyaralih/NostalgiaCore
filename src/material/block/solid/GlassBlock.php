<?php

class GlassBlock extends TransparentBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(GLASS, 0, "Glass");
		$this->hardness = 1.5;
		$this->breakTime = 0.3;
		$this->material = Material::$glass;
	}
	
	public function getDrops(Item $item, Player $player){
		return array();
	}
	
	public static function getCollisionBoundingBoxes(Level $level, $x, $y, $z, Entity $entity){
		return [new AxisAlignedBB($x, $y, $z, $x + 1, $y + 1, $z + 1)];
	}
}