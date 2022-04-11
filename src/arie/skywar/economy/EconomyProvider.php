<?php
declare(strict_types=1);

namespace arie\skywar\economy;

use pocketmine\player\Player;

interface EconomyProvider{
	public function addMoney(Player $player, int|float $amount) : void;
	public function removeMoney(Player $player, int|float $amount) : void;
	public function getMoney(Player $player);

	public function getCurrencySymbol() : string;
	public function getName() : string;
}