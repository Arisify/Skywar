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

use pocketmine\utils\TextFormat;

final class Language{

	protected array $messages;

	public function __construct(
		protected string $filePath,
		protected ?string $language_id = null,
		protected ?string $language_name = null,
		protected ?float  $language_version = null
	){
		if (!is_file($this->filePath)) {
			return;
		}
		$data = yaml_parse_file($this->filePath);
		$this->language_id ??= basename($this->filePath, '.yml');
		$this->language_name ??= (string) ($data["lang.name"] ?? "unknown");
		$this->language_version ??= (float) ($data["lang.version"] ?? -1.0);

		$this->messages = array_map(static fn(string $message) : string => TextFormat::colorize($message), $data);
	}

	public static function create(string $filePath, string $language_id, string $language_name = "unknown", float $language_version = -1.0) : Language{
		return new Language($filePath, $language_id, $language_name, $language_version);
	}

	public function getMessage(string $key) : ?string{
		return $this->messages[$key] ?? null;
	}

	/**
	 * @return string
	 */
	public function getLanguageName() : string{
		return $this->language_name;
	}

	/**
	 * @return float
	 */
	public function getLanguageVersion() : float{
		return $this->language_version;
	}

	/**
	 * @return string
	 */
	public function getLanguageId() : string{
		return $this->language_id;
	}
}
