<?php
/**
 * Набор функций для работы с файлами и папками
 * 
 * @package    DIAFAN.CMS
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

class File
{
	/**
	 * @var string ошибка операции
	 */
	private static $error;

	/**
	 * @var array внутренний кэш класса
	 */
	private static $cache;

	/**
	 * Проверяет существует ли файл
	 *
	 * @param string $file_path путь до файла относительно корня сайта
	 * @return void
	 */
	public static function check_file($file_path)
	{
		if(! file_exists(ABSOLUTE_PATH.$file_path))
		{
			throw new File_exception('Ошибочный путь.');
		}
	}

	/**
	 * Копирует файл
	 *
	 * @param string $source полный путь до исходного файла
	 * @param string $file_path путь до нового файла относительно корня сайта
	 * @return void
	 */
	public static function copy_file($source, $file_path)
	{
		$arr = explode('/', $file_path);
		$name = array_pop($arr);
		$path = implode('/', $arr);

		self::create_dir($path);

		if(! $source)
		{
			throw new File_exception('Пустая ссылка на исходный файл.');
		}
		if(! self::is_writable("tmp"))
		{
			throw new File_exception('Установите права на запись (777) для папки tmp.');
		}
		$tmp_path = 'tmp/'.mt_rand(0, 999999);
		if(preg_match('/^https?:\/\//', $source))
		{
			Custom::inc('plugins/httprequest/httprequest.php');
			$new_file = fopen(ABSOLUTE_PATH.$tmp_path, 'wb');
			if(! DHttpRequest::get($source)->receive($new_file)->ok())
			{
				throw new File_exception('Невозможно скопировать файл '.$source.'.');
			}
			fclose($new_file);
			if(! filesize(ABSOLUTE_PATH.$tmp_path))
			{
				unlink(ABSOLUTE_PATH.$tmp_path);
				return;
			}
		}
		else
		{
			if(! file_exists($source))
			{
				throw new File_exception('Файл '.$source.' не существует.');
			}
			copy($source, ABSOLUTE_PATH.$tmp_path);
		}
		self::upload_file(ABSOLUTE_PATH.$tmp_path, $path.'/'.$name);
	}

	/**
	 * Загружает файл и удаляет временный файл
	 *
	 * @param string $tmp_path полный путь, где храниться временный файл
	 * @param string $file_path путь до нового файла относительно корня сайта
	 * @return void
	 */
	public static function upload_file($tmp_path, $file_path)
	{
		$arr = explode('/', $file_path);
		$name = array_pop($arr);
		$path = implode('/', $arr);


		self::create_dir($path);

		$file_path = ($path ? $path.'/' : '').$name;
		if(! file_exists($tmp_path))
		{
			throw new File_exception('Файл '.$tmp_path.' не существует.');
		}
		if(self::is_writable($path) && copy($tmp_path, ABSOLUTE_PATH.$file_path))
		{
			chmod(ABSOLUTE_PATH.$file_path, 0755);	
		}
		else
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_put($conn_id, $file_path, $tmp_path, FTP_BINARY))
				{
					unlink($tmp_path);
					throw new File_exception('Не удалось сохранить файл. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
				}	
				ftp_close($conn_id);
			}
			else
			{
				unlink($tmp_path);
				throw new File_exception('Не удалось сохранить файл. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
			}
		}
		unlink($tmp_path);
	}

	/**
	 * Сохраняет файл
	 *
	 * @param string $content содержание файла
	 * @param string $file_path путь до нового файла относительно корня сайта
	 * @return void
	 */
	public static function save_file($content, $file_path)
	{
		$arr = explode("/", $file_path);
		$name = array_pop($arr);
		$path = implode("/", $arr);

		self::create_dir($path);

		if(! file_exists(ABSOLUTE_PATH.$file_path))
		{
			if(self::is_writable($path))
			{
				if($fp = fopen(ABSOLUTE_PATH.$file_path, "wb"))
				{
					fwrite($fp, $content);
					fclose($fp);
					return;
				}
			}
		}
		elseif(self::is_writable($file_path))
		{
			if($fp = fopen(ABSOLUTE_PATH.$file_path, "wb"))
			{
				fwrite($fp, $content);
				fclose($fp);
				return;
			}
		}
		$tmp_path = ABSOLUTE_PATH.'tmp/'.md5('files'.mt_rand(0, 99999999));
		if(! $fp = fopen($tmp_path, "wb"))
		{
			throw new File_exception('Установите права на запись (777) для папки tmp.');
		}
		fwrite($fp, $content);
		fclose($fp);

		$conn_id = self::connect_ftp();
		if($conn_id)
		{
			if (! ftp_put($conn_id, $file_path, $tmp_path, FTP_BINARY))
			{
				unlink($tmp_path);
				throw new File_exception('Не удалось сохранить файл. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
			}	
			ftp_close($conn_id);
		}
		else
		{
			unlink($tmp_path);
			throw new File_exception('Не удалось сохранить файл. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
		}
	}

	/**
	 * Переименовывает файл
	 *
	 * @param string $name новое имя
	 * @param string $old_name старое имя
	 * @param string $path путь до папки, в которой лежит файл, относительно корня сайта
	 * @return  void
	 */
	public static function rename_file($name, $old_name, $path)
	{
		if(! file_exists(ABSOLUTE_PATH.($path ? $path.'/' : '').$old_name))
		{
			throw new File_exception('Файл '.($path ? $path.'/' : '').$old_name.' не существует.');
		}
		if(! self::is_writable(($path ? $path.'/' : '').$old_name) || ! rename(ABSOLUTE_PATH.($path ? $path.'/' : '').$old_name, ABSOLUTE_PATH.($path ? $path.'/' : '').$name))
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_rename($conn_id, ($path ? $path.'/' : '').$old_name, ($path ? $path.'/' : '').$name))
				{
					throw new File_exception('Не удалось переименовать. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.($path ? $path.'/' : '').$old_name.'.');
				}	
				ftp_close($conn_id);
			}
			else
			{
				throw new File_exception('Не удалось переименовать. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.($path ? $path.'/' : '').$old_name.'.');
			}
		}
	}

	/**
	 * Удаляет файл
	 *
	 * @param string $file_path путь до файла относительно корня сайта
	 * @return  void
	 */
	public static function delete_file($file_path)
	{
		if(! file_exists(ABSOLUTE_PATH.$file_path))
		{
			return;
		}
		if(self::is_writable($file_path))
		{
			if(unlink(ABSOLUTE_PATH.$file_path))
			{
				return;
			}
		}
		$conn_id = self::connect_ftp();
		if($conn_id)
		{
			if (! ftp_delete($conn_id, $file_path))
			{
				throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
			}	
			ftp_close($conn_id);
		}
		else
		{
			throw new File_exception('Не удалось удалить. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
		}
	}

	/**
	 * Проверяет существует ли папка
	 *
	 * @param string $dir_path путь до папки относительно корня сайта
	 * @return  void
	 */
	public static function check_dir($dir_path)
	{
		if(! is_dir(ABSOLUTE_PATH.$dir_path))
		{
			throw new File_exception('Ошибочный путь.');
			return false;
		}
		return true;
	}

	/**
	 * Создает папку, если она не создана 
	 *
	 * @param string $path путь до папки-родителя относительно корня сайта
	 * @param boolean $access_close доступ к папке извне будет закрыт 
	 * @return  void
	 */
	public static function create_dir($path, $access_close = false)
	{
		if(is_dir(ABSOLUTE_PATH.($path ? $path.'/' : '')))
		{
			if($access_close && ! file_exists(ABSOLUTE_PATH.($path ? $path.'/' : '').'.htaccess'))
			{
				$text = 'Options -Indexes
				<files *>
				<IfModule mod_authz_core.c> 
				Require all denied 
				</IfModule> 
				<IfModule !mod_authz_core.c> 
				Order deny,allow 
				Deny from all 
				</IfModule> 
				</files>';
	
				self::save_file($text, ($path ? $path.'/' : '').'.htaccess');
			}
			return;
		}

		$arr = explode("/", $path);
		$name = array_pop($arr);
		$path = '';
		foreach($arr as $a)
		{
			$path .= ($path ? '/' : '').$a;
			self::create_dir($path);
		}
		if(self::is_writable($path) && mkdir(ABSOLUTE_PATH.($path ? $path.'/' : '').$name))
		{
			chmod(ABSOLUTE_PATH.($path ? $path.'/' : '').$name, 0777);
		}
		else
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_mkdir($conn_id, ($path ? $path.'/' : '').$name))
				{
					throw new File_exception('Не удалось создать папку '.$name.'. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
				}	
				ftp_close($conn_id);
			}
			else
			{
				throw new File_exception('Не удалось создать папку '.$name.'. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
			}
		}
		if($access_close)
		{
			$text = 'Options -Indexes
			<files *>
			<IfModule mod_authz_core.c> 
			Require all denied 
			</IfModule> 
			<IfModule !mod_authz_core.c> 
			Order deny,allow 
			Deny from all 
			</IfModule> 
			</files>';

			self::save_file($text, ($path ? $path.'/' : '').$name.'/.htaccess');
		}
	}

	/**
	 * Переименовывает папку
	 *
	 * @param string $name новое имя папки
	 * @param string $old_name старое имя папки
	 * @param string $path путь до папки-родителя относительно корня сайта
	 * @return  void
	 */
	public static function rename_dir($name, $old_name, $path)
	{
		if(! self::is_writable(($path ? $path.'/' : '').$old_name) || ! rename(ABSOLUTE_PATH.($path ? $path.'/' : '').$old_name, ($path ? $path.'/' : '').$name))
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_rename($conn_id, ($path ? $path.'/' : '').$old_name, ($path ? $path.'/' : '').$name))
				{
					throw new File_exception('Не удалось переименовать. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path.'/' : '').$old_name.'.');
				}	
				ftp_close($conn_id);
			}
			else
			{
				throw new File_exception('Не удалось переименовать. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path.'/' : '').$old_name.'.');
			}
		}
	}

	/**
	 * Копирует папку
	 *
	 * @param string $source полный путь до исходной папки
	 * @param string $path путь до папки-назначения относительно корня сайта
	 * @return  void
	 */
	public static function copy_dir($source, $path)
	{
		if(! is_dir($source))
		{
			return;
		}
		$dir = opendir($source);
		while (($file = readdir($dir)) !== false)
		{
			if($file == '.' || $file == '..')
				continue;

			if(is_dir($source.'/'.$file))
			{
				self::copy_dir($source.'/'.$file, $path.'/'.$file);
			}
			else
			{
				self::copy_file($source.'/'.$file, $path.'/'.$file);
			}
		}
	}

	/**
	 * Удаляет папку
	 *
	 * @param string $dir_path путь до папки относительно корня сайта
	 * @return  void
	 */
	public static function delete_dir($dir_path)
	{
		if(! $dir_path)
		{
			throw new File_exception('Нельзя удалить корневую директорию.');
		}
		if(! is_dir(ABSOLUTE_PATH.$dir_path))
		{
			return;
		}
		if(self::is_writable($dir_path))
		{
			$conn_id = false;
			self::delete_recursive($dir_path, $conn_id);
		}
		else
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				self::delete_recursive($dir_path, $conn_id);
			}
			else
			{
				throw new File_exception('Не удалось удалить. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.$dir_path.'.');
			}
		}
	}

	/**
	 * Определяет, доступны ли файл или папка для записи
	 *
	 * @param string $path путь до файла или папки относительно корня сайта
	 * @param boolean $ftp учитывать возможность редактирования по FTP
	 * @return boolean
	 */
	public static function is_writable($path, $ftp = false)
	{
		if($ftp && FTP_HOST && FTP_LOGIN && FTP_PASSWORD)
		{
			return true;
		}
		/*if(is_file(ABSOLUTE_PATH.$path))
		{
			if(is_writable(ABSOLUTE_PATH.$path))
			{
				$path_dir = preg_replace('/(\/)([^\/]+)$/', '', ABSOLUTE_PATH.$path);
				return is_writable($path_dir);
			}
			else
			{
				return false;
			}
		}
		else
		{*/
			return is_writable(ABSOLUTE_PATH.$path);
		//}
	}

	/**
	 * Сжимает JS и CSS файлы
	 * 
	 * @param string|array $path путь до файла относительно корня сайта
	 * @param string $type тип: css, js
	 * @return string
	 */
	public static function compress($path, $type)
	{
		return $path;
		static $clear;
		if(MOD_DEVELOPER || IS_DEMO)
		{
			return $path;
		}

		if(! in_array($type, array('js', 'css')))
			return $path;

		if(! is_array($path))
		{
			$path = array($path);
		}
		$name = '';
		foreach($path as $p)
		{
			if(! $clear)
			{
				$clear = true;
				clearstatcache();
			}
			$name .= $p.filemtime(ABSOLUTE_PATH.$p).' ';
		}

		$name = md5($name).'.'.$type;
		if(! file_exists(ABSOLUTE_PATH.'cache/'.$type.'/'.$name))
		{
			$code = '';
			switch($type)
			{
				case 'css':
					foreach($path as $p)
					{
						$c = file_get_contents(ABSOLUTE_PATH.$p);
						self::$cache["dir"] = str_replace(strrchr($p, '/'), '', $p);
						self::$cache["dir"] = preg_replace('/custom\/[^\/]+\//', '', self::$cache["dir"]);
						$c = preg_replace_callback('/url\((\"|\')*([^)]+?)(\"|\')*\)/', array('File', '_compress_css_url'), $c);
						$code .= $c;
					}
					Custom::inc('plugins/minify/minify.php');
					Custom::inc('plugins/minify/css.php');
					$minifier = new CSS_Minify($code);
					$code = $minifier->minify();
					break;

				case 'js':
					foreach($path as $p)
					{
						$code .= file_get_contents(ABSOLUTE_PATH.$p);
					}
					Custom::inc('plugins/minify/minify.php');
					Custom::inc('plugins/minify/js.php');
					$minifier = new JS_Minify($code);
					$code = $minifier->minify();
					break;
			}
			self::save_file(trim($code), 'cache/'.$type.'/'.$name);
		}
		return 'cache/'.$type.'/'.$name;
	}

	/**
	 * Удаляет папку рекурсивно
	 *
	 * @param string $dir_path путь до папки относительно корня сайта
	 * @return  void
	 */
	private static function delete_recursive($path, &$conn_id)
	{
		$dir = opendir(ABSOLUTE_PATH.$path);
		while (($file = readdir($dir)) !== false)
		{
			if($file == '.' || $file == '..')
				continue;

			if(is_dir(ABSOLUTE_PATH.$path.'/'.$file))
			{
				self::delete_recursive($path.'/'.$file, $conn_id);
			}
			else
			{
				if($conn_id)
				{
					if (! ftp_delete($conn_id, $path.'/'.$file))
					{
						ftp_close($conn_id);
						throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$path.'/'.$file.'.');
					}
				}
				else
				{
					if(! self::is_writable($path.'/'.$file) || ! unlink(ABSOLUTE_PATH.$path.'/'.$file))
					{
						throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$path.'/'.$file.'.');
					}
				}
			}
		}
		closedir($dir);
		if($conn_id)
		{
			if (! ftp_rmdir($conn_id, $path))
			{
				ftp_close($conn_id);
				throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.$path.'.');
			}
		}
		else
		{
			if(! self::is_writable($path) || ! rmdir(ABSOLUTE_PATH.$path))
			{
				throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.$path.'.');
			}
		}
	}

	/**
	 * Пробует установить FTP-соединение
	 *
	 * @return resource идентификатор соединения с FTP сервером
	 */
	private static function connect_ftp()
	{
		self::$error = '';
		if(! defined('FTP_HOST') || ! defined('FTP_LOGIN') || ! defined('FTP_PASSWORD') || ! FTP_HOST || ! FTP_LOGIN || ! FTP_PASSWORD)
		{
			return false;
		}
		$host = FTP_HOST;
		$port = null;
		if(strpos($host, ':') !== false)
		{
			list($host, $port) = explode(':', FTP_HOST, 2);
		}
		if(! $conn_id = ftp_connect($host, $port))
		{
			self::$error = 'Ошибка подключения по FTP. Хост не найден.';
			return false;
		}
		if(! ftp_login($conn_id, FTP_LOGIN, FTP_PASSWORD))
		{
			ftp_close($conn_id);
			self::$error = 'Ошибка подключения по FTP. Указаны неверные данные для подлкючения.';
			return  false;
		}
		ftp_pasv($conn_id, true);
		if (! ftp_chdir($conn_id, FTP_DIR))
		{
			ftp_close($conn_id);
			self::$error = 'Неправильно задан относительный путь.';
			return  false;
		}
		return $conn_id;
	}

	static private function _compress_css_url($res)
	{
		if (substr($res[2], 0, 4) == 'http')
		{
			if(strpos($res[2], BASE_PATH) === false)
			{
				return $res[0];
			}
			else
			{
				$res[2] = preg_replace('/^'.preg_quote(BASE_PATH, '/').'/', '', $res[2]);
				$dir = '';
			}
		}
		$query = '';
		if(strpos($res[2], '#') !== false)
		{
			list($res[2], $query) = explode('#', $res[2]);
			$query = '#'.$query;
		}
		if(strpos($res[2], '?') !== false)
		{
			list($res[2], $query) = explode('?', $res[2]);
			$query = '?'.$query;
		}
		if(! isset($dir))
		{
			$count = substr_count($res[2], '../');
			$res[2] = str_replace('../', '', $res[2]);
			$adir = explode('/', self::$cache["dir"]);
			for($i = 0; $i < $count; $i++)
			{
				array_pop($adir);
			}
			$dir = implode('/', $adir).($adir ? '/' : '');
		}
		return 'url("/'.(REVATIVE_PATH ? REVATIVE_PATH.'/' : '').Custom::path($dir.$res[2]).$query.'")';
	}
}

/**
 * File_exception
 * 
 * Исключение для работы с файлами
 */
class File_exception extends Exception{}