<?php
/**
 * On-line консультант, система JivoSite
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

class Consultant_admin extends Frame_admin
{
	/**
	 * Основная страница модуля
	 * @return void
	 */
	public function show()
	{
		if(! empty($_POST["consultant_action"]))
		{
			switch($_POST["consultant_action"])
			{
				case "consultant_register":
					$this->consultant_register();
					break;

				case "consultant_config":
					$this->consultant_config();
					break;

				default:
					Custom::inc('includes/404.php');
			}
		}
		if(! empty($_GET["consultant_action"]))
		{
			switch($_GET["consultant_action"])
			{
				case "consultant_config_form":
					return $this->consultant_config_form();
				default:
					Custom::inc('includes/404.php');
			}
		}
		if(! $this->diafan->configmodules("jivosite_id", "consultant"))
		{
			echo '<p>Для подключения онлайн-консультанта <a href="https://www.jivosite.ru?partner_id=1936&lang=ru&pricelist_id=5" target="_blank">JivoSite</a> нужно дополнительно зарегистрироваться на их сайте.<br> Это можно сделать по <a href="https://www.jivosite.ru?partner_id=1936&lang=ru&pricelist_id=5" target="_blank">ссылке</a>, либо заполнив поля ниже. Укажите Ваш e-mail, на который Вам придет письмо-памятка, а также придумайте любой логин и пароль, который будет использоваться консультантами сайта.</p>';
			$this->consultant_register_form();
			echo '<p>Если Вы уже регистрировались и у Вас есть идентификатор виджета, внесите их в <a href="?consultant_action=consultant_config_form">настройки модуля</a>.</p>';
		}
		else
		{
			$password = $this->diafan->configmodules("password", "consultant");
			echo '<p>Вы зарегистрированы в системе JivoSite.</p>';
			if($this->diafan->configmodules("jivosite_password", "consultant"))
			{
				echo '<p>Ваш e-mail и пароль: <b>'
				.$this->diafan->configmodules("jivosite_email", "consultant").'</b> и <b>'
				.$this->diafan->configmodules("jivosite_password", "consultant").'</b>.</p>
				<p><a href="https://admin.jivosite.com/?lang=ru" target="_blank">Личный кабинет на сайте jivosite.ru</a></p>';
			}
			
			echo '<p>Для начала работы с консультантом нужно проделать два шага:</p>';
			
			echo '<p>1. Добавьте в шаблон DIAFAN.CMS (<i>/themes/site.php</i>) шаблонный тег<br> <code><span style="color: #000000"><span style="color: #0000BB">&lt;insert</span> <span style="color: #007700">name=</span><span style="color: #DD0000">&quot;show_block&quot;</span> <span style="color: #007700">module=</span><span style="color: #DD0000">&quot;consultant&quot</span> <span style="color: #007700">system=</span><span style="color: #DD0000">&quot;jivosite&quot</span><span style="color: #0000BB">&gt;</span></span></code>.</p>';
			
			echo '<p>2. Настройте внешний вид консультанта на сайте <a href="https://www.jivosite.ru?partner_id=1936&lang=ru&pricelist_id=5" target="_blank">ссылке</a>, используя свой логин и пароль.</p>';
			
			echo '<p>Изменить идентификатор виджета можно в <a href="?consultant_action=consultant_config_form">настройках модуля</a>.</p>';
		}
	}
	
	/**
	 * Форма регистрации в системе JivoSite
	 *
	 * @return void
	 */
	private function consultant_register_form()
	{
		echo '<form method="POST" action="">
		<input type="hidden" name="consultant_action" value="consultant_register">
		<p>E-mail: <input type="text" name="consultant_email" value="'.(!empty($_POST["consultant_email"]) ? str_replace('"', '&quot;', $_POST["consultant_email"]) : '').'"></p>
		<p>Адрес сайта: <input type="text" name="consultant_url" value="'.(!empty($_POST["consultant_url"]) ? str_replace('"', '&quot;', $_POST["consultant_url"]) : '').'"></p>
		<p>Пароль: <input type="password" name="consultant_password" value="'.(!empty($_POST["consultant_password"]) ? str_replace('"', '&quot;', $_POST["consultant_password"]) : '').'"></p>
		<p>Ваше имя: <input type="text" name="consultant_name" value="'.(!empty($_POST["consultant_name"]) ? str_replace('"', '&quot;', $_POST["consultant_name"]) : '').'"></p>
		<input type="submit" class="button" value="'.$this->diafan->_('Зарегистрироваться').'">
		</form>';
	}
	
	/**
	 * Регистрация в системе JivoSite
	 *
	 * @return void
	 */
	private function consultant_register()
	{
		if($this->diafan->configmodules("jivosite_id", "consultant"))
		{
			$this->diafan->redirect(BASE_PATH_HREF.'consultant/');
		}
		if(empty($_POST["consultant_name"]) || empty($_POST["consultant_email"]) || empty($_POST["consultant_password"]) || empty($_POST["consultant_url"]))
		{
			echo '<div class="error">'.$this->diafan->_('Заполните все поля.').'</div>';
			return;
		}
		$fp = fsockopen('user.diafan.ru', 80);
		if($fp)
		{;
			$data = http_build_query(array(
				"name" => $_POST["consultant_name"],
				"email" => $_POST["consultant_email"],
				"password" => $_POST["consultant_password"],
				"url" => $_POST["consultant_url"],
			));
			fputs($fp, "POST http://user.diafan.ru/service/jivosite.php HTTP/1.1\r\n");
			fputs($fp, "Host: user.diafan.ru\r\n");
			fputs($fp, "Content-Type: application/x-www-form-urlencoded;charset=UTF-8\r\n");
			fputs($fp, "Content-length: ".utf::strlen($data)."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $data);

			$result = '';
			while(!feof($fp))
			{
				$result = fgets($fp, 128);
				//echo $result;
			}
			fclose($fp);
			if(preg_match('/Error:(.*)/', $result, $m))
			{
				echo '<div class="error">'.$m[1].'</div>';
			}
			else
			{
				$fields = array('email', 'password');
				foreach ($fields as $field)
				{
					$this->diafan->configmodules('jivosite_'.$field, "consultant", 0, _LANG, (! empty($_POST["consultant_".$field]) ? $_POST["consultant_".$field] : ''));
				}
				$this->diafan->configmodules('jivosite_id', "consultant", 0, _LANG, preg_replace('/[^0-9a-zA-Z]+/', '', $result));
				$this->diafan->redirect(BASE_PATH_HREF.'consultant/success1/');
			}
		}
	}
	
	/**
	 * Настройки консультанта
	 *
	 * @return void
	 */
	private function consultant_config_form()
	{
		$fields = array('id');
		foreach ($fields as $field)
		{
			$$field = (!empty($_POST["consultant_".$field]) ? $_POST["consultant_".$field] : $this->diafan->configmodules('jivosite_'.$field, "consultant"));
		}

		echo '<form method="POST" action="">
		<input type="hidden" name="consultant_action" value="consultant_config">
		<p>Идентификатор виджета: <input type="text" name="consultant_id" style="width:100px;" value="'.str_replace('"', '&quot;', $id).'" maxlength="12"><br>
		'.$this->diafan->_('Строго 10 символов из переменной widget_id = "<b>XXXXXXXXXX</b>" кода, который Вы получили').'</p>
		<input type="submit" class="button" value="'.$this->diafan->_('Сохранить').'">
		</form>';
	}
	
	/**
	 * Сохранение настроек консультанта
	 *
	 * @return void
	 */
	private function consultant_config()
	{
		$fields = array('id');
		foreach ($fields as $field)
		{
			$this->diafan->configmodules('jivosite_'.$field, "consultant", 0, _LANG, (! empty($_POST["consultant_".$field]) ? $_POST["consultant_".$field] : ''));
		}
		$this->diafan->redirect(BASE_PATH_HREF.'consultant/success1/?consultant_action=consultant_config_form');
	}
}