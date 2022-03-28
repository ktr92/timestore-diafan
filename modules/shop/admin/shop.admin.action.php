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
 * Shop_admin_action
 */
class Shop_admin_action extends Action_admin
{
	/**
	 * Вызывает обработку POST-запросов
	 * 
	 * @return void
	 */
	public function init()
	{
		if ( ! empty($_POST["action"]))
		{
			switch ($_POST["action"])
			{
				case 'show_discount_goods':
					$this->show_discount_goods();
					break;

				case 'discount_good':
					$this->discount_good();
					break;

				case 'delete_discount_good':
					$this->delete_discount_good();
					break;

				case 'show_order_goods':
					$this->show_order_goods();
					break;

				case 'add_order_good':
					$this->add_order_good();
					break;

				case 'new_order':
					$this->new_order();
					break;

				case 'list_site_id':
					$this->list_site_id();
					break;

				case 'param_category_rel':
				case 'param_category_unrel':
					$this->param_category();
					break;

				case 'brand_category_rel':
				case 'brand_category_unrel':
					$this->brand_category();
					break;

				case 'group_no_buy':
				case 'group_not_no_buy':
				case 'group_hit':
				case 'group_not_hit':
				case 'group_action':
				case 'group_not_action':
				case 'group_new':
				case 'group_not_new':
					$this->group_option();
					break;

				case 'group_clone':
					$this->group_clone();
					break;

				case 'optimize_price':
					$this->optimize_price();
					break;
				
				case 'group_abandonmented_cart_mail':
					$this->group_abandonmented_cart_mail();
					break;
			}
		}
	}

	/**
	 * Подгружает список товаров
	 * 
	 * @return void
	 */
	private function show_discount_goods()
	{
		if (empty($_POST["discount_id"]))
		{
			$_POST["discount_id"] = 0;
		}
		$nastr = 16;
		$list = '';
		if (empty($_POST["page"]))
		{
			$start = 0;
			$page = 1;
			if ( ! isset($_POST["search"]))
			{
				$list .= '<div class="fa fa-close ipopup__close"></div>
				<div class="ipopup__heading">'.$this->diafan->_('Товары').'</div>
				<div class="infofield">'.$this->diafan->_('Поиск').'</div> <input type="text" size="30" class="rel_module_search">
				<div class="rel_all_elements_container">';
			}
		}
		else
		{
			$page = intval($_POST["page"]);
			$start = ($page - 1) * $nastr;
		}
		$list .= '<div class="rel_all_elements">';
		$discount_goods = array();
		if ($_POST["discount_id"])
		{
			$discount_goods = DB::query_fetch_value("SELECT good_id FROM {shop_discount_object} WHERE discount_id=%d", $_POST["discount_id"], "good_id");
		}
		if (! empty($_POST["search"]))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))", $_POST["search"], $_POST["search"]);
			$rows = DB::query_range_fetch_all("SELECT id, [name] FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))", $_POST["search"], $_POST["search"], $start, $nastr);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
			$rows = DB::query_range_fetch_all("SELECT id, [name] FROM {shop} WHERE trash='0'", $start, $nastr);
		}
		foreach ($rows as $row)
		{
			$img = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE element_id=%d AND module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort ASC LIMIT 1", $row["id"]);
			$list .= '<div class="rel_module" element_id="'.$row["id"].'">
			<div'.(in_array($row["id"], $discount_goods) ? ' class="rel_module_selected"' : '').'>
			'.($img ? '<a href="javascript:void(0)"><img src="'.BASE_PATH.USERFILES.'/small/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'"></a><br>' : '').'
			<a href="javascript:void(0)">'.$row["name"].'</a>
			</div>
			</div>';
		}
		$list .= '</div><div class="rel_module_navig paginator">';
		for ($i = 1; $i <= ceil($count / $nastr); $i ++ )
		{
			if ($i != $page)
			{
				$list .= '<a href="javascript:void(0)" page="'.$i.'">'.$i.'</a> ';
			}
			else
			{
				$list .= '<span class="active">'.$i.'</span>';
			}
		}
		$list .= '</div>';
		if (empty($_POST["page"]) && ! isset($_POST["search"]))
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;
	}

	/**
	 * Прикрепляет скидку к товару
	 * 
	 * @return void
	 */
	private function discount_good()
	{
		if ( ! $_POST["discount_id"])
		{
			$_POST["discount_id"] = DB::query("INSERT INTO {shop_discount} () VALUES ()");
			$this->result["id"] = $_POST["discount_id"];
		}
		Custom::inc('modules/shop/admin/shop.admin.view.php');
		if ( ! DB::query_result("SELECT id FROM {shop_discount_object} WHERE good_id=%d AND discount_id=%d LIMIT 1", $_POST["good_id"], $_POST["discount_id"]))
		{
			DB::query("INSERT INTO {shop_discount_object} (good_id, discount_id) VALUES (%d, %d)", $_POST["good_id"], $_POST["discount_id"]);
		}

		$shop_admin_view = new Shop_admin_view($this->diafan);
		$this->result["data"] = $shop_admin_view->discount_goods($_POST["discount_id"]);
	}

	/**
	 * Удаляет скидку на товар
	 * 
	 * @return void
	 */
	private function delete_discount_good()
	{
		DB::query("DELETE FROM {shop_discount_object} WHERE good_id=%d AND discount_id=%d", $_POST['good_id'], $_POST['discount_id']);

		$this->diafan->_cache->delete("", $this->diafan->_admin->module);
	}

	/**
	 * Клонирует товар
	 * 
	 * @return void
	 */
	private function group_clone()
	{
		// Проверяет права на редактирование
		if (! $this->diafan->_users->roles('edit', 'shop'))
		{
			return;
		}
		foreach ($_POST["ids"] as $id)
		{
			$id = intval($id);
			if(! $id)
				continue;

			$row = DB::query_fetch_array("SELECT * FROM {shop} WHERE id=%d LIMIT 1", $id);
	
			foreach ($row as $k => $v)
			{
				if ($k == 'name'.$this->diafan->_languages->site)
				{
					$v = $this->diafan->_('КОПИЯ').' '.$v;
				}
				$row[$k] = "'".str_replace("'", "\\'", $v)."'";
			}
			unset($row['id']);
			unset($row['counter_buy']);
	
			$n_id = DB::query('INSERT INTO {shop} ('.implode(',', array_keys($row)).') VALUES ('.implode(',', $row).')');
	
			$site_id = $row['site_id'];
	
			$rows = DB::query_fetch_all("SELECT cat_id, trash FROM {shop_category_rel} WHERE element_id='%d'", $id);
			foreach ($rows as $row)
			{
				DB::query("INSERT INTO {shop_category_rel} (element_id, cat_id, trash) VALUES (%d, %d, '%s')", $n_id, $row['cat_id'], $row['trash']);
			}
			
			$prices = array();
			$rows = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE good_id=%d AND trash='0'", $id);
			foreach ($rows as $row)
			{
				$row['good_id'] = $n_id;
				$row_param = array();
				foreach ($row as $k => $v)
				{
					if($k != "id")
					{
						$row_param[$k] = "'".str_replace("'", "\\'", $v)."'";
					}
				}
				$price_id = DB::query('INSERT INTO {shop_price} ('.implode(',', array_keys($row_param)).') VALUES ('.implode(',', $row_param).')');
				if($row["id"] == $row["price_id"])
				{
					$prices[$row["price_id"]] = $price_id;
	
					$rows_param = DB::query_fetch_all("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d", $row["price_id"]);
					foreach ($rows_param as $row_param)
					{
						DB::query("INSERT INTO {shop_price_param} (price_id, param_id, param_value) VALUES (%d, %d, %d)", $price_id, $row_param["param_id"], $row_param["param_value"]);
					}
				}
			}
			foreach ($prices as $old => $new)
			{
				DB::query("UPDATE {shop_price} SET price_id=%d WHERE price_id=%d AND good_id=%d", $new, $old, $n_id);
			}
	
			$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE element_id=%d AND element_type='element' AND module_name='shop' AND trash='0'", $id);
			foreach ($rows as $row)
			{
				$n = array();
				$m = array();
				$vs = array();
				foreach($row as $k => $v)
				{
					if($k == 'id')
						continue;
		
					$n[] = $k;
					switch($k)
					{
						case 'element_id':
							$m[] = "%d";
							$vs[] = $n_id;
							break;
		
						case 'tmpcode':
							$m[] = "''";
							break;
		
						case 'image_id':
							if($v)
							{
								$vs[] = $v;
							}
							else
							{
								$vs[] = $row["id"];
							}
							$m[] = "%d";
							break;
						
						case 'created':
							$m[] = "%d";
							$vs[] = time();
							break;
		
						default:
							$m[] = "'%s'";
							$vs[] = $v;
					}
				}
				$img_id = DB::query("INSERT INTO {images} (".implode(",", $n).") VALUES (".implode(",", $m).")", $vs);
				foreach ($prices as $old => $new)
				{
					$iid = DB::query_result("SELECT id FROM {shop_price_image_rel} WHERE price_id=%d AND image_id=%d", $old, $row['id']);
					if($iid)
					{
						DB::query("INSERT INTO {shop_price_image_rel} (`price_id`, `image_id`, `trash`) VALUES(%d, %d, '0')", $new, $img_id);
					}
				}
			}
	
			$rows = DB::query_fetch_all("SELECT * FROM {shop_param_element} WHERE element_id='%d' AND trash='0'", $id);
			foreach ($rows as $row)
			{
				unset($row["id"]);
				$row['element_id'] = $n_id;
				foreach ($row as $k => &$v)
				{
					$v = "'".str_replace("'", "\\'", $v)."'";
				}
				DB::query('INSERT INTO {shop_param_element} ('.implode(',', array_keys($row)).') VALUES ('.implode(',', $row).')');
			}
		}
	}

	/**
	 * Подгружает список товаров для добавления в заказ
	 * 
	 * @return void
	 */
	private function show_order_goods()
	{
		if (empty($_POST["order_id"]))
		{
			$_POST["order_id"] = 0;
		}
		$nastr = 18;
		$list = '';
		if (empty($_POST["page"]))
		{
			$start = 0;
			$page = 1;
			if ( ! isset($_POST["search"]) && ! isset($_POST["cat_id"]))
			{
				$list .= '<div class="fa fa-close ipopup__close"></div>
				<div class="ipopup__heading">'.$this->diafan->_('Товары').'</div>
				<form>
				<div class="infofield">'.$this->diafan->_('Поиск').'</div> <input type="text" size="30" class="order_goods_search" placeholder="'.$this->diafan->_('Введите несколько символов для поиска').'">
				';

				if($this->diafan->configmodules("cat", "shop"))
				{
					$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {shop_category} WHERE trash='0' ORDER BY sort ASC", "parent_id");
					$vals = array();
					if(! empty($_POST["cat_id"]))
					{
						$vals[] = $this->diafan->filter($_POST, "int", "cat_id");
					}
					$list.= ' <select name="cat_id" class="order_goods_cat_id"><option value="">'.$this->diafan->_('Все').'</option>'.$this->diafan->get_options($cats, $cats[0], $vals).'</select>';
				}
				$list.= '</form><div class="order_all_goods_container">';
			}
		}
		else
		{
			$page = intval($_POST["page"]);
			$start = ($page - 1) * $nastr;
		}
		$list .= '<div class="rel_all_elements">';

		$where = '';
		if(! empty($_POST["cat_id"]))
		{
			$where = " AND cat_id=".$this->diafan->filter($_POST, "int", "cat_id");
		}
		$where .= " ORDER BY sort DESC";

		if ( ! empty($_POST["search"]))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))".$where, $_POST["search"], $_POST["search"]);
			$rows = DB::query_range_fetch_all("SELECT id, [name], no_buy FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))".$where, $_POST["search"], $_POST["search"], $start, $nastr);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'".$where);
			$rows = DB::query_range_fetch_all("SELECT id, [name], no_buy FROM {shop} WHERE trash='0'".$where, $start, $nastr);
		}
		$user_id = DB::query_result("SELECT user_id FROM {shop_order} WHERE id=%d LIMIT 1", $_POST["order_id"]);
		foreach ($rows as &$row)
		{
			$this->diafan->_shop->price_prepare_base($row["id"]);
			$ids[] = $row["id"];
		}
		$param_select_ids = array();
		foreach($rows as &$row)
		{
			$row["prices"] = $this->diafan->_shop->price_get_base($row["id"], true);
			foreach($row["prices"] as &$pr)
			{
				if(! empty($pr["param"]))
				{
					foreach($pr["param"] as $p)
					{
						if(! in_array($p, $param_select_ids))
						{
							$param_select_ids[] = $p;
						}
					}
				}
				if($pr["currency_id"])
				{
					if(! isset($currencies))
					{
						$currencies = DB::query_fetch_key("SELECT id, exchange_rate, name FROM {shop_currency} WHERE trash='0'", "id");
					}
					$pr["price"] = $currencies[$pr["currency_id"]]["exchange_rate"] * $pr["price"];
				}
			}
		}
		if($param_select_ids)
		{
			$param_select = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE id IN (%s)", implode(",", $param_select_ids), "id", "name");
		}
		if($rows)
		{
			$imgs = DB::query_fetch_key("SELECT name, folder_num, element_id FROM {images} WHERE element_id IN(%s) AND module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort DESC", implode(',', $ids), "element_id");
		}
		$goods = array();
		if(! empty($_POST["order_id"]))
		{
			$goods = DB::query_fetch_value("SELECT good_id FROM {shop_order_goods} WHERE order_id=%d", $_POST["order_id"], "good_id");
		}
		if(! empty($_POST["new_goods"]))
		{
			foreach($_POST["new_goods"] as $good_id)
			{
				$good_id = intval($good_id);
				if(! in_array($good_id, $goods))
				{
					$goods[] = $good_id;
				}
			}
		}
		foreach ($rows as &$row)
		{
			if(! $row["name"])
			{
				$row["name"] = $row["id"];
			}
			$list .= '<div class="rel_module order_good'.(in_array($row["id"], $goods) ? ' rel_module_selected' : '').'">
			<div>';
			if(! empty($imgs[$row["id"]]))
			{
				$list .= '<img src="'.BASE_PATH.USERFILES.'/small/'.($imgs[$row["id"]]["folder_num"] ? $imgs[$row["id"]]["folder_num"].'/' : '').$imgs[$row["id"]]["name"].'">';
			}
			if(count($row["prices"]) > 1)
			{
				$list .= '<a href="javascript:void(0)" class="order_good_show_price">'.$row["name"].'</a>';
				$list .= '<div class="order_good_all_price hide"><div class="fa fa-close order_good_price_close"></div>';
				foreach ($row["prices"] as $price)
				{
					if($price["param"])
					{
						$k = 0;
						foreach($price["param"] AS $p)
						{
							if(! empty($param_select[$p]))
							{
								if($k > 0)
								{
									$list .= ', ';
								}
								$list .= $param_select[$p];
							}
							$k++;
						}
					}
					if(! $price["count_goods"] && $this->diafan->configmodules('use_count_goods'))
					{
						$list .= $this->diafan->_shop->price_format($price["price"]).' '.$this->diafan->configmodules("currency", "shop").' <b>'.$this->diafan->_('Товар временно отсутствует').'</b><br>';
					}
					else
					{
						$list .= ' <a href="javascript:void(0)" price_id="'.$price["id"].'" class="order_good_add">'.$this->diafan->_shop->price_format($price["price"]).' '.$this->diafan->configmodules("currency", "shop").'</a><br>';
					}
				}
				$list .= '</div>';
			}
			elseif($row["prices"])
			{
				$price = $row["prices"][0];
				if(! empty($row["no_buy"]) || ! $price["count_goods"] && $this->diafan->configmodules('use_count_goods'))
				{
					$list .= '<b>'.$this->diafan->_('Товар временно отсутствует').'</b>';
				}
				else
				{
					$list .= ' <a href="javascript:void(0)" price_id="'.$price["id"].'" class="order_good_add">'.$row["name"].'<br>'.$this->diafan->_shop->price_format($price["price"]).' '.$this->diafan->configmodules("currency", "shop").'</a>';
				}
			}
			elseif($this->diafan->configmodules("buy_empty_price", "shop"))
			{
				$list .= ' <a href="javascript:void(0)" good_id="'.$row["id"].'" class="order_good_add">'.$row["name"].'</a>';
			}
			$list .= '</div>
			</div>';
		}
		$list .= '</div><div class="paginator order_goods_navig">';
		for ($i = 1; $i <= ceil($count / $nastr); $i ++ )
		{
			if ($i != $page)
			{
				$list .= '<a href="javascript:void(0)" page="'.$i.'">'.$i.'</a> ';
			}
			else
			{
				$list .= '<span class="active">'.$i.'</span> ';
			}
		}
		$list .= '</div>';
		if (empty($_POST["page"]) && ! isset($_POST["search"]))
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;
	}

	/**
	 * Добавляет выбранный товар в заказ
	 * 
	 * @return void
	 */
	private function add_order_good()
	{
		if(! $this->diafan->_users->roles('edit', 'shop/order'))
		{
			return;
		}
		if (empty($_POST["price_id"]) && empty($_POST["good_id"]))
		{
			return;
		}
		$format_price = intval($this->diafan->configmodules("format_price_1", "shop"));
		$depend = '';
		if(! empty($_POST["price_id"]))
		{
			$price = DB::query_fetch_array("SELECT price_id, price, old_price, good_id, discount_id, id FROM {shop_price} WHERE id=%d LIMIT 1", $_POST["price_id"]);
			$where = array();
			$params = array();
			$rows = DB::query_fetch_all("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d AND trash='0'", $price["price_id"]);
			foreach ($rows as $row)
			{
				$params[$row["param_id"]] = $row["param_value"];
				$where[] = "s.param_id=".intval($row["param_id"])." AND s.value=".intval($row["param_value"]);
			}
			if($params)
			{
				foreach ($params as $id => $value)
				{
					if(! $value)
						continue;
					$param_name  = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $id);
					$param_value = DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d LIMIT 1", $value);
					$depend .= ($depend ? ', ' : '').$param_name.': '.$param_value;
				}
			}
			$good = DB::query_fetch_array("SELECT id, [name], article, cat_id, [measure_unit] FROM {shop} WHERE id=%d LIMIT 1", $price["good_id"]);
			$img = DB::query_fetch_array("SELECT i.name, i.folder_num FROM {images} AS i
			LEFT JOIN {shop_price_image_rel} AS r ON r.image_id=i.id AND r.price_id=%d
			WHERE i.element_id=%d AND i.module_name='shop' AND i.element_type='element' AND i.trash='0'
			ORDER BY r.image_id DESC, i.sort ASC LIMIT 1",
			$price["price_id"], $price["good_id"]);
	
			$old_price = $price["old_price"] ? $price["old_price"] : $price["price"];
			if($price["discount_id"])
			{
				$d = DB::query_fetch_array("SELECT discount, deduction FROM {shop_discount} WHERE id=%d LIMIT 1", $price["discount_id"]);
				$discount = $d["discount"] ? $d["discount"].'%' : $d["deduction"].' '.$this->diafan->configmodules("currency", "shop");
			}
		}
		else
		{
			$good = DB::query_fetch_array("SELECT id, [name], article, cat_id, [measure_unit] FROM {shop} WHERE id=%d LIMIT 1", $_POST["good_id"]);
			$img = array();
			$old_price = 0;
			$discount = '';
		}
		$cat_name = $good["cat_id"] ? DB::query_result("SELECT [name] FROM {shop_category} WHERE id=%d", $good["cat_id"]) : '';

		$this->result["data"] = '
		<li class="item">
		<div class="item__in">
			<div class="sum no_important ipad">'.($img ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'">' : '').'</div>
			
			<div class="name"><a href="'.BASE_PATH_HREF.'shop/edit'.$good["id"].'/" good_id="'.$good["id"].'" class="js_order_new_good">'.$good["name"].' '.$depend.' ('.$good["article"].')</a>';
			if(! empty($_POST["price_id"]))
			{
				$this->result["data"] .= '<input type="hidden" name="new_prices[]" value="'.$price["id"].'">';
			}
			else
			{
				$this->result["data"] .= '<input type="hidden" name="new_goods[]" value="'.$good["id"].'">';
			}
			$this->result["data"] .= '<div class="categories">'.$cat_name.'</div></div>
			
			<div class="item__adapt mobile">
				<i class="fa fa-bars"></i>
				<i class="fa fa-caret-up"></i>
			</div>
			<div class="item__seporator mobile"></div>
								
			<div class="num no_important ipad"><nobr><input type="text" name="';
			if(! empty($_POST["price_id"]))
			{
				$this->result["data"] .= 'new_count_prices';
			}
			else
			{
				$this->result["data"] .= 'new_count_goods';
			}
			$this->result["data"] .= '[]" value="1" size="2">';
			if($good["measure_unit"])
			{
				$this->result["data"] .= ' '.$good["measure_unit"];
			}
			$this->result["data"] .= '</nobr></div>';

			if(! empty($_POST["price_id"]))
			{
				$this->result["data"] .= '<div class="num no_important ipad">'.$this->diafan->_shop->price_format($old_price).'</div>
			
				<div class="num no_important ipad">'.(! empty($price["discount_id"]) ? '<a href="'.BASE_PATH_HREF.'shop/discount/edit'.$price["discount_id"].'/">'.$discount.'</a>' : '').'</div>
				
				<div class="num no_important ipad"><input type="text" name="new_price_goods[]" value="'.number_format($price["price"], $format_price, ".", "").'" size="4"></div>
				
				<div class="num">'.$this->diafan->_shop->price_format($price["price"]).'</div>';
			}
			else
			{
				$this->result["data"] .= '<div class="num no_important ipad"></div>
			
				<div class="num no_important ipad"></div>
				
				<div class="num no_important ipad"></div>
				
				<div class="num"></div>';
			}
			
			$this->result["data"] .= '<div class="num"><a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить товар из заказа?').'" class="delete delete_order_good"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a></div>

		</div>';
		if(! empty($_POST["price_id"]))
		{
			$additional_costs = DB::query_fetch_all("SELECT a.id, a.[name], a.price, a.percent, r.summ, r.element_id FROM {shop_additional_cost} AS a
			INNER JOIN {shop_additional_cost_rel} AS r ON r.element_id=%d AND r.additional_cost_id=a.id
			WHERE a.trash='0' AND a.shop_rel='1'
			ORDER BY a.sort ASC", $good["id"]);
			foreach($additional_costs as $a)
			{
				if($a["percent"])
				{
					$a["summ"] = ($price["price"] * $a["percent"]) / 100;
				}
				elseif(! $a["summ"])
				{
					$a["summ"] = $a["price"];
				}
				$this->result["data"] .= '
				<div class="item__in">
					<div class="sum no_important ipad"></div>
					
					<div class="name">'.$a["name"].'</div>
					
					<div></div>
					<div></div>
					<div></div>
					
					<div class="num">
					<input name="additional_cost_id_price_'.$price["id"].'_'.$a["id"].'" id="additional_cost_id_price_'.$price["id"].'_'.$a["id"].'" value="1" type="checkbox" title="'.$this->diafan->_('Добавлено к заказу').'">
					<label for="additional_cost_id_price_'.$price["id"].'_'.$a["id"].'">
					<input type="text" name="summ_additional_cost_price_'.$price["id"].'_'.$a["id"].'" value="'.number_format($a["summ"], $format_price, ".", "").'" size="4"></label></div>
					
					<div class="num no_important ipad">'.number_format($a["summ"], $format_price, ".", "").'
					</div>
					<div class="num no_important ipad"></div>
				</div>';
			}
		}
		$this->result["data"] .= '</li>';
	}

	/**
	 * Проверяет наличие новых заказов
	 * 
	 * @return void
	 */
	private function new_order()
	{
		$last_order_id = $this->diafan->filter($_POST, "int", "last_order_id");

		$this->result["next_order_id"] = DB::query_result("SELECT id FROM {shop_order} WHERE id>%d AND trash='0' LIMIT 1", $last_order_id);
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
			$list = '<div class="fa fa-close ipopup__close"></div>
			<div class="menu_list menu_list_first"><div class="ipopup__heading">'.$this->diafan->_('Страницы сайта').'</div>';
		}
		else
		{
			$list = '<div class="menu_list">';
		}
		
		$rows = DB::query_fetch_all("SELECT id, [name], module_name, count_children FROM {site} WHERE [act]='1' AND trash='0' AND parent_id=%d ORDER BY sort ASC", $_POST["parent_id"]);
		foreach ($rows as $row)
		{
			$list .= '<p site_id="'.$row["id"].'" module_name="site" element_id="" cat_id="">';
			if ($row["count_children"])
			{
				$list .= '<a href="javascript:void(0)" class="plus menu_plus">+</a>';
			}
			else
			{
				$list .= '&nbsp;&nbsp;';
			}
			$list .= '&nbsp;<a href="'.BASE_PATH_HREF.'site/edit'.$row["id"].'/" class="menu_select">'.$row["name"].'</a></p>';
		}
		$list .= '</div>';

		$this->result["data"] = $list;
	}

	/**
	 * Прикрепляет/открепляет характеристику к категории
	 * 
	 * @return void
	 */
	private function param_category()
	{
		if(! empty($_POST["cat_id"]) || ! empty($_POST["ids"]))
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
			if($ids)
			{
				if($_POST["action"] == 'param_category_rel')
				{
					DB::query("DELETE FROM {shop_param_category_rel} WHERE element_id IN(%s) AND cat_id IN(%d, 0)", implode(",", $ids), $_POST["cat_id"]);
				}
				else
				{
					DB::query("DELETE FROM {shop_param_category_rel} WHERE element_id IN(%s) AND cat_id=%d", implode(",", $ids), $_POST["cat_id"]);
				}
			}
			if($_POST["action"] == 'param_category_rel')
			{
				foreach ($ids as $id)
				{
					DB::query("INSERT INTO {shop_param_category_rel} (element_id, cat_id) VALUES (%d, %d)", $id, $_POST["cat_id"]);
				}
			}
			else
			{
				// выбираем все характеристики из выделенных, которые прикреплены к каким-нибудь категориям
				$cats_rel = DB::query_fetch_value("SELECT DISTINCT(element_id) FROM {shop_param_category_rel} WHERE element_id IN (%s)", implode(",", $ids), "element_id");
				// если характеристика не прикреплена ни к одной категории, делаем ее общей
				foreach($ids as $id)
				{
					if(! in_array($id, $cats_rel))
					{
						DB::query("INSERT INTO {shop_param_category_rel} (element_id) VALUES (%d)", $id);
					}
				}
			}
		}
	}

	/**
	 * Прикрепляет/открепляет характеристику к производителю
	 * 
	 * @return void
	 */
	private function brand_category()
	{
		if(! empty($_POST["cat_id"]) || ! empty($_POST["ids"]))
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
			if($ids)
			{
				if($_POST["action"] == 'brand_category_rel')
				{
					DB::query("DELETE FROM {shop_brand_category_rel} WHERE element_id IN(%s) AND cat_id IN(%d, 0)", implode(",", $ids), $_POST["cat_id"]);
				}
				else
				{
					DB::query("DELETE FROM {shop_brand_category_rel} WHERE element_id IN(%s) AND cat_id=%d", implode(",", $ids), $_POST["cat_id"]);
				}
			}
			if($_POST["action"] == 'brand_category_rel')
			{
				foreach ($ids as $id)
				{
					DB::query("INSERT INTO {shop_brand_category_rel} (element_id, cat_id) VALUES (%d, %d)", $id, $_POST["cat_id"]);
				}
			}
			else
			{
				// выбираем все производители из выделенных, которые прикреплены к каким-нибудь категориям
				$cats_rel = DB::query_fetch_value("SELECT DISTINCT(element_id) FROM {shop_brand_category_rel} WHERE element_id IN (%s)", implode(",", $ids), "element_id");
				// если характеристика не прикреплена ни к одной категории, делаем ее общей
				foreach($ids as $id)
				{
					if(! in_array($id, $cats_rel))
					{
						DB::query("INSERT INTO {shop_brand_category_rel} (element_id) VALUES (%d)", $id);
					}
				}
			}
		}
	}

	/**
	 * Групповая операция "Товар временно отсутствует", "Акция" и др.
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
				case 'group_no_buy':
					$set = "no_buy='1'";
					$this->result["action"] = 'group_not_no_buy';
					break;

				case 'group_not_no_buy':
					$set = "no_buy='0'";
					$this->result["action"] = 'group_no_buy';
					break;

				case 'group_hit':
					$set = "hit='1'";
					$this->result["action"] = 'group_not_hit';
					break;

				case 'group_not_hit':
					$set = "hit='0'";
					$this->result["action"] = 'group_hit';
					break;

				case 'group_action':
					$set = "action='1'";
					$this->result["action"] = 'group_not_action';
					break;

				case 'group_not_action':
					$set = "action='0'";
					$this->result["action"] = 'group_action';
					break;

				case 'group_new':
					$set = "new='1'";
					$this->result["action"] = 'group_not_new';
					break;

				case 'group_not_new':
					$set = "new='0'";
					$this->result["action"] = 'group_new';
					break;
			}
			if($ids)
			{
				DB::query("UPDATE {shop} SET ".$set." WHERE id IN (%s)", implode(",", $ids));
				$this->diafan->_cache->delete("", "shop");
			}
		}
	}

	/**
	 * Оптимизировать таблицу БД цены товаров
	 * 
	 * @return void
	 */
	private function optimize_price()
	{
		$service = $this->diafan->filter($_POST, "string", "service");
		$service = preg_replace('/[^a-zA-Z0-9_:;]/', '', $service);
		$service = $this->diafan->str_to_array($service, ';', ':');
		
		$mode_optimize = $this->diafan->filter($_POST, "integer", "mode_optimize");
		$mode_optimize = ! empty($mode_optimize);
		
		if($mode_optimize && (! defined('MOD_DEVELOPER_TECH') || ! MOD_DEVELOPER_TECH))
		{
			$messages = $this->diafan->_('Ошибка: оптимизация прервана. Необходимо перевести сайт в режим обслуживания.');
			$this->result["error"] = false;
			$this->result["messages"] = '<div class="error">'.$messages.'</div>';
			return false;
		}
		
		$max = 500; $sleep = 1;
		if(function_exists('set_time_limit'))
		{
			$disabled = explode(',', ini_get('disable_functions'));
			if(! in_array('set_time_limit', $disabled))
			{
				set_time_limit(0);
			}
		}
		$part = $this->diafan->filter($_POST, "integer", "part");
		$iteration = $this->diafan->filter($_POST, "integer", "iteration");
		
		// пропускаем итерации, если не режим оптимизации
		$part = ! $mode_optimize && $part >= 11 ? $part + 4 : $part;
		
		switch($part)
		{
			case 0:
				$messages = $this->diafan->_('Начинаем процесс проверки ...');
				$count = 0;
				$rows = array();
				break;
				
			case 1:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка характеристик ...');
				$count = 0;
				$rows = array();
				break;
				
			case 2:
				$messages = $this->diafan->_('Проверка характеристик ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop_param} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id, type, required FROM {shop_param} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					if (in_array($row["type"], array('select', 'multiple'))) continue;
					
					if(! $mode_optimize)
					{
						$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_select} WHERE param_id=%d AND trash='0'", $row["id"]);
						$service['shop_param_select'] = ! empty($service['shop_param_select']) ? $service['shop_param_select'] : 0;
						$service['shop_param_select'] += $query_result;
					}
					else
					{
						// удаляем списки характеристик кроме 'select', 'multiple'
						DB::query("DELETE FROM {shop_param_select} WHERE param_id=%d AND trash='0'", $row["id"]);
					}
				}
				break;
			
			case 3:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка значений характеристик ...');
				$count = 0;
				$rows = array();
				break;
				
			case 4:
				$messages = $this->diafan->_('Проверка значений характеристик ...').' %s%%';
				$count = $max * 6;
				switch($iteration)
				{
					case 0:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_select} LEFT JOIN {shop_param} ON {shop_param_select}.param_id={shop_param}.id AND {shop_param_select}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_param_select'] = ! empty($service['shop_param_select']) ? $service['shop_param_select'] : 0;
							$service['shop_param_select'] += $query_result;
						}
						else
						{
							// удаляем списки характеристик, указывающие на не существующие виды характеристики
							DB::query("DELETE {shop_param_select} FROM {shop_param_select} LEFT JOIN {shop_param} ON {shop_param_select}.param_id={shop_param}.id AND {shop_param_select}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;
						
					case 1:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_param_element'] = ! empty($service['shop_param_element']) ? $service['shop_param_element'] : 0;
							$service['shop_param_element'] += $query_result;
						}
						else
						{
							// удаляем значения характеристик, указывающие на не существующие виды характеристики
							DB::query("DELETE {shop_param_element} FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;
						
					case 2:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop_param_element}.trash='0' AND {shop}.trash='0' WHERE {shop}.id IS NULL");
							$service['shop_param_element'] = ! empty($service['shop_param_element']) ? $service['shop_param_element'] : 0;
							$service['shop_param_element'] += $query_result;
						}
						else
						{
							// удаляем значения характеристик, указывающие на не существующие товары
							DB::query("DELETE {shop_param_element} FROM {shop_param_element} LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop_param_element}.trash='0' AND {shop}.trash='0' WHERE {shop}.id IS NULL");
						}
						break;
						
					case 3:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id");
							$service['shop_param_element'] = ! empty($service['shop_param_element']) ? $service['shop_param_element'] : 0;
							$service['shop_param_element'] += $query_result;
						}
						else
						{
							// удаляем значения характеристик, не принадлежащие странице, к которой прикреплен модуль
							DB::query("DELETE {shop_param_element} FROM {shop_param_element} LEFT JOIN {shop_param} ON {shop_param_element}.param_id={shop_param}.id AND {shop_param_element}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop} ON {shop_param_element}.element_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id");
						}
						break;
						
					case 4:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_category_rel} LEFT JOIN {shop_param} ON {shop_param_category_rel}.element_id={shop_param}.id AND {shop_param_category_rel}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_param_category_rel'] = ! empty($service['shop_param_category_rel']) ? $service['shop_param_category_rel'] : 0;
							$service['shop_param_category_rel'] += $query_result;
						}
						else
						{
							// удаляем связь харктеристик с категориями, указывающие на не существующие харктеристики
							DB::query("DELETE {shop_param_category_rel} FROM {shop_param_category_rel} LEFT JOIN {shop_param} ON {shop_param_category_rel}.element_id={shop_param}.id AND {shop_param_category_rel}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;
						
					case 5:
						$rows = array();
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_param_category_rel} LEFT JOIN {shop_category} ON {shop_param_category_rel}.cat_id={shop_category}.id AND {shop_param_category_rel}.trash='0' AND {shop_category}.trash='0' WHERE {shop_param_category_rel}.cat_id<>0 AND {shop_category}.id IS NULL");
							$service['shop_param_category_rel'] = ! empty($service['shop_param_category_rel']) ? $service['shop_param_category_rel'] : 0;
							$service['shop_param_category_rel'] += $query_result;
						}
						else
						{
							// удаляем связь харктеристик с категориями, указывающие на не существующие категории
							DB::query("DELETE {shop_param_category_rel} FROM {shop_param_category_rel} LEFT JOIN {shop_category} ON {shop_param_category_rel}.cat_id={shop_category}.id AND {shop_param_category_rel}.trash='0' AND {shop_category}.trash='0' WHERE {shop_param_category_rel}.cat_id<>0 AND {shop_category}.id IS NULL");
						}
						break;
						
					default:
						$rows = array();
						break;
				}
				break;
				
			case 5:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка параметров цен ...');
				$count = 0;
				$rows = array();
				break;
				
			case 6:
				$messages = $this->diafan->_('Проверка параметров цен ...').' %s%%';
				$count = $max * 9;
				switch($iteration)
				{
					case 0:
						$rows = array(true);
						
						// удаляем цены, указывающие на не существующие товары
						$ids = DB::query_fetch_value("SELECT p.price_id FROM {shop_price} AS p LEFT JOIN {shop} AS s ON p.good_id=s.id AND p.trash='0' AND s.trash='0' WHERE p.id=p.price_id AND s.id IS NULL", "price_id");
						if(! empty($ids))
						{
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += count($ids);
								foreach($ids as $id)
								{
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} WHERE price_id=%d", $id);
									$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
									$service['shop_price_param'] += $query_result;
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} WHERE price_id=%d", $id);
									$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
									$service['shop_price_image_rel'] += $query_result;
								}
							}
							else
							{
								foreach($ids as $id)
								{
									DB::query("DELETE FROM {shop_price} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id=%d", $id);
								}
							}
						}
						break;
						
					case 1:
						$rows = array(true);
						
						// удаляем цены, идентификаторы исходных цен которых указывают на не существующие цены
						$ids = DB::query_fetch_value("SELECT a.price_id FROM {shop_price} AS a LEFT JOIN {shop_price} AS b ON a.price_id=b.id WHERE a.id<>a.price_id AND b.id IS NULL", "price_id");
						if(! empty($ids))
						{
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += count($ids);
								foreach($ids as $id)
								{
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} WHERE price_id=%d", $id);
									$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
									$service['shop_price_param'] += $query_result;
									$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} WHERE price_id=%d", $id);
									$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
									$service['shop_price_image_rel'] += $query_result;
								}
							}
							else
							{
								foreach($ids as $id)
								{
									DB::query("DELETE FROM {shop_price} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $id);
									DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id=%d", $id);
								}
							}
						}
						break;
						
					case 2:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} LEFT JOIN {images} ON {shop_price_image_rel}.image_id={images}.id AND {shop_price_image_rel}.trash='0' AND {images}.trash='0' WHERE {images}.id IS NULL");
							$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
							$service['shop_price_image_rel'] += $query_result;
						}
						else
						{
							// удаляем связь цен с изображениями, указывающую на не существующие картинки
							DB::query("DELETE {shop_price_image_rel} FROM {shop_price_image_rel} LEFT JOIN {images} ON {shop_price_image_rel}.image_id={images}.id AND {shop_price_image_rel}.trash='0' AND {images}.trash='0' WHERE {images}.id IS NULL");
						}
						break;
						
					case 3:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} LEFT JOIN {shop_price} ON {shop_price_image_rel}.price_id={shop_price}.id AND {shop_price_image_rel}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
							$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
							$service['shop_price_image_rel'] += $query_result;
						}
						else
						{
							// удаляем связь цен с изображениями, указывающую на не существующие цены
							DB::query("DELETE {shop_price_image_rel} FROM {shop_price_image_rel} LEFT JOIN {shop_price} ON {shop_price_image_rel}.price_id={shop_price}.id AND {shop_price_image_rel}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
						}
						break;
						
					case 4:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param_select} ON {shop_price_param}.param_value={shop_param_select}.id AND {shop_price_param}.trash='0' AND {shop_param_select}.trash='0' WHERE {shop_price_param}.param_value<>0 AND ({shop_param_select}.id IS NULL OR {shop_price_param}.param_id<>{shop_param_select}.param_id)");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на не существующие списки характеристик
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param_select} ON {shop_price_param}.param_value={shop_param_select}.id AND {shop_price_param}.trash='0' AND {shop_param_select}.trash='0' WHERE {shop_price_param}.param_value<>0 AND ({shop_param_select}.id IS NULL OR {shop_price_param}.param_id<>{shop_param_select}.param_id)");
						}
						break;
						
					case 5:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на не существующие характеристики
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;
						
					case 6:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price}.trash='0' LEFT JOIN {shop} ON {shop_price}.good_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.id IS NULL OR {shop_price}.id IS NULL OR {shop}.id IS NULL OR ({shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id)");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на списки характеристик, не принадлежащие странице, к которой прикреплен модуль
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price}.trash='0' LEFT JOIN {shop} ON {shop_price}.good_id={shop}.id AND {shop}.trash='0' WHERE {shop_param}.id IS NULL OR {shop_price}.id IS NULL OR {shop}.id IS NULL OR ({shop_param}.site_id<>0 AND {shop_param}.site_id<>{shop}.site_id)");
						}
						break;
					
					case 7:
						$rows = array(true);
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_param}.required='1' AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на характеристики, не влияющие на цену
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_param} ON {shop_price_param}.param_id={shop_param}.id AND {shop_param}.required='1' AND {shop_price_param}.trash='0' AND {shop_param}.trash='0' WHERE {shop_param}.id IS NULL");
						}
						break;
						
					case 8:
						$rows = array();
						
						if(! $mode_optimize)
						{
							$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price_param}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
							$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
							$service['shop_price_param'] += $query_result;
						}
						else
						{
							// удаляем параметры цен, указывающие на не существующие цены
							DB::query("DELETE {shop_price_param} FROM {shop_price_param} LEFT JOIN {shop_price} ON {shop_price_param}.price_id={shop_price}.id AND {shop_price_param}.trash='0' AND {shop_price}.trash='0' WHERE {shop_price}.id IS NULL");
						}
						break;
						
					default:
						$rows = array();
						break;
				}
				break;
				
			case 7:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка цен ...');
				$count = 0;
				$rows = array();
				break;
				
			case 8:
				$messages = $this->diafan->_('Проверка цен ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id, site_id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					if(! $pr = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE good_id=%d AND id=price_id AND trash='0' ORDER BY id ASC", $row["id"]))
					continue;
					$p_ids = array();
					foreach($pr as $p)
					{
						$p_ids[] = $p["price_id"];
					}
					$pr_param = DB::query_fetch_key_array("SELECT * FROM {shop_price_param} WHERE price_id IN (%s) AND trash='0' ORDER BY param_id ASC", implode(',', $p_ids), "price_id");

					$cats = DB::query_fetch_value("SELECT cat_id FROM {shop_category_rel} WHERE element_id=%d", $row["id"], "cat_id");
					array_push($cats, 0);
					$params = DB::query_fetch_key_value("SELECT p.id FROM {shop_param} AS p LEFT JOIN {shop_param_category_rel} AS r ON p.id=r.element_id AND p.trash='0' AND r.trash='0' WHERE p.required='1' AND (p.site_id=0 OR p.site_id=%d) AND r.cat_id IN (%s) ORDER BY p.id ASC", $row["site_id"], implode(',', $cats), "id", "id");
					
					foreach($pr as $p)
					{
						// проверяем корректность параметров цен
						if(isset($pr_param[$p["id"]]))
						{
							foreach($pr_param[$p["id"]] as $k => $pa)
							{
								if(! in_array($pa["param_id"], $params))
								{
									if(! $mode_optimize)
									{
										$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
										$service['shop_price_param'] += 1;
									}
									else
									{
										DB::query("DELETE FROM {shop_price_param} WHERE id=%d", $pa["id"]);
									}
									unset($pr_param[$p["id"]][$k]);
								}
							}
						}
						// восстанавливаем параметры цен
						foreach($params as $param_id)
						{
							$isset = false;
							if(isset($pr_param[$p["id"]]))
							{
								foreach($pr_param[$p["id"]] as $k => $pa)
								{
									if($pa["param_id"] != $param_id) continue;
									$isset = true;
									break;
								}
							}
							if($isset) continue;
							
							if(! $mode_optimize)
							{
								$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
								$service['shop_price_param'] += 1;
							}
							else
							{
								DB::query("INSERT INTO {shop_price_param} (price_id, param_id, param_value) VALUES (%d, %d, %d)", $p["id"], $param_id, 0);
							}
						}
						// проверка на наличие дубликатов пройдет на следующем этапе: Оптимизация параметров цен
					}
				}
				break;
				
			case 9:
				sleep($sleep);
				$messages = $this->diafan->_('Проверка оптимизации параметров цен ...');
				$count = 0;
				$rows = array();
				break;
				
			case 10:
				$messages = $this->diafan->_('Проверка оптимизации параметров цен ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				// проверка на наличие дубликатов
				foreach ($rows as $row)
				{
					if(! $pr = DB::query_fetch_all("SELECT * FROM {shop_price} WHERE good_id=%d AND id=price_id AND trash='0' ORDER BY id ASC", $row["id"]))
					continue;
					$uniq = array();
					$p_ids = array();
					foreach($pr as $p)
					{
						$p_ids[] = $p["price_id"];
					}
					$pr_param = DB::query_fetch_key_array("SELECT * FROM {shop_price_param} WHERE price_id IN (%s) AND trash='0' ORDER BY param_id ASC", implode(',', $p_ids), "price_id");
					foreach($pr as $p)
					{
						$u = '';
						if(isset($pr_param[$p["id"]]))
						{
							foreach($pr_param[$p["id"]] as $pa)
							{
								$u .= "_".$pa["param_id"]."_".$pa["param_value"]."_";
							}
						}
						// если есть цена с такими же параметрами (запись более ранняя), удаляем ее
						if(isset($uniq[$u]))
						{
							if(! $mode_optimize)
							{
								$service['shop_price'] = ! empty($service['shop_price']) ? $service['shop_price'] : 0;
								$service['shop_price'] += 1;
								$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_param} WHERE price_id=%d", $uniq[$u]);
								$service['shop_price_param'] = ! empty($service['shop_price_param']) ? $service['shop_price_param'] : 0;
								$service['shop_price_param'] += $query_result;
								$query_result = DB::query_result("SELECT COUNT(*) FROM {shop_price_image_rel} WHERE price_id=%d", $uniq[$u]);
								$service['shop_price_image_rel'] = ! empty($service['shop_price_image_rel']) ? $service['shop_price_image_rel'] : 0;
								$service['shop_price_image_rel'] += $query_result;
							}
							else
							{
								DB::query("DELETE FROM {shop_price} WHERE price_id=%d", $uniq[$u]);
								DB::query("DELETE FROM {shop_price_param} WHERE price_id=%d", $uniq[$u]);
								DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id=%d", $uniq[$u]);
							}
						}
						$uniq[$u] = $p["id"];
					}
				}
				break;
				
			case 11:
				sleep($sleep);
				$messages = $this->diafan->_('Пересчет цен ...');
				$count = 0;
				$rows = array();
				break;
				
			case 12:
				$messages = $this->diafan->_('Пересчет цен ...').' %s%%';
				$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'");
				$rows = DB::query_fetch_all("SELECT id FROM {shop} WHERE trash='0' LIMIT %d, %d", $max * $iteration, $max);
				foreach ($rows as $row)
				{
					$this->diafan->_shop->price_calc($row["id"]);
				}
				break;
				
			case 13:
				sleep($sleep);
				$messages = $this->diafan->_('Переиндексация таблицы цен ...');
				$count = 0;
				$rows = array();
				break;
				
			case 14:
				$messages = $this->diafan->_('Переиндексация таблицы цен ...').' %s%%';
				$count = $max * 8;
				
				$url = parse_url(DB_URL);
				$dbname = substr($url['path'], 1);
				$table = 'shop_price';
				$field = 'id_id';
				$is_field = DB::query_fetch_value("SHOW COLUMNS FROM {".$table."} FROM `".$dbname."` WHERE Field='%s'", $field, 'Field');
				
				switch($iteration)
				{
					case 0:
						$rows = array(true);
						if(! $is_field)
						{
							DB::query("ALTER TABLE {shop_price} DROP PRIMARY KEY, MODIFY `id` INT(11) UNSIGNED NOT NULL DEFAULT '0', AUTO_INCREMENT=1, ADD `".$field."` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT AFTER `id`, ADD PRIMARY KEY(`".$field."`);");
						}
						break;
						
					case 1:
						$rows = array(true);
						if($is_field)
						{
							// TO_DO: если присутствуют не совпадения, то они будут переписаны значением NULL.
							// Если требуется избежать этого и оставить не совпадения как есть, необходимо заменить LEFT JOIN на просто JOIN.
							DB::query("UPDATE {shop_price} AS b LEFT JOIN {shop_price} AS a ON b.`price_id`=a.`id` SET b.`price_id`=a.`".$field."`;");
						}
						break;
						
					case 2:
						$rows = array(true);
						if($is_field)
						{
							// TO_DO: если присутствуют не совпадения, то они будут переписаны значением NULL.
							// Если требуется избежать этого и оставить не совпадения как есть, необходимо заменить LEFT JOIN на просто JOIN.
							DB::query("UPDATE {shop_price_param} LEFT JOIN {shop_price} ON {shop_price_param}.`price_id`={shop_price}.`id` SET {shop_price_param}.`price_id`={shop_price}.`".$field."`;");
						}
						break;
						
					case 3:
						$rows = array(true);
						if($is_field)
						{
							// TO_DO: если присутствуют не совпадения, то они будут переписаны значением NULL.
							// Если требуется избежать этого и оставить не совпадения как есть, необходимо заменить LEFT JOIN на просто JOIN.
							DB::query("UPDATE {shop_price_image_rel} LEFT JOIN {shop_price} ON {shop_price_image_rel}.`price_id`={shop_price}.`id` SET {shop_price_image_rel}.`price_id`={shop_price}.`".$field."`;");
						}
						break;
						
					case 4:
						$rows = array(true);
						if($is_field)
						{
							DB::query("ALTER TABLE {shop_price} DROP `id`;");
						}
						break;
						
					case 5:
						$rows = array(true);
						if($is_field)
						{
							DB::query("ALTER TABLE {shop_price} CHANGE `".$field."` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;");
						}
						break;
						
					case 6:
						$rows = array();
						if($is_field)
						{
							DB::query("ALTER TABLE {shop_price} DROP PRIMARY KEY, ADD PRIMARY KEY(`id`);");
						}
						break;
						
					case 7:
						$rows = array();
						if($is_field)
						{
							// TO_DO: InnoDB
							// Table does not support optimize, doing recreate + analyze instead:
							// CREATE TABLE <NEW.NAME.TABLE> LIKE <TABLE.CRASHED>;
							// INSERT INTO <NEW.NAME.TABLE> SELECT * FROM <TABLE.CRASHED>;
							// RENAME TABLE <TABLE.CRASHED> TO <TABLE.CRASHED.BACKUP>;
							// RENAME TABLE <NEW.NAME.TABLE> TO <TABLE.CRASHED>;
							// DROP TABLE <TABLE.CRASHED.BACKUP>;
							DB::query("OPTIMIZE TABLE {shop_price};");
							DB::query("OPTIMIZE TABLE {shop_price_param};");
							DB::query("OPTIMIZE TABLE {shop_price_image_rel};");
							DB::query("OPTIMIZE TABLE {shop_param};");
							DB::query("OPTIMIZE TABLE {shop_param_select};");
							DB::query("OPTIMIZE TABLE {shop_param_element};");
							DB::query("OPTIMIZE TABLE {shop_param_category_rel};");
						}
						break;
						
					default:
						$rows = array();
						break;
				}
				break;
				
			case 15:
				sleep($sleep);
				$messages = $this->diafan->_('Процесс проверки завершен ...');
				$count = 0;
				$rows = array();
				break;
				
			case 16:
				sleep($sleep);
				$messages = $this->diafan->_('Процесс проверки завершен ...').' %s%%';
				$count = 0;
				$rows = array();
				// удаляем кэш модуля Интернет-магазин
				$this->diafan->_cache->delete("", "shop");
				break;
				
			default:
				sleep($sleep);
				$messages = '';
				$count = 0;
				$rows = array();
				break;
		}
		
		$c = (($max * $iteration) + $max);
		$c = $c < $count ? $c : $count;
		$c = $count > 0 ? ceil($c * 100 / $count) : 100;
		$messages = sprintf($messages, $c);
		
		$this->result["messages"] = '<div class="commentary">'.$messages.'</div>';
		if(count($rows))
		{
			$this->result["error"] = 'next';
		}
		elseif($part <= 16)
		{
			$this->result["error"] = 'next_part';
		}
		else
		{
			if(! $mode_optimize)
			{
				$messages = $this->diafan->_('Цены товаров проверены.');
				$this->result["error"] = $messages;
				$messages = array();
				if(! empty($service))
				{
					foreach($service as $key => $value)
					{
						if(empty($value)) continue;
						$error = true;
						$messages[] = $this->diafan->_("в таблице %s - %s", '{'.$key.'}', $value);
					}
				} 
				
				if(! empty($messages))
				{
					$messages = "<b>".$this->diafan->_('Выявлены ошибки:')."</b>"."<br />".implode("<br />", $messages);
					$this->result["messages"] = '<div class="error">'.$messages.'</div>';
				}
				else
				{
					$messages = $this->diafan->_('Ошибок не выявлено.');
					$this->result["messages"] = '<div class="ok">'.$messages.'</div>';
				}
			}
			else
			{
				$messages = $this->diafan->_('Цены товаров успешно оптимизированы.');
				$this->result["error"] = $messages;
				$this->result["messages"] = '<div class="ok">'.$messages.'</div>';
			}
		}
		
		$this->result["service"] = $this->diafan->array_to_str($service, ';', ':');
	}

	/**
	 * Отправляет письма пользователям о брошенных корзинах
	 * 
	 * @return void
	 */
	private function group_abandonmented_cart_mail()
	{
		if(! $this->diafan->configmodules('message_abandonmented_cart', 'shop') || ! $this->diafan->configmodules('subject_abandonmented_cart', 'shop'))
		{
			return;
		}
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
		if(! $ids)
		{
			return;
		}
		$rows = DB::query_fetch_all("SELECT * FROM {users} WHERE id IN (%s) AND mail<>''", implode(",", $ids));
		if(! $rows)
		{
			return;
		}
		$link_to_cart = $this->diafan->_route->module('cart');
		$rows_id = array();
		foreach($rows as $row)
		{
			$rows_id[] = $row["id"];
		}
		$cart = DB::query_fetch_all(
			"SELECT * FROM {shop_cart} WHERE user_id IN (%s)",
			implode(",", $rows_id),
			"user_id"
		);
		$good_ids = array();
		$param_ids = array();
		$param_values = array();
		$prepare["cart"] = array();
		foreach($cart as $c)
		{
			$prepare["cart"][$c["user_id"]][] = $c;
			if(! in_array($c["good_id"], $good_ids))
			{
				$good_ids[] = $c["good_id"];
				$this->diafan->_route->prepare(0, $c["good_id"], "shop");
			}

			$params = unserialize($c["param"]);
			foreach ($params as $id => $value)
			{
				if(! in_array($id, $param_ids))
				{
					$param_ids[] = $id;
				}
				if(! in_array($value, $param_values))
				{
					$param_values[] = $value;
				}
			}
		}
		if($good_ids)
		{
			$prepare["goods"] = DB::query_fetch_key("SELECT id, [name], article, site_id FROM {shop} WHERE id IN (%s)", implode(",", $good_ids), "id");
		}
		if ($param_ids)
		{
			$prepare["param_names"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param} WHERE id IN (%s)", implode(",", $param_ids), "id", "name");
		}
		if ($param_values)
		{
			$prepare["select_names"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE id IN (%s)", implode(",", $param_values), "id", "name");
		}
		foreach($rows as $row)
		{
			if(empty($prepare["cart"][$row["id"]]))
			{
				continue;
			}
			$text = '';
			foreach($prepare["cart"][$row["id"]] as $i => $c)
			{
				if(empty($prepare["goods"][$c["good_id"]]))
				{
					continue;
				}
				$good = $prepare["goods"][$c["good_id"]];
				if($i)
				{
					$text .= '<br>';
				}
				$text .= '<a href="'.BASE_PATH.$this->diafan->_route->link($good["site_id"], $c["good_id"], "shop").'">'.$good["name"].($good["article"] ? " ".$good["article"] : '');
	
				$params = unserialize($c["param"]);
				foreach ($params as $id => $value)
				{
					if(! empty($prepare["param_names"][$id]) && ! empty($prepare["select_names"][$value]))
					{
						$text .= ', '.$prepare["param_names"][$id].': '.$prepare["select_names"][$value];
					}
				}
				$text .= '</a>';
			}
			if(! $text)
			{
				continue;
			}
			Custom::inc('includes/mail.php');
			$email = ($this->diafan->configmodules("emailconf", 'shop')
					   && $this->diafan->configmodules("email", 'shop')
					   ? $this->diafan->configmodules("email", 'shop') : '' );

			$lang_id = $row["lang_id"];
			if(! $lang_id)
			{
				$lang_id = $this->diafan->_languages->site;
			}
			$subject = str_replace(array('%title', '%url'), array(TITLE, BASE_URL), $this->diafan->configmodules('subject_abandonmented_cart', 'shop', 0, $lang_id));

			$message = str_replace(array('%title', '%url', '%goods', '%link'), array (TITLE, BASE_URL, $text, BASE_PATH.$link_to_cart), $this->diafan->configmodules('message_abandonmented_cart', 'shop', 0, $lang_id));

			send_mail($row["mail"], $subject, $message,  $email);
		}
		$this->result["redirect"] = URL.'success1/'.$this->diafan->get_nav;
	}
}