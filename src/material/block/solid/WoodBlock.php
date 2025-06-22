<?php

class WoodBlock extends SolidBlock{
	public static $blockID;
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	const JUNGLE = 3;
	
	public function __construct($meta = 0){
		parent::__construct(WOOD, $meta, "Wood");
		$names = array(
			WoodBlock::OAK => "Oak Wood",
			WoodBlock::SPRUCE => "Spruce Wood",
			WoodBlock::BIRCH => "Birch Wood",
			WoodBlock::JUNGLE => "Jungle Wood",
		);
		$this->name = $names[$this->meta & 0x03];
		$this->hardness = 10;
		$this->breakTime = 2.0;
		$this->material = Material::$wood;
		$this->lightBlock = 255;
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
	public function onBreak(Item $item, Player $player){
		parent::onBreak($item, $player);
		for($xOff = -4; $xOff <= 4; ++$xOff){
			for($yOff = -4; $yOff <= 4; ++$yOff){
				for($zOff = -4; $zOff <= 4; ++$zOff){
					$b = $player->level->level->getBlock($this->x + $xOff, $this->y + $yOff, $this->z + $zOff);
					$id = $b[0];
					$meta = $b[1];
					if($id === LEAVES){
						if(($meta & 0x8) === 0){
							$player->level->fastSetBlockUpdate($this->x + $xOff, $this->y + $yOff, $this->z + $zOff, $id, $meta | 8);
						}
					}
				}
			}
		}
	}
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, $this->meta & 0x03, 1),
		);
	}
}