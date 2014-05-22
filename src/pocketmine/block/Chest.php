<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Tile;

class Chest extends Transparent{

	const SLOTS = 27;

	public function __construct($meta = 0){
		parent::__construct(self::CHEST, $meta, "Chest");
		$this->isActivable = true;
		$this->hardness = 15;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);

		$chest = false;
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		for($side = 2; $side <= 5; ++$side){
			if(($this->meta === 4 or $this->meta === 5) and ($side === 4 or $side === 5)){
				continue;
			}elseif(($this->meta === 3 or $this->meta === 2) and ($side === 2 or $side === 3)){
				continue;
			}
			$c = $this->getSide($side);
			if(($c instanceof TileChest) and $c->getMetadata() === $this->meta){
				if((($tile = $this->getLevel()->getTile($c)) instanceof TileChest) and !$tile->isPaired()){
					$chest = $tile;
					break;
				}
			}
		}

		$this->getLevel()->setBlock($block, $this, true, false, true);
		$nbt = new Compound(false, array(
			new Enum("Items", array()),
			new String("id", Tile::CHEST),
			new Int("x", $this->x),
			new Int("y", $this->y),
			new Int("z", $this->z)
		));
		$nbt->Items->setTagType(NBT::TAG_Compound);
		$tile = new TileChest($this->getLevel(), $nbt);

		if($chest instanceof TileChest){
			$chest->pairWith($tile);
			$tile->pairWith($chest);
		}

		return true;
	}

	public function onBreak(Item $item){
		$t = $this->getLevel()->getTile($this);
		if($t instanceof TileChest){
			$t->unpair();
		}
		$this->getLevel()->setBlock($this, new Air(), true, true, true);

		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			$top = $this->getSide(1);
			if($top->isTransparent !== true){
				return true;
			}

			$t = $this->getLevel()->getTile($this);
			$chest = false;
			if($t instanceof TileChest){
				$chest = $t;
			}else{
				$nbt = new Compound(false, array(
					new Enum("Items", array()),
					new String("id", Tile::CHEST),
					new Int("x", $this->x),
					new Int("y", $this->y),
					new Int("z", $this->z)
				));
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$chest = new TileChest($this->getLevel(), $nbt);
			}


			if(($player->gamemode & 0x01) === 0x01){
				return true;
			}

			$chest->openInventory($player);
		}

		return true;
	}

	public function getDrops(Item $item){
		$drops = array(
			array($this->id, 0, 1),
		);
		$t = $this->getLevel()->getTile($this);
		if($t instanceof Chest){
			for($s = 0; $s < Chest::SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > Item::AIR and $slot->getCount() > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->getCount());
				}
			}
		}

		return $drops;
	}
}