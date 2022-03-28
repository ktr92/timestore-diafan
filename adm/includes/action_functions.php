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
 * Action_functions_admin
 *
 * Обработка POST-запросов
 */
class Action_functions_admin extends Action_admin
{
	/**
	 * Обработчик функции быстрого сохранения полей
	 *
	 * @return void
	 */
	public function fast_save()
	{
		$this->result["res"] = false;
		if(empty($_POST['name']) || empty($this->diafan->variables_list[$_POST['name']]) || empty($this->diafan->variables_list[$_POST['name']]["fast_edit"]))
			return;

		$func = 'fast_save_'.preg_replace('/[^a-z_]+/', '', $_POST['name']);
		$this->result["res"] = call_user_func_array (array(&$this->diafan, $func), array());
		if ($this->result["res"] === 'fail_function')
		{
			if(! empty($this->diafan->variables_list[$_POST['name']]["type"]) && $this->diafan->variables_list[$_POST['name']]["type"] == 'numtext')
			{
				$_POST['value'] = str_replace(' ', '', $_POST['value']);
			}
			$this->result["res"] = (bool)DB::query('UPDATE {'.$this->diafan->table.'} SET `%h`="'.((bool)$_POST['type'] ? '%h' : '%d' ).'" WHERE id=%d LIMIT 1', $_POST['name'], $_POST['value'], $_POST['id']);
		}
		// Удаляет кэш модуля
		$this->diafan->_cache->delete("", $this->diafan->_admin->module);
	}

	/**
	 * Изменяет количество элементов на странице
	 *
	 * @return void
	 */
	public function change_nastr()
	{
		$nastr = $this->diafan->filter($_POST, 'int', 'nastr');
		if($this->diafan->_users->admin_nastr != $nastr)
		{
			if($nastr > 500)
			{
				$nastr = 500;
			}
			DB::query("UPDATE {users} SET admin_nastr=%d WHERE id=%d", $nastr, $this->diafan->_users->id);
		}
		$this->result["redirect"] = $this->diafan->get_admin_url('page');
	}

	/**
	 * Сохраняет настройки интерфейса администратора
	 *
	 * @return void
	 */
	public function settings()
	{
		if(! in_array($_POST["name"], array('nav_box_compress', 'useradmin_is_toggle')))
		{
			return;
		}
		$cfg = unserialize($this->diafan->_users->config);
		if(empty($_POST["value"]))
		{
			if(isset($cfg[$_POST["name"]]))
			{
				unset($cfg[$_POST["name"]]);
			}
		}
		else
		{
			$cfg[$_POST["name"]] = 1;
		}
		if($cfg)
		{
			$cfg = serialize($cfg);
		}
		else
		{
			$cfg = '';
		}
		DB::query("UPDATE {users} SET config='%s' WHERE id=%d", $cfg, $this->diafan->_users->id);
	}

	/**
	 * Подгружает список для сортировки элементов
	 *
	 * @return void
	 */
	public function sort()
	{
		if($this->diafan->variable_list("name", "variable"))
		{
			$list_name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$list_name = 'name';
		}
		$list_name = ($this->diafan->variable_multilang($list_name) ? $list_name._LANG : $list_name);

		$lang_act = ($this->diafan->variable_multilang("act") ? _LANG : '');

		$text = '<select name="sort">';

		$parent_id = $this->diafan->filter($_POST, 'int', "parent_id");
		$cat_id = $this->diafan->filter($_POST, 'int', "cat_id");
		$site_id = $this->diafan->filter($_POST, 'int', "site_id");
		$sort = $this->diafan->filter($_POST, 'int', "sort");
		$id = $this->diafan->filter($_POST, 'int', "id");

		//список элементов, которые при сортировке стоят перед редактируемым элементом
		$rows = $this->diafan->get_select_from_db(array(
			"table" => $this->diafan->table,
			"name" => $list_name,
			"where" => (isset($_POST["parent_id"]) ? "parent_id=".$parent_id." AND " : '')
				.(isset($_POST["cat_id"]) ? "cat_id=".$cat_id." AND " : '')
				.(isset($_POST["site_id"]) ? "site_id=".$site_id." AND " : '')
				."sort".($this->diafan->variable_list('sort', 'desc') ? '>' : '<=').$sort
				.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : '')
				." AND id<>'".$id."'"
				.$this->diafan->where
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : ''),
			"order" => ($this->diafan->is_variable("act") ? "act".$lang_act." DESC," : '')
				.($this->diafan->variable_list('sort', 'desc') ? 'sort DESC, id DESC' : 'sort ASC, id ASC')
			));
		foreach($rows as $k => $v)
		{
			$text .= '<option value="'.$k.'">'.$v.'</option>';
		}

		$text .= '<option value="'.$id.'" selected>----'.( $_POST["name"] ? $_POST["name"] : $id ).'---</option>';

		//список элементов, которые при сортировке стоят после редактируемого элемента
		$rows = $this->diafan->get_select_from_db(array(
			"table" => $this->diafan->table,
			"name" => $list_name,
			"where" => (isset($_POST["parent_id"]) ? "parent_id=".$parent_id." AND " : '')
				.(isset($_POST["cat_id"]) ? "cat_id=".$cat_id." AND " : '')
				.(isset($_POST["site_id"]) ? "site_id=".$site_id." AND " : '')
				."sort".($this->diafan->variable_list('sort', 'desc') ? '<=' : ">").$sort
				.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : '')
				." AND id<>'".$id."'"
				.$this->diafan->where
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : ''),
			"order" => ($this->diafan->is_variable("act") ? "act".$lang_act." DESC," : '')
				.($this->diafan->variable_list('sort', 'desc') ? 'sort DESC, id DESC' : 'sort ASC, id ASC')
			));
		foreach($rows as $k => $v)
		{
			$text .= '<option value="'.$k.'">'.$v.'</option>';
		}
		$text .= '<option value="down">----'.$this->diafan->_('Вниз').'---</option></select>';

		$this->result["data"] = $text;
	}

	/**
	 * Подгружает список родителей
	 *
	 * @return void
	 */
	public function parent_id()
	{
		$_POST['parent_id'] = $this->diafan->filter($_POST, 'int', 'parent_id');

		if($this->diafan->variable_list("name", "variable"))
		{
			$list_name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$list_name = 'name';
		}

		$rows = DB::query_fetch_all("SELECT id, ".($this->diafan->variable_multilang($list_name) ? '['.$list_name.']' : $list_name).", parent_id FROM {".$this->diafan->table."} WHERE id<>%d"
		.($this->diafan->_admin->module == 'site' ? " AND id<>1" : '')
		.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
		." ORDER BY id DESC", $_POST['id']);

		foreach ($rows as $row)
		{
			$row["name"] = $row[$list_name];
			$cats[$row["parent_id"]][] = $row;
		}
		$this->result["data"] = '<select name="parent_id" upload="1">
			<option value="">'.( $this->diafan->_admin->module == 'site' ? $this->diafan->_('Главная') : '' ).'</option>'
			.$this->diafan->get_options($cats, $cats[0], array ($_POST["parent_id"])).'</select>';
	}

	/**
	 * Перемещает элементы в раздел
	 *
	 * @return void
	 */
	public function group_site_id()
	{
		//проверка прав
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			return;
		}

		if (empty( $_POST['ids'] ) || empty($_POST["site_id"]))
		{
			return;
		}

		$site_id = $this->diafan->filter($_POST, 'int', 'site_id');
		if(! $site_id || ! $this->diafan->config("element_site"))
		{
			return;
		}
		$ids = array();
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;

				if($this->diafan->variable_list('plus'))
				{
					$children = $this->diafan->get_children($id, $this->diafan->table);
					$ids = array_merge($ids, $children);
				}
			}
		}
		if($ids)
		{
			$news_site = array();
			$rows = DB::query_fetch_all("SELECT id, site_id FROM {%s} WHERE id IN (%s)", $this->diafan->table, implode(",", $ids));
			foreach($rows as $row)
			{
				if($row["site_id"] != $site)
				{
					$news_site[] = $row["id"];
				}
			}
			if($news_site)
			{
				DB::query("UPDATE {%s} SET site_id=%d".($this->diafan->config('element') ? ', cat_id=0' : '')." WHERE id IN (%s)", $this->diafan->table, $site_id, implode(",", $ids));
			}
			if($this->diafan->config('category'))
			{
				DB::query("UPDATE {%h} SET site_id=%d WHERE cat_id IN (%h)", str_replace('_category', '', $this->diafan->table), $site_id, implode(",", $ids));
			}
		}
		$this->result["status"] = true;
	}

	/**
	 * Перемещает элементы в категорию
	 *
	 * @return void
	 */
	public function group_cat_id()
	{
		//проверка прав
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			return;
		}

		if (empty( $_POST['ids'] ) || empty($_POST['cat_id']))
		{
			return;
		}

		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if ($this->diafan->config("element_multiple"))
			{
				DB::query("DELETE FROM {%s_category_rel} WHERE element_id=%d", $this->diafan->_admin->module, $id);
				DB::query("INSERT INTO {%s_category_rel} (element_id, cat_id) VALUES('%d', '%d')", $this->diafan->_admin->module, $id, $cat_id);

				if($this->diafan->config("element_site"))
				{
					$site_id = DB::query_result("SELECT site_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $cat_id);
				}
				else
				{
					$site_id = 0;
				}
				DB::query("UPDATE {%h} SET cat_id=%d".($site_id ? ", site_id=".$site_id : "")." WHERE id IN (%h)", $this->diafan->table, $cat_id, $id);

			}
			elseif ($cat_id && DB::query_result("SELECT cat_id FROM {%h} WHERE id=%d LIMIT 1", $this->diafan->table, $id) != $cat_id)
			{
				$children = array($id);
				if($this->diafan->variable_list('plus'))
				{
					$children = $this->diafan->get_children($id, $this->diafan->table);
					$children[] = $id;
				}

				if($this->diafan->config("element_site"))
				{
					$site_id = DB::query_result("SELECT site_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $cat_id);
				}
				else
				{
					$site_id = 0;
				}
				DB::query("UPDATE {%h} SET cat_id=%d".($site_id ? ", site_id=".$site_id : "")." WHERE id IN (%h)", $this->diafan->table, $cat_id, implode(",", $children));
			}
		}
		$this->result["status"] = true;
	}

	/**
	 * Добавить элементы в дополнительную категорию
	 *
	 * @return void
	 */
	public function group_cat_id_multi()
	{
		//проверка прав
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			return;
		}

		if (empty( $_POST['ids'] ) || empty($_POST['cat_id']))
		{
			return;
		}
		if (! $this->diafan->config("element_multiple"))
		{
			return;
		}

		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');
		$site_id = $this->diafan->filter($_POST, 'int', 'site_id');
		$ids = array();
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;
			}
		}

		if($this->diafan->config("element_site"))
		{
			$site_id = DB::query_result("SELECT site_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $cat_id);
		}
		else
		{
			$site_id = 0;
		}

		if($ids)
		{
			DB::query("DELETE FROM {%s_category_rel} WHERE element_id IN (%s) AND cat_id=%d", $this->diafan->_admin->module, implode(",", $ids), $cat_id);
			foreach($ids as $id)
			{
				DB::query("INSERT INTO {%s_category_rel} (element_id, cat_id) VALUES ('%d', '%d')", $this->diafan->_admin->module, $id, $cat_id);
			}
			// если категория не была задана, то назначаем дополнительную категорию главной
			DB::query("UPDATE {%h} SET cat_id=%d".($site_id ? ", site_id=".$site_id : "")." WHERE id IN (%h) AND cat_id=0", $this->diafan->table, $cat_id, implode(",", $ids));
		}
		$this->result["status"] = true;
	}

	/**
	 * Удаляет элементы из категории
	 *
	 * @return void
	 */
	public function group_cat_id_del()
	{
		//проверка прав
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			return;
		}

		if (empty( $_POST['ids'] ) || empty($_POST['cat_id']))
		{
			return;
		}

		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id');
		$ids = array();
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;
			}
		}

		if($ids)
		{
			DB::query("UPDATE {%h} SET cat_id=0 WHERE id IN (%s) AND cat_id=%d", $this->diafan->table, implode(",", $ids), $cat_id);
			if ($this->diafan->config("element_multiple"))
			{
				DB::query("DELETE FROM {%s_category_rel} WHERE element_id IN (%s) AND cat_id=%d", $this->diafan->_admin->module, implode(",", $ids), $cat_id);
				// если удалили главную категорию, то одну из дополнительных делаем главной
				$empty_ids = DB::query_fetch_value("SELECT id FROM {%h} WHERE id IN (%s) AND cat_id=0", $this->diafan->table, implode(",", $ids), "id");
				if($empty_ids)
				{
					$cat_ids_new = DB::query_fetch_key_value("SELECT element_id, cat_id FROM {%s_category_rel} WHERE element_id IN (%s) AND trash='0'", $this->diafan->_admin->module, implode(",", $empty_ids), "element_id", "cat_id");
					if($cat_ids_new)
					{
						$site_ids_new = DB::query_fetch_key_value("SELECT site_id, id FROM {%s_category} WHERE id IN (%s)", $this->diafan->_admin->module, implode(",", $cat_ids_new), "id", "site_id");
						foreach($cat_ids_new as $id => $cat_id)
						{
							DB::query("UPDATE {%h} SET cat_id=%d".(! empty($site_ids_new[$cat_id]) ? ", site_id=".$site_ids_new[$cat_id] : "")." WHERE id=%d", $this->diafan->table, $cat_id, $id);
						}
					}
				}
			}
		}
		$this->result["status"] = true;
	}

	/**
	 * Подгружает список пользователей
	 *
	 * @return void
	 */
	public function user_list()
	{
		$this->result["data"] = '<ul class="user_search_select">';
		$rows = DB::query_fetch_all("SELECT id, name, fio FROM {users} WHERE name LIKE '%s%%' OR fio LIKE '%s%%'", $_POST["search"], $_POST["search"]);
		foreach ($rows as $row)
		{
			$this->result["data"] .= '<li user_id="'.$row["id"].'"><span>'.$row["fio"].' ('.$row["name"].')</span></li>';
			$find = true;
		}
		if(empty($find))
		{
			$this->result["data"] .= $this->diafan->_('Ничего не найдено.');
		}
		$this->result["data"] .= '</ul>';
	}

	/**
	 * Подгружает список категорий
	 *
	 * @return void
	 */
	public function cat_list()
	{
		$this->result["data"] = '<ul class="cat_search_select">';
		$rows = DB::query_fetch_all("SELECT id, [name] FROM {%s_category} WHERE [name] LIKE '%s%%' AND trash='0'", $this->diafan->_admin->module, $_POST["search"]);
		foreach ($rows as $row)
		{
			$this->result["data"] .= '<li cat_id="'.$row["id"].'">'.$row["name"].'</li>';
			$find = true;
		}
		if(empty($find))
		{
			$this->result["data"] .= $this->diafan->_('Ничего не найдено.');
		}
		$this->result["data"] .= '</ul>';
	}
}