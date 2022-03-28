<?php
/**
 * Обработка POST-запросов при работе с меню в административной части
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

/**
 * Menu_admin_action
 */
class Menu_admin_action extends Action_admin
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
				case 'list_site_id':
					$this->list_site_id();
					break;

				case 'list_module':
					$this->list_module();
					break;

				case 'group_menu':
					$this->group_menu();
					break;
			}
		}
	}

	/**
	 * Подгружает список модулей
	 * 
	 * @return void
	 */
	private function list_site_id()
	{
		if (! $_POST["parent_id"])
		{
			$list = '<div class="fa fa-close ipopup__close"></div><div class="menu_list menu_list_first"><h2>'.$this->diafan->_('Страницы сайта').'</h2>';
		}
		else
		{
			$list = '<div class="menu_list">';
		}
		
		$rows = DB::query_fetch_all("SELECT id, [name], module_name, count_children FROM {site} WHERE [act]='1' AND trash='0' AND parent_id='%d' ORDER BY sort ASC", $_POST["parent_id"]);
		foreach ($rows as $row)
		{
			$list .= '<p site_id="'.$row["id"].'" module_name="site">';
			if ($row["count_children"])
			{
				$list .= '<a href="javascript:void(0)" class="plus menu_plus">+</a>';
			}
			else
			{
				$list .= '&nbsp;&nbsp;';
			}
			$list .= '&nbsp;<a href="'.BASE_PATH.$this->diafan->_route->link($row["id"]).'" class="menu_select">'.$row["name"].'</a>';
			if ($row["module_name"] && Custom::exists('modules/'.$row["module_name"].'/admin/'.$row["module_name"].'.admin.menu.php'))
			{
				Custom::inc('modules/'.$row["module_name"].'/admin/'.$row["module_name"].'.admin.menu.php');
				
				$class_name  = ucfirst($row["module_name"]).'_admin_menu';
				$class = new $class_name($this->diafan);
				$count = $class->count($row["id"]);
				if ($count)
				{
					$list .= ' <a href="javascript:void(0)" class="menu_select_module plus" module_name="'.$row["module_name"].'"><i class="fa fa-puzzle-piece fa-service"></i></a>';
				}
			}
			$list .= '</p>';
		}
		$list .= '</div>';

		$this->result["data"] = $list;
	}

	/**
	 * Подгружает список ссылок для меню на элементы модуля
	 * 
	 * @return void
	 */
	private function list_module()
	{
		if (empty($_POST["module_name"]) || empty($_POST["site_id"]))
		{
			$this->result["error"] = 'ERROR';
			return;
		}
		$module_name = $this->diafan->filter($_POST, "string", "module_name");
		$parent_id   = $this->diafan->filter($_POST, "int", "parent_id");
		$site_id     = $this->diafan->filter($_POST, "int", "site_id");

		$list = '';
		if (! $parent_id)
		{
			$name = $this->diafan->_(! empty($this->diafan->title_modules[$module_name]) ? $this->diafan->title_modules[$module_name] : $module_name);
			$list .= '<h2>'.$name.'</h2>';
		}
		else
		{
			$list = '<div class="menu_list">';
		}

		if (Custom::exists('modules/'.$module_name.'/admin/'.$module_name.'.admin.menu.php'))
		{
			Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin.menu.php');
			$class_name  = ucfirst($module_name).'_admin_menu';
			$class = new $class_name($this->diafan);
			$rows = $class->list_($site_id, $parent_id);
			foreach ($rows as $row)
			{
				if (! empty($row["hr"]))
				{
					$list .= '<div class="hr"></div>';
					continue;
				}
				$list .= '<p module_name="'.$module_name.'" site_id="'.$site_id.'" cat_id="'.$row["element_id"].'">';
				if ($row["count"])
				{
					$list .= '<a href="javascript:void(0)" class="plus menu_plus">+</a>';
				}
				else
				{
					$list .= '&nbsp;&nbsp;';
				}
				$link = BASE_PATH.$this->diafan->_route->link($site_id, $row['element_id'], $module_name, $row["element_type"]);
				$list .= '&nbsp;<a href="'.$link.'" class="menu_select">'.($row["name"] ? $row["name"] : $row["element_id"]).'</a>';
				$list .= '</p>';
			}
		}
		if ($parent_id)
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;
	}

	/**
	 * Сохраняет ссылки на элементы в меню при групповом выделение в списке
	 * 
	 * @return void
	 */
	private function group_menu()
	{
		Custom::inc('modules/menu/admin/menu.admin.inc.php');

		$_POST['ids'] = array_reverse($_POST['ids']);
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;
			}
		}
		if(! empty($ids))
		{
			$rows = DB::query_fetch_all("SELECT * FROM {%h} WHERE id IN (%s)", $this->diafan->table, implode(",", $ids));
			foreach($rows as $row)
			{
				$save = array(
					"element_id" => $row["id"],
					"module_name" => $this->diafan->_admin->module, 
					"element_type" => $this->diafan->element_type(),
					"is_new" => false,
					"parent_id" => ! empty($row["parent_id"]) ? $row["parent_id"] : '',
					"cat_id" => ! empty($row["cat_id"]) ? $row["cat_id"] : 0,
					"site_id" => ! empty($row["site_id"]) ? $row["site_id"] : 0,
					"name" => htmlspecialchars_decode($row["name"._LANG]),
					"old_name" => $row["name"._LANG],
					"access" => $row["access"],
					"old_access" => $row["access"],
					"sort" => ! empty($row["sort"]) ? $row["sort"] : 0,
					"act" => $row["act"._LANG],
					"date_start" => ! empty($row["date_start"]) ? $row["date_start"] : 0,
					"old_date_start" => ! empty($row["date_start"]) ? $row["date_start"] : 0,
					"date_finish" => ! empty($row["date_finish"]) ? $row["date_finish"] : 0,
					"old_date_finish" => ! empty($row["date_finish"]) ? $row["date_finish"] : 0
				);
				$menu_cat_ids = ! empty($_POST["menu_cat_ids"]) ? $_POST["menu_cat_ids"] : array();
				$menu_cat_ids = array_unique($menu_cat_ids);
				$menu_inc = new Menu_admin_inc($this->diafan);
				$menu_inc->save_menu($save, $menu_cat_ids);
			}
		}
	}
}