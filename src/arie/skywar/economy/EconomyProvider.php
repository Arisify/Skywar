<?php
declare(strict_types=1);

namespace arie\skywar\economy;

interface EconomyProvider{
	public function addMoney(string $user, int|float $amount) : void;
	public function removeMoney(string $user, int|float $amount) : void;
	public function getMoney(string $user);

	public function getCurrencySymbol() : string;
	public function getName() : string;
}