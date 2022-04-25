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

namespace arie\skywar\match;

use pocketmine\plugin\PluginBase;

class MatchManager{
	public function __construct(
		private PluginBase $plugin
	){

	}

	public function createMatch() : bool{
		return true;
	}

	/**
	 * @return PluginBase
	 */
	public function getPlugin() : PluginBase{
		return $this->plugin;
	}
}