<?php
/*
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

namespace arie\skywar\utils\provider\economy;

interface EconomyProvider{
	public function addMoney(string $user, int|float $amount) : void;
	public function removeMoney(string $user, int|float $amount) : void;
	public function getMoney(string $user);

	public function getCurrencySymbol() : string;
	public function getName() : string;
}