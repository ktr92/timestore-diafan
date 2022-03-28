<?php
/**
 * Подключение модуля
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Addons_inc
 */
class Addons_inc extends Diafan
{

	const API_VERSION = 1;
	const API_URI = "https://addons.diafan.ru/api.php";
	const ERROR_RESPONSE = 'error';
	const ERROR_CODE_RESPONSE = 'errno';
	const RESPONSE = 'response';
	const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';
	const MODULE_NAME = 'addons';
	
	const METHOD_GET = 'modules.get';
	const METHOD_DOWNLOAD = 'modules.download';
	const PREFIX = 'addon_';
	const COUNT = 20;
	
	/**
	 * @var integer метка времени
	 */
	static private $timemarker = 0;
	
	/**
	 * @var array массив объектов - установка модулей
	 */
	private $install = array();

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		Custom::inc('plugins/httprequest/httprequest.php');
		self::$timemarker = mktime(23, 59, 0, date("m"), date("d"), date("Y")); // кешируем на сутки
	}

	/**
	 * Проверка ответа на наличие ошибок
	 *
	 * @param array $response
	 * @return boolean
	 * @throws AtolonlineException
	 */
	private function error($response)
	{
		if(empty($response[self::ERROR_RESPONSE]))
		{
			return false;
		}
		throw new Addons_exception($response[self::ERROR_RESPONSE], $response[self::ERROR_CODE_RESPONSE]);
	}

	/**
	 * Создает GET запрос к API
	 *
	 * @param string $method
	 * @param array $params
	 * @return \DHttpRequest
	 */
	private function createRequest($method, $params)
	{
		if(!is_array($params))
		{
			$params = array();
		}
		$params['method'] = $method;
		$params['v'] = self::API_VERSION;

		$http = DHttpRequest::get(self::API_URI, $params)->userAgent(self::USER_AGENT)->acceptJson();
		return $http;
	}

	/**
	 * Создает запрос и получает ответ от API
	 *
	 * @param string $method
	 * @param array $params
	 * @return array|string
	 * @throws Addons_exception
	 */
	public function request($method, $params = null)
	{
		$result = array();

		$cache_meta = array(
			'params' => null !== $params ? json_encode($params) : '',
			'method' => $method,
			'v' => self::API_VERSION,
			'time' => self::$timemarker,
			'name' => __METHOD__
		);

		if(! $result = $this->diafan->_cache->get($cache_meta, self::MODULE_NAME))
		{
			try
			{
				$http = $this->createRequest($method, $params);
				$response = json_decode($http->body(), true);

				if (! $this->error($response))
				{
					$result = $response[self::RESPONSE];
					$this->diafan->_cache->save($result, $cache_meta, self::MODULE_NAME);
				}
			}
			catch(DHttpRequestException $ex)
			{
				throw new Addons_exception($ex->getMessage(), $ex->getCode());
			}
		}

		return $result;
	}
	
	/**
	 * Подготавливает полученный список дополнений
	 *
	 * @return void
	 */
	private function prepare($response)
	{
		if(empty($response) || empty($response['items']) || empty($response['count']))
		{
			return array(
				'items' => array(),
				'count' => 0,
			);
		}

		$items = array();
		foreach ($response['items'] as $row)
		{
			$author = array("link" => '', "name" => '');
			if(! empty($row["author"]))
			{
				$author = array(
					"link" => $this->diafan->filter($row["author"], 'string', 'link', ''),
					"name" => $this->diafan->filter($row["author"], 'string', 'name', ''),
				);
			}

			$value = array(
				"id" => $this->diafan->filter($row, 'int', 'id', 0),
				"name" => $this->diafan->filter($row, 'string', 'name', ''),
				"timeedit" => $this->diafan->filter($row, 'int', 'timeedit', 0),
				"anons" => $this->diafan->filter($row, 'string', 'anons', ''),
				"text" => $this->diafan->filter($row, 'string', 'text', ''),
				"downloaded" => $this->diafan->filter($row, 'int', 'downloaded', 0),
				"install" => $this->diafan->filter($row, 'string', 'install', ''),
				"link" => $this->diafan->filter($row, 'string', 'link', ''),
				"image" => $this->diafan->filter($row, 'string', 'img', ''),
				"author" => ! empty($author["name"]) ? $author["name"] : 'Diafan',
				"author_link" => ! empty($author["link"]) ? $author["link"] : 'https://www.diafan.ru/',
			);
			$value["name"] = htmlspecialchars_decode($value["name"]); //$value["name"] = html_entity_decode($value["name"]);
			$value["anons"] = htmlspecialchars_decode($value["anons"]); //$value["anons"] = html_entity_decode($value["anons"]);
			$value["text"] = htmlspecialchars_decode($value["text"]); //$value["text"] = html_entity_decode($value["text"]);
			$value["install"] = htmlspecialchars_decode($value["install"]); //$value["install"] = html_entity_decode($value["install"]);
			$value["author"] = htmlspecialchars_decode($value["author"]); //$value["author"] = html_entity_decode($value["author"]);
			unset($author);

			$items[$value["id"]] = $value;
		}

		$response['items'] = $items;
		$response['count'] = (int) $response['count'];

		return $response;
	}

	/**
	 * Обновляет список дополнений
	 *
	 * @param boolean $upgrade принудительное обновление
	 * @return void
	 */
	public function update($upgrade = false)
	{
		if($upgrade)
		{
			$this->diafan->_cache->delete("", self::MODULE_NAME);
		}
		
		$cache_meta = array(
			'time' => self::$timemarker,
			'name' => __METHOD__
		);
		
		if(! $result = $this->diafan->_cache->get($cache_meta, self::MODULE_NAME))
		{
			if(function_exists('set_time_limit'))
			{
				$disabled = explode(',', ini_get('disable_functions'));
				if(! in_array('set_time_limit', $disabled))
				{
					set_time_limit(0);
				}
			}
			
			$offset = 0;
			do
			{
				$response = $this->diafan->_addons->request(self::METHOD_GET, array(
						'count' => self::COUNT,
						'offset' => $offset,
					)
				);
				$response = $this->prepare($response);
				$offset += self::COUNT;
				if(empty($response['items']) || empty($response['count'])) break;

				// TO_DO: $addon_ids = array_column($response['items'], 'id');
				// PHP 5 >= 5.5.0, PHP 7
				$addon_ids = array();
				foreach($response['items'] as $item)
				{
					if(empty($item["id"])) continue;
					$addon_ids[] = $item["id"];
				}

				$rows = DB::query_fetch_key_value("SELECT id, addon_id FROM {".self::MODULE_NAME."} WHERE addon_id IN(%s)", implode(",", $addon_ids), "addon_id", "id");
				foreach($response['items'] as $value)
				{
					if(! empty($rows[$value["id"]]))
					{
						DB::query("UPDATE {".self::MODULE_NAME."} SET name='%s', timeedit=%d, anons='%s', text='%s', downloaded=%d, install='%s', link='%h', image='%h', author='%s', author_link='%h', import_update='%s' WHERE id=%d", $value["name"], $value["timeedit"], $value["anons"], $value["text"], $value["downloaded"], $value["install"], $value["link"], $value["image"], $value["author"], $value["author_link"], '1', $rows[$value["id"]]);
					}
					else
					{
						DB::query("INSERT INTO {".self::MODULE_NAME."} (addon_id, custom_id, name, anons, text, install, link, image, author, author_link, downloaded, timeedit, custom_timeedit, import_update) VALUES (%d, %d, '%s', '%s', '%s', '%s', '%h', '%h', '%s', '%h', %d, %d, %d, '%s')", $value["id"], 0, $value["name"], $value["anons"], $value["text"], $value["install"], $value["link"], $value["image"], $value["author"], $value["author_link"], $value["downloaded"], $value["timeedit"], 0, '1');
					}
				}
			}
			while($offset < (int) $response['count']);
			
			DB::query("DELETE FROM {".self::MODULE_NAME."} WHERE import_update<>'%s'", '1');
			DB::query("UPDATE {".self::MODULE_NAME."} SET import_update='%s' WHERE import_update<>'%s'", '0', '0');
			
			$this->diafan->_cache->save(true, $cache_meta, self::MODULE_NAME);
		}
	}

	/**
	 * Инсталлирует дополнения
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return boolean
	 */
	public function install($array, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		if(function_exists('set_time_limit'))
		{
			$disabled = explode(',', ini_get('disable_functions'));
			if(! in_array('set_time_limit', $disabled))
			{
				set_time_limit(0);
			}
		}

		$names = Custom::names();
		foreach($names as $key => $name) $names[$key] = "'".$name."'";
		$rows = DB::query_fetch_key("SELECT e.*, e.id as id, addon_id, IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`, IF (c.id > 0 AND c.name IN (".implode(", ", $names)."), '1', '0') AS act FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id");

		$names = array();
		foreach($rows as $row)
		{
			$name = $row["custom.name"];
			$id = $row["addon_id"];
			$dir_path = 'custom/' . $name;

			if(! empty($row["custom.id"]) && ! empty($name) && is_dir(ABSOLUTE_PATH.$dir_path))
			{
				if($row["act"])
				{
					continue;
				}
				else
				{
					$names[] = $name;
					continue;
				}
			}

			$response = $this->diafan->_addons->request(self::METHOD_DOWNLOAD, array('ids' => $id));
			if (! empty($response['items'][$id]['link']))
			{
				$item = $response['items'][$id];
				if($file_path = $this->download($item['link']))
				{
					$name = false;
					if(! empty($row["custom.name"]))
					{
						$name = $row["custom.name"];
					}
					else
					{
						$name = $this->generate_name($id);
					}

					if ($name && $this->diafan->_custom->import($file_path, $name))
					{
						if(empty($row["custom.id"]))
						{
							$row["anons"] = ! empty($row["anons"]) ? $row["anons"] : $row["text"];
							$row["custom.id"] = DB::query("INSERT INTO {custom} (name, created, text, current) VALUES ('%s', %d, '%s', '1')", $name, time(), $row["anons"]);
							$names[] = $name;
						}
						else
						{
							$names[] = $name;
						}
						DB::query("UPDATE {%s} SET custom_id=%d, custom_timeedit=%d WHERE id=%d", self::MODULE_NAME, $row["custom.id"], $row["timeedit"], $row["id"]);
					}
					unlink($file_path);
				}
			}
		}
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}
		if(! empty($names))
		{
			$this->diafan->_custom->set($names, true, $sql);
			if($sql)
			{
				$modules = $this->diafan->_custom->get_modules($names);
				$module_names = array();
				foreach($modules as $key => $module)
				{
					if(! empty($module["installed"])) continue;
					$module_names[] = $key;
				}
				$this->diafan->_custom->set_modules($module_names, true, $names);
			}
		}

		return true;
	}

	/**
	 * Деинсталлирует дополнения
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return boolean
	 */
	public function uninstall($array, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		$names = DB::query_fetch_key_value("SELECT c.id as id, IFNULL(c.name, '') as name FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE c.id IS NOT NULL AND e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id", "name");

		$result = false;
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}
		if(! empty($names))
		{
			if($sql)
			{
				$module_names = array();
				$modules = $this->diafan->_custom->get_modules($names);
				if(! empty($modules))
				{
					foreach($modules as $key => $module)
					{
						if(empty($module["installed"])) continue;
						$module_names[] = $key;
					}
				}
				$this->diafan->_custom->set_modules($module_names, false, $names);
			}
			$result = $this->diafan->_custom->set($names, false, $sql);
		}
		return $result;
	}

	/**
	 * Деинсталлирует и удаляет дополнения
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return boolean
	 */
	public function delete($array, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		$this->uninstall($ids, $sql);
		if($sql)
		{
			$names = DB::query_fetch_key_value("SELECT c.id as id, IFNULL(c.name, '') as name FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE c.id IS NOT NULL AND e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id", "name");
			if(! empty($names))
			{
				foreach($names as $id => $name)
				{
					if(empty($name)) continue;
					$dir_path = 'custom/' . $name;
					if(is_dir(ABSOLUTE_PATH.$dir_path))
					{
						DB::query("DELETE FROM {custom} WHERE id=%d LIMIT 1", $id);
						File::delete_dir($dir_path);
					}
				}
			}
			DB::query("UPDATE {%s} SET custom_id=%d, custom_timeedit=%d WHERE id IN (%s)", self::MODULE_NAME, 0, 0, implode(',', $ids));
		}
		
		return true;
	}

	/**
	 * Обновляет дополнения путем только замены файлов в прикреплнной к дополнению теме
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @return boolean
	 */
	public function reload($array)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		if(function_exists('set_time_limit'))
		{
			$disabled = explode(',', ini_get('disable_functions'));
			if(! in_array('set_time_limit', $disabled))
			{
				set_time_limit(0);
			}
		}

		$names = Custom::names();
		foreach($names as $key => $name) $names[$key] = "'".$name."'";
		$rows = DB::query_fetch_key("SELECT e.*, e.id as id, addon_id, IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`, IF (c.id > 0 AND c.name IN (".implode(", ", $names)."), '1', '0') AS act FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id");

		foreach($rows as $row)
		{
			$name = $row["custom.name"];
			$id = $row["addon_id"];
			$dir_path = 'custom/' . $name;

			if(empty($row["custom.id"]) || empty($name) && ! is_dir(ABSOLUTE_PATH.$dir_path))
			{
				continue;
			}

			$response = $this->diafan->_addons->request(self::METHOD_DOWNLOAD, array('ids' => $id));
			if (! empty($response['items'][$id]['link']))
			{
				$item = $response['items'][$id];
				if($file_path = $this->download($item['link']))
				{
					File::delete_dir($dir_path);
					if ($name && $this->diafan->_custom->import($file_path, $name))
					{
						DB::query("UPDATE {%s} SET custom_id=%d, custom_timeedit=%d WHERE id=%d", self::MODULE_NAME, $row["custom.id"], $row["timeedit"], $row["id"]);
					}
					unlink($file_path);
				}
			}
		}

		return true;
	}

	/**
	 * Загружает дополнения
	 *
	 * @param string $url ссылка архивного файла дополнения
	 * @return string
	 */
	private function download($url)
	{
		$file_path = 'tmp/' . md5('addon' . mt_rand(0, 9999));
		$url = $this->append($url, array('key' => mt_rand(0, 9999)));
		File::copy_file($url, $file_path);
		return (file_exists(ABSOLUTE_PATH.$file_path) ? $file_path : false);
	}

	/**
     * Appends the parameters of the object/array $params to the main URL
     *
     * @param string $url
     * @param array|object $params
     * @return string
     */
    private function append($url, $params)
    {
		return $url.(strpos($url, '?') === false ? '?' : '&').http_build_query($params);
    }

	/**
	 * Генерирует новое имя для темы сайта
	 *
	 * @param integer $id идентификатор дополнения
	 * @return string
	 */
	private function generate_name($id = false)
    {
		$i = 0;
		do
		{
			$theme = self::PREFIX.($id ? $id.'_' : '').strtolower($this->diafan->uid());
			$dir_path = 'custom/' . $theme;
			$result = is_dir(ABSOLUTE_PATH.$dir_path); $i++;
		}
		while($result && $i < 10);
		return $theme = ! $result ? $theme : $result;
    }
}

/**
 * Addons_exception
 * 
 * Исключение для дополнений
 */
class Addons_exception extends Exception{}