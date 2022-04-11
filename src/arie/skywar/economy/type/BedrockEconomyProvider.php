<?php
declare(strict_types=1);

use arie\skywar\economy\EconomyProvider;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\LegacyBEAPI;
use cooldogedev\libSQL\context\ClosureContext;
use pocketmine\player\Player;

class BedrockEconomyProvider implements EconomyProvider{
	private LegacyBEAPI $economy;

	public function __construct() {
		$this->economy = BedrockEconomyAPI::legacy();
	}

	public function addMoney(Player $player, int|float $amount) : void{
		$this->economy->addToPlayerBalance($player->getName(), (int) floor($amount));
	}

	public function removeMoney(Player $player, int|float $amount) : void{
		$this->economy->subtractFromPlayerBalance($player->getName(), (int) floor($amount));
	}

	public function getMoney(Player $player) : int{
		$b = 0;
		$this->economy->getPlayerBalance($player->getName(), ClosureContext::create(
			static fn(int $balance) : int => $b = $balance
		));
		return $b;
	}

	public function getCurrencySymbol() : string{
		// TODO: Implement getCurrencySymbol() method.
	}

	public function getName() : string{
		// TODO: Implement getName() method.
	}
}