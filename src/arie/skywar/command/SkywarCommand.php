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

namespace arie\command;

use pocketmine\command\CommandSender;

use skymin\CommandLib\BaseCommand;
use skymin\CommandLib\EnumFactory;
use skymin\CommandLib\EnumType;

class SkywarCommand extends BaseCommand{

	public function __construct() {
		parent::__construct('test');
		$this->addParameter(EnumFactory::create('pos', Enumtype::TARGET(), null, true));
		$this->addParameter(EnumFactory::create('test', 'test', ['t', 'e', 's', 't']));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		return;
	}
}