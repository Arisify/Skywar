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
 * •.,¸,.•*`•.,¸¸,.•*¯ ╭━━━━━━━━━━━━╮
 * •.,¸,.•*¯`•.,¸,.•*¯.|:::::::/\___/\
 * •.,¸,.•*¯`•.,¸,.•* <|:::::::(｡ ●ω●｡)
 * •.,¸,.•¯•.,¸,.•╰ *  し------し---Ｊ
 *
 *
*/
declare(strict_types=1);

namespace arie\skywar\language;

use pocketmine\utils\EnumTrait;

class TranslatorTag{
	public const LANG_NAME = "{LANG_NAME}";
	public const LANG_VER = "{LANG_VER}";
	public const LANG_ID = "{LANG_ID}";
	public const DEFAULT_LANG_VER = "{DEFAULT_LANG_VER}";
	public const DEFAULT_LANG_NAME = "{DEFAULT_LANG_NAME}";
	public const DEFAULT_LANG_ID = "{DEFAULT_LANG_ID}";

	public const PLUGIN_LANG_VER = "{PLUGIN_LANG_VER}";
	public const PLUGIN_LANG_NAME = "{PLUGIN_LANG_NAME}";
	public const PLUGIN_LANG_ID = "{PLUGIN_LANG_ID}";

	public const PLAYER = "{PLAYER}";
	public const KILLER = "{KILLER}";
	public const CURRENT = "{CURRENT}";
	public const CMD = "{CMD}";
	public const SLOT = "{SLOT}";
	public const DIR = "{DIR}";
	public const OLD_LANG_NAME = "{OLD_LANG_NAME}";
}