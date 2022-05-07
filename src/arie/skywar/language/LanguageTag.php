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

final class LanguageTag{
	public const ERROR_DIR_NOT_FOUND = "error.dir-not-found";

	public const LANGUAGE_ALREADY_SET = "language.already-set";
	public const LANGUAGE_DEFAULT_NOT_EXIST = "language.default-not-exist";
	public const LANGUAGE_KEY_NOT_FOUND = "language.key-not-found";
	public const LANGUAGE_NOT_SUPPORTED = "language.not-supported";
	public const LANGUAGE_OUTDATED = "language.outdated";
	public const LANGUAGE_SET = "language.set";
}