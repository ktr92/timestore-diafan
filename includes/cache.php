<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
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
 * Cache
 * 
 * Кэширование
 */
class Cache
{
	/**
	 * @var object бэкэнд
	 */
	private $backend;

	/**
	 * @var string метка кэша
	 */
	private $name;

	/**
	 * @var string название модуля
	 */
	private $module;

	/**
	 * Конструктор класса
	 *
	 * @return \Cache
	 */
	public function __construct()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return;
		}
		if(defined('CACHE_MEMCACHED') && CACHE_MEMCACHED)
		{
			$backend = 'memcached';
		}
		else
		{
			$backend = 'files';
		}
		switch($backend)
		{
			case 'files':
				Custom::inc('includes/cache/cache.files.php');
				$this->backend = new Cache_files();
				break;
			
			case 'memcached':
				Custom::inc('includes/cache/cache.memcached.php');
				$this->backend = new Cache_memcached();
				break;
		}
	}

	/**
	 * Закрывает ранее открытое соединение
	 * 
	 * @return void
	 */
	public function close()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return;
		}
		if($this->backend)
		{
			$this->backend->close();
		}
	}

	/**
	 * Читает кэш модуля $module с меткой $name
	 *
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return mixed
	 */
	public function get($name, $module)
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return false;
		}
		if (MOD_DEVELOPER_CACHE)
			return false;

		$this->transform_param($name, $module);

		return $this->backend->get($this->name, $this->module);
	}

	/**
	 * Сохраняет данные $data для модуля $module с меткой $name
	 *
	 * @param mixed $data данные, сохраняемые в кэше
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return boolean
	 */
	public function save($data, $name, $module)
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return false;
		}
		if (MOD_DEVELOPER_CACHE)
			return false;

		$this->transform_param($name, $module);

		return $this->backend->save($data, $this->name, $this->module);
	}

	/**
	 * Удаляет кэш для модуля $module с меткой $name. Если функция вызвана с пустой меткой, то удаляется весь кэш для модуля $module
	 *
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return boolean
	 */
	public function delete($name, $module = '')
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return false;
		}

		if($module == 'cache_extreme' && (! defined('CACHE_EXTREME') || ! CACHE_EXTREME))
		{
			return false;
		}
		$this->transform_param($name, $module);
		$result = $this->backend->delete($this->name, $this->module);

		// удаляет экстремальный кэш
		if(defined('CACHE_EXTREME') && CACHE_EXTREME && $module != 'cache_extreme')
		{
			$this->transform_param('', 'cache_extreme');
			$this->backend->delete($this->name, $this->module);
		}
		return $result;
	}

	/**
	 * Преобразует метку и название модуля для работы с кэшем
	 *
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return boolean true
	 */
	private function transform_param($name, $module)
	{
		if($name)
		{
			if (! is_array($name))
			{
				$this->name = md5($name);
			}
			else
			{
				$this->name = md5(serialize($name));
			}
		}
		else
		{
			$this->name = '';
		}
		if($module)
		{
			$this->module = md5($module);
		}
		else
		{
			$this->module = '';
		}
		return true;
	}
}

/**
 * Cache_exception
 * 
 * Исключение для кэширования
 */
class Cache_exception extends Exception{}

/**
 * Cache_interface
 * 
 * Интерфейс бэкенда для работы с кэшем
 */
interface Cache_interface
{
	/**
	 * Закрывает ранее открытое соединение
	 * 
	 * @return void
	 */
	public function close();

	/*
	 * Читает кэш модуля $module с меткой $name.
	 *
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return mixed
	 */
	public function get($name, $module);

	/*
	 * Сохраняет данные $data для модуля $module с меткой $name
	 *
	 * @param mixed $data данные, сохраняемые в кэше
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	public function save($data, $name, $module);

	/*
	 * Удаляет кэш для модуля $module с меткой $name. Если функция вызвана с пустой меткой, то удаляется весь кэш для модуля $module
	 *
	 * @param string $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	public function delete($name, $module);
}