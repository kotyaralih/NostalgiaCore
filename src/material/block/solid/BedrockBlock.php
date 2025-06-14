<?php

class BedrockBlock extends SolidBlock{
	public static $blockID;
	public function __construct(){
		parent::__construct(BEDROCK, 0, "Bedrock");
		$this->breakable = false;
		$this->hardness = 18000000;
		$this->breakTime = -1;
		$this->material = Material::$stone;
	}
	
	public function isBreakable(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return true;
		}
		return false;
	}
	
}