<?php
/**
 * Обработка POST-запросов в административной части модуля
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
 * Addons_admin_action
 */
class Addons_admin_action extends Action_admin
{

	/**
	 * Вызывает обработку Ajax-запросов
	 * 
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'check_update':
					$this->check_update();
					break;

				case 'group_action':
				case 'group_no_action':
				case 'group_addon_update':
					$this->group_option();
					break;
			}
		}
	}

	/**
	 * Проверить обновления для дополнений
	 * 
	 * @return void
	 */
	private function check_update()
	{
		$this->diafan->_addons->update(true);
		$count = DB::query_result("SELECT COUNT(*) FROM {%s} WHERE custom_timeedit>0 AND timeedit<>custom_timeedit", $this->diafan->table);
		$message = '';
		if($count)
		{
			$message = $this->diafan->_('Доступно обновление для дополнений: %d.');
		}
		else
		{
			$message = $this->diafan->_('Доступных обновлений для дополнений пока нет. Попробуйте проверить чуть позже.');
		}
		$this->result["errors"]["message"] = $message;
		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Групповая операция "Установка дополнения", "Отключение дополнения" и др.
	 * 
	 * @return void
	 */
	private function group_option()
	{
		if(! empty($_POST["ids"]))
		{
			$ids = array();
			foreach ($_POST["ids"] as $id)
			{
				$id = intval($id);
				if($id)
				{
					$ids[] = $id;
				}
			}
		}
		elseif(! empty($_POST["id"]))
		{
			$ids = array(intval($_POST["id"]));
		}
		if(! empty($ids))
		{
			switch ($_POST["action"])
			{
				case 'group_action':
					$this->group_action($ids);
					break;

				case 'group_no_action':
					$this->group_no_action($ids);
					break;

				case 'group_addon_update':
					$this->group_addon_update($ids);
					break;
			}
		}
	}

	/**
	 * Активация элемента
	 *
	 * @param array $ids идентификаторы дополнений
	 * @return void
	 */
	public function group_action($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		$question = ! empty($_POST["question"]) ? true : false;
		$this->diafan->_addons->install($ids, $question);

		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Блокировка элемента
	 *
	 * @param array $ids идентификаторы дополнений
	 * @return void
	 */
	public function group_no_action($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		$question = ! empty($_POST["question"]) ? true : false;
		$this->diafan->_addons->uninstall($ids, $question);

		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Обновляет элемент
	 *
	 * @param array $ids идентификаторы дополнений
	 * @return void
	 */
	public function group_addon_update($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		$this->diafan->_addons->reload($ids);

		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}
}
