<?php
/**
 * Отчет о продажах
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
 * Shop_admin_ordercount
 */
class Shop_admin_ordercount extends Frame_admin
{
	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'created' => array(
			'name' => 'Период',
			'type' => 'date_interval',
			'links' => true,
		),
		'status_id' => array(
			'type' => 'multiselect',
			'name' => 'Искать по статусу',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if ($this->diafan->is_action("edit"))
		{
			Custom::inc('includes/404.php');
		}

		$select = array();
		$rows = DB::query_fetch_all("SELECT id, [name], status, color FROM {shop_order_status} WHERE trash='0' ORDER BY sort ASC");
		foreach ($rows as $row)
		{
			$this->cache["status"][$row["id"]] = $row["status"];
			$this->cache["status_color"][$row["id"]] = $row["color"];
			$select[$row["id"]] = $row["name"];
			if(! isset($_GET["filter_status_id"]) && $row["status"] == 3)
			{
				$_GET["filter_status_id"][] = $row["id"];
			}
		}
		$this->diafan->variable_filter("status_id", 'select', $select);
	}

	/**
	 * Выводит список заказов
	 * @return void
	 */
	public function show()
	{
		$where   = " WHERE e.trash='0'".$this->diafan->where; //начало условия отбора: не удаленные заказы со статустом "Выполнен"

		$this->diafan->_paginator->page    = $this->diafan->_route->page;
		$this->diafan->_paginator->navlink = $this->diafan->_admin->rewrite.'/';
		$this->diafan->_paginator->get_nav = $this->diafan->get_nav;
		$this->diafan->_paginator->nen     = DB::query_result("SELECT COUNT(*) FROM {shop_order} AS e".$where);
		$result["links"] = $this->diafan->_paginator->get();

		$result["rows"] = array();
		$result["summ"] = DB::query_result("SELECT SUM(e.summ) FROM {shop_order} AS e".$where);
		$result["count"] = $this->diafan->_paginator->nen;
		$result["count_goods"] = DB::query_result("SELECT COUNT(g.id) FROM {shop_order_goods} AS g INNER JOIN {shop_order} AS e ON e.id=g.order_id".$where);

		//забираем все заказы, удовлетворяющие фильтру
		$rows1 = DB::query_range_fetch_all("SELECT * FROM {shop_order} AS e".$where." ORDER BY created DESC", $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		
		foreach ($rows1 as $row1)
		{
			//берем каждый заказ и забираем из БД его товары (ограничение 100 товаров на заказ)
			$rows = DB::query_range_fetch_all("SELECT * FROM {shop_order_goods} WHERE order_id=%d", $row1["id"], 0, 100);

			foreach ($rows as $row)
			{
				$params = '';
				$rows_p = DB::query_fetch_all("SELECT * FROM {shop_order_goods_param} WHERE order_goods_id=%d", $row["id"]); 
				foreach ($rows_p as $row_p)
				{
					$param_name  = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $row_p["param_id"]);
					$param_value = DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d AND param_id=%d LIMIT 1", $row_p["value"], $row_p["param_id"]);
					$params .= ($params ? ', ' : '').$param_name.': '.$param_value;
				}
				$good = DB::query_fetch_array("SELECT [name], article FROM {shop} WHERE id=%d LIMIT 1", $row["good_id"]);

				$row["link"] = BASE_PATH_HREF.'shop/edit'.$row["good_id"].'/';

				$row["name"] = $good["name"].($good["article"] ? " ".$good["article"] : '')." ".$params;

				//выясняем, заказ делал пользователь или нет
				if ($row1["user_id"])
				{
						$row["user_link"] = BASE_PATH_HREF.'users/edit'.$row1["user_id"].'/';
						$row["user"]      = DB::query_result("SELECT CONCAT(fio, ' (', name, ')') FROM {users} WHERE id=%d LIMIT 1", $row1["user_id"]);
				}

				$row["created"] = $row1["created"]; //запоминаем дату текущего заказа
				$result["rows"][] = $row;
			}
		}

		$this->template($result);
	}

	/**
	 * Шаблон вывода
	 * @return boolean true
	 */
	public function template($result)
	{
		echo '<form action="" method="POST">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="action" value="">
		<input type="hidden" name="id" value="">
		<input type="hidden" name="module" value="">';
		$filter = $this->diafan->get_filter();
		if($filter)
		{
			echo '<div class="content__left">
			<div class="action-box"><div class="btn btn_blue btn_small btn_filter">
				<i class="fa fa-filter"></i>
				'.$this->diafan->_('Фильтровать').'
			</div></div>';
		}
		$paginator = '';
		if($result["links"])
		{
			$paginator = '<div class="paginator">'.$this->diafan->_tpl->get('get_admin', 'paginator', $result["links"]);
			$paginator .= '<div class="paginator__unit">
				'.$this->diafan->_('Показывать на странице').':
				<input name="nastr" type="text" value="'.$this->diafan->_paginator->nastr.'">
				<button type="button" class="btn btn_blue btn_small change_nastr">'.$this->diafan->_('ОК').'</button>
			</div>';
			$paginator .= '</div>';
		}
		echo $paginator;

		echo '
		<ul class="list list_stat do_auto_width">
			<li class="item item_heading">
				<div class="item__th">'.$this->diafan->_('Дата').'</div>
				<div class="item__th">'.$this->diafan->_('Товар').'</div>
				<div class="item__th">'.$this->diafan->_('Сумма').'</div>
				<div class="item__th">'.$this->diafan->_('Заказ №').'</div>
				<div class="item__th">'.$this->diafan->_('Пользователь').'</div>
				<div class="item__th item__th_adapt"></div>
				<div class="item__th item__th_seporator"></div>
			</li>';
		foreach ($result["rows"] as $row)
		{
			echo '
			<li class="item">
			<div class="item__in">
			<div class="no_important">'.date("d.m.Y H:i", $row["created"]).'</div>
			<div class="name"><a href="'.$row["link"].'">'.$row["name"].'</a></div>
			
			<div class="num">'.$this->diafan->_shop->price_format($row["price"]).' '.$this->diafan->configmodules("currency", "shop").'</div>
			<div class="num"><a href="'.BASE_PATH_HREF.'shop/order/edit'.$row["order_id"].'/">'.$this->diafan->_('Заказ').' '.$row["order_id"].'</a></div>
			<div class="user no_important">';
			if (! empty($row["user"]))
			{
				echo '<a href="'.$row["user_link"].'">'.$row["user"].'</a>';
			} else 
			{
				echo $this->diafan->_('Без регистрации');
			}
			echo '</div>

			<div class="item__adapt mobile">
				<i class="fa fa-bars"></i>
				<i class="fa fa-caret-up"></i>
			</div>
			<div class="item__seporator mobile"></div>									
			
			</div></li>';
		}
		
		echo '</ul>';
		echo $paginator;
		
		echo '<div class="orders_bottom">
			<p>'.$this->diafan->_('Всего товаров').': <span>'.$result["count_goods"].'</span></p>
			<p>'.$this->diafan->_('Всего заказов').': <span>'.$result["count"].'</span></p>
			<p>'.$this->diafan->_('На сумму').': <span>'.$this->diafan->_shop->price_format($result["summ"]).' '.$this->diafan->configmodules("currency", "shop").'</span></p>';
			echo '<p>'.$this->diafan->_('Средний чек').': <span>'.($result["count"] ? $this->diafan->_shop->price_format($result["summ"] / $result["count"]) : 0).' '.$this->diafan->configmodules("currency", "shop").'</span></p></div>';

		if($filter)
		{
			echo '</div>';
		}
		echo '</form>';
		echo $filter;
	}
}