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

use pocketmine\plugin\PluginBase;

final class LanguageManager{
	private string $filePath;
	private float $plugin_language_version;

	public const DEFAULT_LANGUAGE = "en-US";

	protected const SUPPORTED_LANGUAGES = [
		"en-US",
		"vi-VN"
	];

	protected string $language_id;
	protected array $languages = [];
	protected array $language_names = [];
	protected array $language_versions = [];
	
	protected array $raw_language;

	public function __construct(protected PluginBase $plugin){
		$this->filePath = $this->plugin->getDataFolder() . "language/";
		$this->plugin_language_version = (float) $this->plugin->getDescription()->getMap()["versions"]["language"];
		$language_id = $this->plugin->getConfig()->get("language", self::DEFAULT_LANGUAGE);

		if (!@mkdir($concurrentDirectory = $this->filePath) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf($this->getMessage(LanguageTag::ERROR_DIR_NOT_FOUND), $concurrentDirectory));
		}

		foreach (self::SUPPORTED_LANGUAGES as $language) {
			$this->plugin->saveResource("language/" . $language . ".yml");
		}

		foreach (glob($this->filePath . "*.yml") as $language) {
			$this->register(new Language($language), true);
		}

		if (!array_key_exists($language_id, $this->language_names)) {
			$this->plugin->getLogger()->notice($this->getMessage(LanguageTag::LANGUAGE_DEFAULT_NOT_EXIST,
				[
					TranslatorTag::LANG_ID => $language_id,
					TranslatorTag::DEFAULT_LANG_NAME => $this->getLanguageName()
				],
				self::DEFAULT_LANGUAGE,
				true
			));
			$language_id = self::DEFAULT_LANGUAGE;
		}
		if ($this->plugin_language_version > $this->language_versions[$language_id]) {
			$this->plugin->getLogger()->notice($this->getMessage(LanguageTag::LANGUAGE_OUTDATED,
				[
					TranslatorTag::LANG_ID => $this->language_versions[$language_id],
					TranslatorTag::LANG_NAME => $this->getLanguageName($language_id),
					TranslatorTag::PLUGIN_LANG_VER => $this->plugin_language_version
				],
				$language_id,
				true
			));
		}
		$this->language_id = $language_id;
		$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_SET,
			[
				TranslatorTag::LANG_NAME => $this->getLanguageName(),
				TranslatorTag::LANG_ID => $this->language_id,
				TranslatorTag::LANG_VER => $this->language_versions[$language_id],
			],
			raw: true
		));
	}

	public function register(Language $language, bool $replace = false) : bool{
		$language_id = $language->getLanguageId();
		if ($replace || !isset($this->languages[$language->getLanguageId()])) {
			$this->languages[$language_id] = $language;
			$this->language_names[$language_id] = $language->getLanguageName();
			$this->language_versions[$language_id] = $language->getLanguageVersion();
			return true;
		}
		return false;
	}

	public function setLanguage(string $language_id) : bool{
		if ($this->language_id === $language_id) {
			$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_ALREADY_SET,
				[
					TranslatorTag::LANG_NAME => $this->getLanguageName($language_id),
					TranslatorTag::LANG_ID => $language_id,
					TranslatorTag::LANG_VER => $this->language_versions[$language_id],
				],
				raw: true
			));
			return false;
		}
		if (!array_key_exists($language_id, $this->language_names)) {
			$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_NOT_SUPPORTED,
				[
					TranslatorTag::LANG_NAME => $this->getLanguageName($language_id),
				],
				raw: true
			));
			return false;
		}
		$this->language_id = $language_id;

		$this->raw_language = $this->getFactoryData($language_id);
		$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_SET,
			[
				TranslatorTag::LANG_NAME => $this->getLanguageName($language_id),
				TranslatorTag::LANG_ID => $language_id,
				TranslatorTag::LANG_VER => $this->language_versions[$language_id],
			],
			raw: true
		));
		return true;
	}


	public function getMessage(string $key, array $replacements = [], ?string $language_id = null, bool $raw = false) : ?string{
		$message = $this->languages[$language_id ?? $this->language_id]->getMessage($key);
		if ($message === null) {
			$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_KEY_NOT_FOUND,
				[
					"{MESSAGE_KEY}" => $key,
					TranslatorTag::LANG_NAME => $this->getLanguageName($language_id),
					TranslatorTag::LANG_ID => $language_id
				]
			));
			if (!$raw) {
				return null;
			}
			$message = $this->raw_language[$key] ?? null;
			if ($message === null) {
				return null;
			}
		}
		return empty($replacements) ? $message : strtr($message, $replacements);
	}

	public function getSupportedLanguageList() : array{
		return self::SUPPORTED_LANGUAGES;
	}

	public function getLanguage(string $id = self::DEFAULT_LANGUAGE) : Language{
		return $this->languages[$id];
	}

	public function getLanguageName(string $id = self::DEFAULT_LANGUAGE) : string{
		return $this->language_names[$id];
	}

	/**
	 * @return PluginBase
	 */
	public function getPlugin() : PluginBase{
		return $this->plugin;
	}

	public function getFactoryData(string $language_id = self::DEFAULT_LANGUAGE) : ?array{
		if (!in_array($language_id, self::SUPPORTED_LANGUAGES, true)) {
			$language_id = self::DEFAULT_LANGUAGE;
		}
		$resource = $this->plugin->getResource("language/$language_id.yml");
		if ($resource === null) {
			return null;
		}
		$data = yaml_parse(stream_get_contents($resource));
		fclose($resource);
		return $data;
	}


	/**
	 * @return array
	 */
	public function getLanguageList() : array{
		return $this->language_names;
	}

	/**
	 * @return string
	 */
	public function getLanguageId() : string{
		return $this->language_id;
	}

	public function getPluginLanguageVersion() : float{
		return $this->plugin_language_version;
	}

	public function getLanguageVersion(string $language_id = self::DEFAULT_LANGUAGE) : float{
		return $this->language_versions[$language_id];
	}
}