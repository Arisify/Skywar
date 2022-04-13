<?php
declare(strict_types=1);

use arie\skywar\economy\EconomyProvider;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\BetaBEAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\promise\Promise;

class BedrockEconomyProvider implements EconomyProvider{
	private BetaBEAPI $economy;

	public function __construct() {
		$this->economy = BedrockEconomyAPI::beta();
	}

	public function addMoney(string $user, int|float $amount) : void{
		$this->economy->add;
	}

	public function removeMoney(string $user, int|float $amount, Closure $onSuccess, Closure $onFailure) : void{
		$this->economy->getAccountManager()->deduct($user, $amount)->onCompletion($onSuccess, $onFailure);
	}

	public function getMoney(string $user, Closure $onSuccess, Closure $onFailure) : void{
		$this->economy->get($user)->onCompletion($onSuccess, $onFailure);
	}

	public function getCurrencySymbol() : string{
		return BedrockEconomy::getInstance()->getCurrencyManager()->getSymbol();
	}

	public function getCurrencyName() : string{
		return BedrockEconomy::getInstance()->getCurrencyManager()->getSymbol();
	}

	public function getName() : string{
		// TODO: Implement getName() method.
	}
}