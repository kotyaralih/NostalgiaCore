<?php

class HayBaleBlock extends SolidBlock{
	public static $blockID;
	public function __construct($meta = 0){
		parent::__construct(HAY_BALE, $meta, "Hay Bale");
		$this->hardness = 2.5;
		$this->breakTime = 0.5;
		$this->material = Material::$dirt;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 0,
			1 => 0,
			2 => 0b1000,
			3 => 0b1000,
			4 => 0b0100,
			5 => 0b0100,
		);

		$this->meta = ($this->meta & 0x03) | $faces[$face];
		$this->level->setBlock($block, $this, true, false, true);
		return true;
	}

	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, 0, 1),
		);
	}
	
}