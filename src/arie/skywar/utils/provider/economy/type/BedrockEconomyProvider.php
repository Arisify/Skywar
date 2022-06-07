<?php
/**
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 * @author Arisify
 * @link   https://github.com/Arisify
 * @license https://opensource.org/licenses/MIT MIT License
 *
 * \    /\
 *  )  ( ') ᵐᵉᵒʷˢ
 * (  /  )
 *  \(__)|
 *
*/
declare(strict_types=1);

namespace arie\skywar\utils\provider\economy\type;

use arie\skywar\utils\provider\EconomyProvider;
use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\api\version\BetaBEAPI;
use cooldogedev\BedrockEconomy\BedrockEconomy;

class BedrockEconomyProvider implements EconomyProvider{
	private BetaBEAPI $economy;

	public function __construct(){
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