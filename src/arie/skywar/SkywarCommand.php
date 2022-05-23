<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 *  /\___/\
 *  )     (     @author Arisify
 * =\     /=
 *   )   (      @link   https://github.com/Arisify
 *  /     \     @license https://opensource.org/licenses/MIT MIT License
 *  )     (   /\
 * /       \ ( ') ⁿʸᵃⁿ
 * \       / /  )
 *  \__ __/ (__)|
 *     ))  (
 *    ((
 *     \)
*/
declare(strict_types=1);

namespace arie\skywar;

use JetBrains\PhpStorm\ArrayShape;
use pocketmine\command\CommandSender;

use pocketmine\player\Player;
use skymin\CommandLib\BaseCommand;
use skymin\CommandLib\Parameter;

class SkywarCommand extends BaseCommand{

	public function __construct(protected Skywar $plugin){
		parent::__construct("skywar", "Allow you to modify and join skywar", "/sw addmap|join|list|ui", ["sw"]);
		$this->addOverload([
			Parameter::create("manage", "manage", ["manage"]),
			Parameter::create("map", "map", ["map"]),
			Parameter::create("map-args", "map-args", ["add", "list", "set"], Parameter::FLAG_HAS_ENUM_CONSTRAINT),
		]);
		$this->addOverload([
			Parameter::create("manage", "manage", ["manage"]),
			Parameter::create("preset", "preset", ["preset"]),
			Parameter::create("preset-args", "preset-args", ["add", "list", "set"], Parameter::FLAG_HAS_ENUM_CONSTRAINT),
		]);
		$this->addOverload([Parameter::create("join", "join", ["join"])]);

		$this->addOverload([
			Parameter::create("lang", "lang", ["lang"]),
			Parameter::create("set", "set", ["set"]),
			Parameter::create("id", "language id", array_map('mb_strtolower', array_keys($this->plugin->getLanguage()->getLanguageList())))
		]);
		$this->addOverload([
			Parameter::create("lang", "lang", ["lang"]),
			Parameter::create("list", "list", ["list"])
		]);

		// /sw
		//   - join <>
		//   - manage
		//     - map
		//        - add
		//        - list
		//        - set
		//     - preset
		//        - add
		//        - list
		//   - setting
		//     - kdr-enabled
		//     - economy
		//     etc
		//   - help
		//     - manage:
		//     - setting:
		//   - ui
		//   - language:
		//     - set
		//     -
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		var_dump($commandLabel);
		var_dump($args);
		switch ($args[0]) {
			case "manage":
				if (!isset($args[1])) {
					if ($sender instanceof Player) {
						$sender->sendForm($this->plugin->getSkywarManagerUI());
					}
					break;
				}
				break;
			case "join":
				//TODO
				break;
			case "lang":
				if (!isset($args[1])) {
					if ($sender instanceof Player) {
						$sender->sendForm($this->plugin->getLanguageUI());
					}
					break;
				}
				switch ($args[1]) {
					case "set":
						$this->plugin->getLanguage()->setLanguage($args[1]);
						break;
					case "list":
						$language = $this->plugin->getLanguage();
						$contents = "LANGUAGE LIST: " . PHP_EOL;
						$len = max(array_map('mb_strlen', $language->getLanguageList()));

						foreach ($language->getLanguageList() as $id => $name) {
							$contents .= $name . str_repeat("1", $len - mb_strlen($name) + 6) . $id . PHP_EOL;
						}
						$sender->sendMessage($contents);
						break;
					default:
						$sender->sendMessage($this->getUsage());
				}

				break;
			default:
				$sender->sendMessage($this->getUsage());
		}
		/*switch ($args[0]) {
			case "addmap":
				if (!isset($args[1]) && !$sender instanceof Player) {
					$sender->sendMessage("Usage: /sw addmap");
				}
				break;
			case "join":
				if (!$sender instanceof Player) {
					$sender->sendMessage("This command can only be unused in game");
					return false;
				}
				$match = isset($args[1]) ? $arenaManager->getArena($args[1]) : $arenaManager->getAvailableArena();
				if ($match === null) {
					return false;
				}
				$match->join($sender);
				break;
			case "start":

				break;
			case "set":
				if (isset($args[1])) {
					$sender->sendMessage("/sw set <map id>");
					return false;
				}
				$arena = $arenaManager->getArena($args[1]);
				if (!$arena instanceof Arena) {
					$sender->sendMessage("Arena $args[1] is not exist or they have been removed from the game!");
					return false;
				}
				break;
			case "list":
				foreach ($arenaManager->getAllArena() as $arena) {
					$sender->sendMessage($arena->getName() . ":      $arena->getId()        Active");
				}
				break;
			case "ui":
				if (!$sender instanceof Player) {
					$sender->sendMessage("This command can only be used in-game!");
					break;
				}
				$sender->sendForm($this->plugin->getSkywarManagerUI());
				break;
			case "lang":
				if (!isset($args[1])) {
					if ($sender instanceof Player) {
						$sender->sendForm($this->plugin->getLanguageUI());
						break;
					}
					$sender->sendMessage($this->plugin->getLanguage()->getMessage("command.help.usage"));
					break;
				}
				break;
			default:
				$sender->sendMessage($this->getUsage());
		}
		*/
		return true;
	}

	/**
	 * @return Skywar
	 */
	public function getPlugin() : Skywar{
		return $this->plugin;
	}
}