<?php

class CobwebBlock extends FlowableBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(COBWEB, 0, "Cobweb");		
		$this->isSolid = true;
		$this->isFullBlock = false;
		$this->hardness = 30;
		$this->breakTime = 4;
		$this->material = Material::$web;
		$this->lightBlock = 1;
	}
	
	public static function getAABB(Level $level, $x, $y, $z){
		return null;
	}
	
	public function getDrops(Item $item, Player $player){
		if ($item->isSword()){
			return [
				[STRING, 0, 1],
			];
		}
		elseif ($item->isShears()){
			return [
				array(COBWEB, 0, 1),
			];
		}
	}
	
	public static function onEntityCollidedWithBlock(Level $level, $x, $y, $z, Entity $entity){
		$entity->setInWeb();
	}
}