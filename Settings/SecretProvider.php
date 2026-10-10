<?php

namespace Bxx\Settings;

class SecretProvider
{
	private static ?array $Cache = null;

	/**
	 * Возвращает секрет по имени секции и ключа из всех INI-файлов.
	 *
	 * @param string $Section Имя секции INI.
	 * @param string $Key Имя ключа в секции.
	 * @param mixed $Default Значение, возвращаемое при отсутствии секции или ключа.
	 * @return mixed Значение секрета или значение по умолчанию.
	 * @throws \RuntimeException Если INI-файл не удалось разобрать или ключ объявлен более одного раза.
	 */
	public static function get(string $Section, string $Key, mixed $Default = null): mixed
	{
		$Secrets = static::getAll();

		return isset($Secrets[$Section]) && array_key_exists($Key, $Secrets[$Section])
			? $Secrets[$Section][$Key]
			: $Default;
	}

	/**
	 * Возвращает секреты выбранной секции или все секции.
	 *
	 * @param string|null $Section Имя секции; null означает вернуть все секции.
	 * @return array Ассоциативный массив секретов.
	 * @throws \RuntimeException Если INI-файл не удалось разобрать или ключ объявлен более одного раза.
	 */
	public static function getAll(?string $Section = null): array
	{
		$Secrets = static::loadAll();

		return $Section === null ? $Secrets : ($Secrets[$Section] ?? []);
	}

	/**
	 * Загружает и кэширует секции из всех INI-файлов в local/secrets.
	 *
	 * @return array Секции с ключами и значениями.
	 * @throws \RuntimeException Если каталог не удалось прочитать, файл не удалось разобрать,
	 *     файл не содержит секций или ключ объявлен более одного раза.
	 */
	private static function loadAll(): array
	{
		if (self::$Cache !== null) {
			return self::$Cache;
		}

		$Directory = \Bitrix\Main\Application::getDocumentRoot().'/local/secrets';
		if (!is_dir($Directory)) {
			return self::$Cache = [];
		}

		$Files = glob($Directory.'/*.ini');
		if ($Files === false) {
			throw new \RuntimeException('Не удалось получить список файлов секретов');
		}
		sort($Files, SORT_STRING);

		$Secrets = [];
		foreach ($Files as $FilePath) {
			$FileSecrets = @parse_ini_file($FilePath, true, INI_SCANNER_RAW);
			if (!is_array($FileSecrets)) {
				throw new \RuntimeException('Не удалось разобрать INI-файл секретов: '.basename($FilePath));
			}

			foreach ($FileSecrets as $Section => $Values) {
				if (!is_array($Values)) {
					throw new \RuntimeException('Секреты должны находиться в секциях INI: '.basename($FilePath));
				}

				foreach ($Values as $Key => $Value) {
					if (isset($Secrets[$Section]) && array_key_exists($Key, $Secrets[$Section])) {
						throw new \RuntimeException(
							'Повтор секрета '.$Section.'.'.$Key.' в файле '.basename($FilePath)
						);
					}

					$Secrets[$Section][$Key] = $Value;
				}
			}
		}

		return self::$Cache = $Secrets;
	}
}