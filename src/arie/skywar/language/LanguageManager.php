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

namespace arie\skywar\language;

use pocketmine\plugin\PluginLogger;
use pocketmine\utils\TextFormat;

use arie\skywar\Skywar;

//Todo: Add random message logic, multi player language
final class LanguageManager{
	private PluginLogger $logger;
	private string $filePath;
	private float $plugin_language_version;

	public const DEFAULT_LANGUAGE = "en-US";
	protected const SUPPORTED_LANGUAGES = [
		"en-US",
		"vi-VN"
	];
	protected const LANGUAGE_INFO = [
		"LANG_VERSION" => -1.0,
		"LANG_NAME" => 'unknown',
	];

	protected string $language_id;
	protected array $language_names = [];
	protected array $language_versions = [];
	protected array $languages = [];
	protected array $raw_language = [];

	public function __construct(private Skywar $plugin){
		$this->logger = $plugin->getLogger();
		$this->filePath = $this->plugin->getDataFolder() . "langs/";
		$this->plugin_language_version = (float) $this->plugin->getDescription()->getMap()["versions"]["language"];
		$language_id = $this->plugin->getConfig()->get("language", self::DEFAULT_LANGUAGE);

		if (!@mkdir($concurrentDirectory = $this->filePath) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf($this->getMessage('error.dir-not-found'), $concurrentDirectory));
		}

		foreach (self::SUPPORTED_LANGUAGES as $lang) {
			$this->plugin->saveResource("langs/" . $lang . ".yml");
		}

		foreach (glob($this->filePath . "*.yml") as $lang) {
			$data = yaml_parse_file($lang);
			$id = basename($lang, '.yml');
			$this->language_versions[$id] = (float) ($data["lang.version"] ?? -1.0);
			$this->language_names[$id] = (string) ($data["lang.name"] ?? "unknown");
			$this->languages[$id] = array_map(static fn(string $message) : string => TextFormat::colorize($message), $data);
		}
		if (!array_key_exists($language_id, $this->language_names)) {
			$this->logger->notice($this->getMessage("language.default-not-exist",
				[
					"{LANG_ID}" => $language_id,
					"{DEFAULT_LANG_NAME}" => $this->getLanguageName()
				],
				self::DEFAULT_LANGUAGE
			));
			$language_id = self::DEFAULT_LANGUAGE;
		}
		if ($this->plugin_language_version > $this->language_versions[$language_id]) {
			$this->logger->notice($this->getMessage("language.outdated",
				[
					"{LANG_VER}" => $this->language_versions[$language_id],
					"{LANG_NAME}" => $this->getLanguageName($language_id),
					"{PLUGIN_LANG_VER}" => $this->plugin_language_version
				],
				$language_id
			));
		}
		$this->language_id = $language_id;
		$this->logger->info($this->getMessage("language.set",
			[
				"{LANG_NAME}" => $this->getLanguageName(),
				"{LANG_ID}" => $this->language_id,
				"{LANG_VER}" => $this->language_versions[$language_id],
			]
		));
	}

	public function getMessage(string $key, array $replacements = [], ?string $language_id = null) : ?string{
		$language_id ??= $this->language_id;
		$language = $this->languages[$language_id];
		if (!isset($language[$key])) {
			$this->logger->info($this->getMessage("language.key-not-found",
				[
					"{MESSAGE_KEY}" => $key,
					"{LANG_NAME}" => $this->getLanguageName($language_id),
					"{LANG_ID}" => $language_id
				]
			));
			$language = $this->raw_language;
			if (!isset($language[$key])) {
				return null;
			}
		}
		return empty($replacements) ? $language[$key] : strtr($language[$key], $replacements);
	}

	public function getLanguageName(string $language_id = self::DEFAULT_LANGUAGE) : string{
		return $this->language_names[$language_id] ?? "unknown";
	}

	public function setLanguage(string $language_id = self::DEFAULT_LANGUAGE) : bool{
		if ($this->language_id === $language_id) {
			$this->logger->info($this->getMessage("language.already-set",
				[
					"{LANG_NAME}" => $this->getLanguageName($language_id),
					"{LANG_ID}" => $language_id,
					"{LANG_VER}" => $this->language_versions[$language_id],
				]
			));
			return false;
		}
		if (!array_key_exists($language_id, $this->language_names)) {
			$this->logger->info($this->getMessage("language.not-supported",
				[
					"{LANG_NAME}" => $this->getLanguageName($language_id),
				]
			));
			return false;
		}
		$this->language_id = $language_id;
		$this->raw_language = $this->getRawLanguageData();
		return true;
	}

	/**
	 * @param string $language_id
	 * @return array|null
	 */
	public function getRawLanguageData(string $language_id = self::DEFAULT_LANGUAGE) : ?array{
		if (!in_array($language_id, self::SUPPORTED_LANGUAGES)) {
			$language_id = self::DEFAULT_LANGUAGE;
		}
		$resource = $this->plugin->getResource("langs/$language_id.yml");
		if ($resource === null) {
			return null;
		}
		$data = yaml_parse(stream_get_contents($resource));
		fclose($resource);
		return $data;
	}

	/**
	 * @return Skywar
	 */
	public function getPlugin() : Skywar{
		return $this->plugin;
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
		return $this->language_versions[$language_id] ?? -1.0;
	}
}
