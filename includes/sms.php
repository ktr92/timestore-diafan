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
 * SMS
 * Набор функций для отправки SMS
 */
class SMS
{
	/**
	 * Отправляет SMS
	 * @param string $text текст SMS
	 * @param string $to номер получателя
	 * @return void
	 */
	public static function send($text, $to)
	{
		if(! SMS)
		{
			return;
		}
		$to = preg_replace('/[^0-9]+/', '', $to);
		Custom::inc('includes/validate.php');
		if($error = Validate::phone($to))
		{
			return $error;	
		}
		$text = urlencode(str_replace("\n", "%0D", substr($text, 0, 800)));
		$fp = fsockopen('bytehand.com', 3800);
		if($fp)
		{
			$result = file_get_contents("http://bytehand.com:3800/send?id=".urlencode(SMS_ID)."&key=".urlencode(SMS_KEY)."&to=".$to."&from=".urlencode(SMS_SIGNATURE)."&text=".$text);
		}
	}
}