<?php
/**
 * Модель модуля «Корзина товаров, оформление заказа»
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

class Cart_model extends Model
{
	/**
	 * Генерирует данные для формы оформления заказов
	 * 
	 * @return void
	 */
	public function form()
	{
		$this->result['form_tag'] = 'cart';
		$this->form_errors($this->result, $this->result['form_tag'], array(''));

		$this->form_table();
		$this->form_registration();
		$this->form_param();

		$this->result["payments"] = $this->diafan->_payment->get_all();
		if($this->result["payments"])
		{
			foreach($this->result["payments"] as $i => $row)
			{
				if($row["payment"] == 'balance')
				{
					if($this->diafan->_balance->get() < ceil($this->result["summ"]))
					{
						unset($this->result["payments"][$i]);
					}
				}
			}
		}
		$this->yandex_fast_order();
		$this->result["view"] = 'form';
	}

	/**
	 * Генерирует таблицу купленных товаров
	 * 
	 * @return array
	 */
	public function form_table()
	{
		$this->result["currency"] = $this->diafan->configmodules("currency", "shop");
		$this->result["summ"] = 0;
		$this->result["count"] = 0;
		$this->result["discount"] = false;
		// корзина
		$cart = $this->diafan->_cart->get();
		if (! $cart)
		{
			$this->result["shop_link"] = $this->diafan->_route->module('shop');
			return $this->result;
		}

		$k = 0;
		foreach ($cart as $good_id => $array)
		{
			if (! $row = DB::query_fetch_array("SELECT id, [name], article, cat_id, site_id, [measure_unit] FROM {shop} WHERE [act]='1' AND id = %d AND trash='0' LIMIT 1", $good_id))
			{
				continue;
			}
			$link = $this->diafan->_route->link($row["site_id"], $row["id"], "shop");
			$img = $this->diafan->_images->get('medium', $good_id, 'shop', 'element', $row["site_id"], $row["name"]);
			foreach ($array as $param => &$ar)
			{
				$query = array();
				$params = unserialize($param);

				foreach ($params as $id => $value)
				{
					$query[] = 'p'.$id.'='.$value;
					if (empty($param_names[$id]))
					{
						$param_names[$id] = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $id);
					}
				}
				if($row_price = $this->diafan->_shop->price_get($good_id, $params))
				{
					$row_price["price"] = $this->diafan->_shop->price_format($row_price["price"], true);
					$row_price["old_price"] = $this->diafan->_shop->price_format($row_price["old_price"], true);
				}
				if(! $row_price && ! $this->diafan->configmodules('buy_empty_price', "shop"))
				{
					$this->diafan->_cart->write();
					continue;
				}
				if($this->diafan->configmodules("use_count_goods", "shop"))
				{
					$count = 0;
					foreach ($ar as $additional_cost => &$c)
					{
						if($c["count"] == -1)
						{
							$c["count"] = 0;
						}
						$count += $c["count"];
					}
					if($row_price["count_goods"] < $count)
					{
						$r = $count - $row_price["count_goods"];

						foreach ($ar as $additional_cost => &$c)
						{
							if($c["count"] > 0 && $r)
							{
								if($c["count"] > $r)
								{
									$c["count"] = $c["count"] - $r;
									$r = 0;
								}
								else
								{
									$c["count"] = -1;
									$r -= $c["count"]; 
								}
							}
							$this->diafan->_cart->set($c["count"], $good_id, $param, $additional_cost, "count");
						}
						$this->diafan->_cart->write();
					}
				}
				foreach ($ar as $additional_cost => &$c)
				{
					$this->result["rows"][$k]["name"] = $row["name"];
					$this->result["rows"][$k]["article"] = $row["article"];
					$this->result["rows"][$k]["measure_unit"] = $row["measure_unit"];
					if(! empty($this->result["rows"][$k]["measure_unit"]))
					{
						$this->result["measure_unit"] = true;
					}
					$this->result["rows"][$k]["link"] = $link;

					if($c["count"] < 0)
					{
						$c["count"] = 0;
					}
	
					if($row["cat_id"])
					{
						if (empty($select_cats[$row["cat_id"]]))
						{
							$select_cats[$row["cat_id"]] = array(
									"name" => DB::query_result("SELECT [name] FROM {shop_category} WHERE id=%d LIMIT 1", $row["cat_id"]),
									"link" => $this->diafan->_route->link($row["site_id"], $row["cat_id"], "shop", 'cat')
								);
						}
						$this->result["rows"][$k]["cat"]["name"] = $select_cats[$row["cat_id"]]["name"];
						$this->result["rows"][$k]["cat"]["link"] = $select_cats[$row["cat_id"]]["link"];
					}
					foreach ($params as $id => $value)
					{
						if (empty($select_names[$id][$value]))
						{
							$select_names[$id][$value] =
								DB::query_result("SELECT [name] FROM {shop_param_select} WHERE param_id=%d AND id=%d LIMIT 1", $id, $value);
						}
	
						$this->result["rows"][$k]["name"] .= ', '.$param_names[$id].': '.$select_names[$id][$value];
					}
	
					$price = $row_price["price"];
	
					$this->result["rows"][$k]["link"] .= ! empty($query) ? '?'.implode('&', $query) : '';
					$this->result["rows"][$k]["count"] = $c["count"];
					if ($img)
					{
						if($price_image_rel = DB::query_result("SELECT image_id FROM {shop_price_image_rel} WHERE price_id=%d LIMIT 1", $row_price["price_id"]))
						{
							foreach ($img as $i)
							{
								if($i["id"] == $price_image_rel)
								{
									$this->result["rows"][$k]["img"] = $i;
								}
							}
						}
						if(empty($this->result["rows"][$k]["img"]))
						{
							$this->result["rows"][$k]["img"] = $img[0];
						}
					}
					
					$this->result["rows"][$k]["additional_cost"] = array();
					if($additional_cost)
					{
						$additional_cost_rels = DB::query_fetch_all("SELECT a.id, a.[name], a.percent, a.price, a.amount, r.element_id, r.summ FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_rel} AS r ON r.additional_cost_id=a.id WHERE r.element_id=%d AND a.id IN (%s) AND a.trash='0'", $good_id, $additional_cost);
						foreach($additional_cost_rels as $a_c_rel)
						{
							$a_c_rel["price"] = $this->diafan->_shop->price_format($a_c_rel["price"], true);
							if($a_c_rel["amount"] && $a_c_rel["amount"] <= $row_price["price"])
							{
								$a_c_rel["summ"] = 0;
							}
							elseif($a_c_rel["percent"])
							{
								$a_c_rel["summ"] = ($row_price["price"] * $a_c_rel["percent"]) / 100;
							}
							elseif(! $a_c_rel["summ"])
							{
								$a_c_rel["summ"] = $a_c_rel["price"];
							}
							if($a_c_rel["summ"])
							{
								$a_c_rel["summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"], true);
								$a_c_rel["format_summ"] = $this->diafan->_shop->price_format($a_c_rel["summ"]);
							}
							$price += $a_c_rel["summ"];
							$this->result["rows"][$k]["additional_cost"][] = $a_c_rel;
						}
					}

					$this->result["rows"][$k]["id"] = $row["id"].'_'.str_replace(array('{',':',';','}',' ','"',"'"), '', $param).'_'.$additional_cost;
					$this->result["rows"][$k]["price"] = $this->diafan->_shop->price_format($price);
					$this->result["rows"][$k]["summ"] = $this->diafan->_shop->price_format($price * $c["count"]);
					
					$this->result["rows"][$k]["old_price"] = $row_price["old_price"] ? $this->diafan->_shop->price_format($row_price["old_price"]) : 0;
					$this->result["rows"][$k]["discount"] = 0;
					if($row_price["discount_id"])
					{
						if(! isset($cache["discount"][$row_price["discount_id"]]))
						{
							$cache["discount"][$row_price["discount_id"]] = DB::query_fetch_array("SELECT discount, deduction FROM {shop_discount} WHERE id=%d LIMIT 1", $row_price["discount_id"]);
						}
						$discount = $cache["discount"][$row_price["discount_id"]];
						$this->result["discount"] = true;
						if(! empty($discount["deduction"]))
						{
							$this->result["rows"][$k]["discount"] = $discount["deduction"].' '.$this->diafan->configmodules("currency", "shop");
						}
						else
						{
							$this->result["rows"][$k]["discount"] = $discount["discount"].' %';
						}
						if(! empty($discount["deduction"]))
						{
							$this->result["rows"][$k]["discount_summ"] = $discount["deduction"];
						}
						else
						{
							$this->result["rows"][$k]["discount_summ"] = $this->diafan->_shop->price_format($row_price["price"] / (100 - $this->result["rows"][$k]["discount"])*$this->result["rows"][$k]["discount"]);
						}
					}
					elseif($row_price["old_price"])
					{
						$this->result["discount"] = true;
						$this->result["rows"][$k]["discount"] = $this->diafan->_shop->price_format($row_price["old_price"] - $row_price["price"]).' '.$this->diafan->configmodules("currency", "shop");
					}

					if($c["count"] > 0)
					{
						$this->result["summ"] += $price * $c["count"];
						$this->result["count"] += $c["count"];
					}
					$k++;
				}
			}
		}
		$this->result["summ_goods"] = $this->result["summ"];
		if(! $this->result["count"])
		{
			return $this->result;
		}
		
		$order_summ = 0;
		if($this->diafan->_users->id)
		{
			$order_summ = DB::query_result("SELECT SUM(summ) FROM {shop_order} WHERE user_id=%d AND (status='1' OR status='3')", $this->diafan->_users->id);
		}

		//скидка на общую сумму заказа
		$person_discount_ids = $this->diafan->_shop->price_get_person_discounts();
		$rows = DB::query_fetch_all("SELECT id, discount, amount, deduction, threshold, threshold_cumulative FROM"
			." {shop_discount} WHERE act='1' AND trash='0' AND (threshold_cumulative>0 OR threshold>0)"
			." AND role_id".($this->diafan->_users->role_id ? ' IN (0, '.$this->diafan->_users->role_id.')' : '=0')
			." AND (person='0'".($person_discount_ids ? " OR id IN(".implode(",", $person_discount_ids).")" : "").")"
			." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d) ORDER BY threshold_cumulative ASC, threshold ASC", time(), time()
		);
		foreach ($rows as $row)
		{
			if($row["threshold"] && $row["threshold"] <= $this->result["summ_goods"]  || $row["threshold_cumulative"] && $row["threshold_cumulative"] <= $order_summ)
			{
				if($row['deduction'])
				{
					if($row['deduction'] < $this->result["summ_goods"])
					{
						$row["discount_summ"] = $row["deduction"];
					}
					else
					{
						$row["discount_summ"] = 0;
					}
				}
				else
				{
					$row["discount_summ"] = $this->result["summ_goods"] * $row["discount"] / 100;
				}
				if(empty($this->result["discount_total"]) || $this->result["discount_total"]["discount_summ"] < $row["discount_summ"])
				{
					$this->result["discount_total"] = $row;
				}
			}
			elseif($row["threshold"])
			{
				if($row['deduction'])
				{
					$row["discount_summ"] = $row["threshold"] - $row["deduction"];
				}
				else
				{
					$row["discount_summ"] = $row["threshold"] * $row["discount"] / 100;
				}
				if(empty($this->result["discount_next"]) || $this->result["discount_next"]["discount_summ"] <= $row["discount_summ"] && $this->result["discount_next"]["threshold"] >= $row["threshold"])
				{
					if($row["deduction"])
					{
						$row["discount"] = $row['deduction'].' '.$this->diafan->configmodules('currency', 'shop');
					}
					else
					{
						$row["discount"] .= '%';
					}
					$row["summ"] = $this->diafan->_shop->price_format($row["threshold"] - $this->result["summ_goods"]).' '.$this->diafan->configmodules('currency',  'shop');
					$this->result["discount_next"] = $row;
				}
			}
		}
		if(! empty($this->result["discount_total"]))
		{
			$this->result["old_summ_goods"] = $this->diafan->_shop->price_format($this->result["summ_goods"]);
			$this->result["summ_goods"] = $this->result["summ_goods"] - $this->result["discount_total"]["discount_summ"];
			$this->result["summ"] = $this->result["summ"] - $this->result["discount_total"]["discount_summ"];
			if($this->result["discount_total"]["deduction"])
			{
				$this->result["discount_total"]["deduction"] = $this->diafan->_shop->price_format($this->result["discount_total"]["deduction"]);
				$this->result["discount_total"]["discount"] = $this->result["discount_total"]["deduction"].' '.$this->diafan->configmodules('currency',  'shop');
			}
			else
			{
				$this->result["discount_total"]["discount"] .= '%';
			}
			$this->result["discount"] = true;
		}

		// дополнительно
		$this->result["cart_additional_cost"] = ! empty($_SESSION["cart_additional_cost"]) ? $_SESSION["cart_additional_cost"] : array();
		$this->result["additional_cost"] = DB::query_fetch_all("SELECT id, [name], price, percent, [text], amount, required FROM {shop_additional_cost} WHERE [act]='1' AND trash='0' AND shop_rel='0' ORDER by sort ASC");
		foreach ($this->result["additional_cost"] as &$row)
		{
			$row['price'] = $this->diafan->_shop->price_format($row['price'], true);
			$row["summ"] = $row['price'];
			if($row['percent'])
			{
				$row["summ"] = $this->result["summ_goods"] * $row['percent'] / 100;
			}
			if (! empty($row['amount']))
			{
				if ($row['amount'] < $this->result["summ_goods"])
				{
					$row["summ"] = 0;
				}
			}
			if (in_array($row["id"], $this->result["cart_additional_cost"]) || $row["required"])
			{
				$this->result["summ"] += $row['summ'];
			}
			$row["summ"] = $this->diafan->_shop->price_format($row["summ"]);
		}

		// способы доставки
		$this->result["delivery"] = DB::query_fetch_all("SELECT id, [name], [text], service, params FROM {shop_delivery} WHERE [act]='1' AND trash='0' ORDER BY sort ASC");
		foreach ($this->result["delivery"] as &$row)
		{
			$row['price'] = 0;
			if($row["service"])
			{
				$row["service_view"] = $this->diafan->_delivery->get($row);
			}
			$row["thresholds"] = DB::query_fetch_all("SELECT price, amount FROM {shop_delivery_thresholds} WHERE delivery_id=%d ORDER BY amount ASC", $row["id"]);
			foreach ($row["thresholds"] as &$row_th)
			{
				$row_th['price'] = $this->diafan->_shop->price_format($row_th['price'], true);
				if($row_th['amount'] <= $this->result["summ"])
				{
					$row['price'] = $row_th["price"];
				}
			}

			if (empty($_SESSION["cart_delivery"]))
			{
				$_SESSION["cart_delivery"] = $row['id'];
			}
			if (! empty($_SESSION["cart_delivery"]) && $row['id'] == $_SESSION["cart_delivery"])
			{
				$this->result["summ"] += $row['price'];
			}
		}
		$this->result["cart_delivery"] = ! empty($_SESSION["cart_delivery"]) ? $_SESSION["cart_delivery"] : 0;
		if($this->diafan->configmodules('tax', 'shop'))
		{
			$this->result["tax"] = $this->diafan->_shop->price_format($this->result["summ"] * $this->diafan->configmodules('tax', 'shop') / (100 + $this->diafan->configmodules('tax', 'shop')));
			$this->result["tax_name"] = $this->diafan->configmodules('tax_name', 'shop');
		}
		$this->result["summ"] = $this->diafan->_shop->price_format($this->result["summ"]);
		$this->result["summ_goods"] = ! empty($this->result["summ_goods"]) ? $this->diafan->_shop->price_format($this->result["summ_goods"]) : 0;
		return $this->result;
	}

	/**
	 * Генерирует форму регистрации и авторизации
	 * 
	 * @return void
	 */
	private function form_registration()
	{
		$this->result["show_auth"] = true;
		if ($this->diafan->_users->id || ! DB::query_result("SELECT id FROM {site} WHERE module_name='registration' AND [act]='1' AND trash='0' LIMIT 1"))
		{
			$this->result["show_auth"] = false;
		}
		else
		{
			Custom::inc('modules/registration/registration.model.php');
			$reg = new Registration_model($this->diafan);
			$reg->form();
			$this->result["registration"] = $reg->result;
			$this->result["registration"]["action"] = BASE_PATH_HREF.$this->diafan->_route->module("registration");
			$show_login = array("error" => $this->diafan->_users->errauth ? $this->diafan->_('Неверный логин или пароль.', false) : '', "action" => '', "user" => '', 'hide' => true);

			$this->result["show_login"] = $show_login;
		}
	}

	/**
	 * Генерирует поля формы, созданные в конструкторе
	 *
	 * @param boolean $one_click сокращенная форма для быстрого заказа 
	 * @return void
	 */
	private function form_param($one_click = false)
	{
		$where = "show_in_form".($one_click ? "_one_click" : '')."='1'";
		$this->result["rows_param"] = $this->get_params(array("module" => "shop", "table" => "shop_order", "fields" => "info", "where" => $where));

		$multiple = array();
		$fields = array();
		foreach ($this->result["rows_param"] as $i => $row)
		{
			if(! empty($row["text"]))
			{
				$this->result["rows_param"][$i]["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
			}
			$fields[] = 'p'.$row["id"];
			if ($row["type"] == "multiple")
			{
				$multiple[] = $row["id"];
			}
			if(! $row["info"])
			{
					continue;
			}
			switch($row["info"])
			{
				case 'name':
					$this->result["user"]['p'.$row["id"]] = $this->diafan->_users->fio;
					break;

				case 'phone':
					$this->result["user"]['p'.$row["id"]] = $this->diafan->_users->phone;
					break;

				case 'email':
					$this->result["user"]['p'.$row["id"]] = $this->diafan->_users->mail;
					break;
			}
		}
		if($fields)
		{
			$this->form_errors($this->result, $this->result['form_tag'], $fields);
		}

		// данные о пользователе
		if ($this->diafan->_users->id)
		{
			$rows = DB::query_fetch_all("SELECT param_id, value FROM {shop_order_param_user} WHERE trash='0' AND user_id=%d", $this->diafan->_users->id);
			foreach ($rows as $row)
			{
				if(empty($row["value"]))
					continue;

				if (in_array($row["param_id"], $multiple))
				{
					$this->result["user"]['p'.$row["param_id"]][] = $row["value"];
				}
				else
				{
					$this->result["user"]['p'.$row["param_id"]] = $row["value"];
				}
			}
			$max_order_id = DB::query_result("SELECT MAX(id) FROM {shop_order} WHERE user_id=%d AND trash='0'", $this->diafan->_users->id);
			$rows = DB::query_fetch_all("SELECT value, param_id FROM {shop_order_param_element} WHERE trash='0' AND element_id=%d", $max_order_id);
			foreach ($rows as $row)
			{
				if(! empty($this->result["user"]['p'.$row["param_id"]]))
					continue;

				if (in_array($row["param_id"], $multiple))
				{
					$this->result["user"]['p'.$row["param_id"]][] = $row["value"];
				}
				else
				{
					$this->result["user"]['p'.$row["param_id"]] = $row["value"];
				}
			}
		}
		
		if($this->diafan->configmodules('subscribe_in_order', 'subscription'))
		{
			$this->result['subscribe_in_order'] = true;
		}
	}

	/**
	 * Интеграция с серсивом "Яндекс.Быстрый заказ"
	 * 
	 * @return void
	 */
	private function yandex_fast_order()
	{
		if(! $this->diafan->configmodules('yandex_fast_order', 'shop'))
		{
			return;
		}
		$this->result["yandex_fast_order"] = true;
		$this->result["yandex_fast_order_link"] =  'http'.(IS_HTTPS ? "s" : '').'://market.yandex.ru/addresses.xml?callback='
		.urlencode(BASE_PATH_HREF.$this->diafan->_route->current_link().'?yandex_fast_order=true')
		.'&size=mini';
		if(! empty($_POST["operation_id"]))
		{
			foreach ($this->result["rows_param"] as $i => $row)
			{
				if(! $row["info"])
				{
						continue;
				}
				switch($row["info"])
				{
					case 'address':
						if(! empty($_POST["street"]) || ! empty($_POST["building"]) || ! empty($_POST["suite"]) || ! empty($_POST["flat"]) || ! empty($_POST["entrance"]) || ! empty($_POST["intercom"]) || ! empty($_POST["city"]) || ! empty($_POST["country"]) || ! empty($_POST["zip"]) || ! empty($_POST["metro"]))
						{
							$this->result["user"]['p'.$row["id"]] = '';
							if(! empty($_POST["zip"]))
							{
								$this->result["user"]['p'.$row["id"]] = $this->diafan->filter($_POST, "string", "zip");
							}
							if(! empty($_POST["country"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->filter($_POST, "string", "country");
							}
							if(! empty($_POST["city"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->filter($_POST, "string", "city");
							}
							if(! empty($_POST["metro"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->_('станция метро', false).' '.$this->diafan->filter($_POST, "string", "metro");
							}
							if(! empty($_POST["street"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->filter($_POST, "string", "street");
							}
							if(! empty($_POST["suite"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->filter($_POST, "string", "suite");
							}
							if(! empty($_POST["building"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->_('дом', false).' '.$this->diafan->filter($_POST, "string", "building");
							}
							if(! empty($_POST["suite"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->_('корпус', false).' '.$this->diafan->filter($_POST, "string", "suite");
							}
							if(! empty($_POST["flat"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->_('кв.', false).' '.$this->diafan->filter($_POST, "string", "flat");
							}
							if(! empty($_POST["entrance"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->_('этаж', false).' '.$this->diafan->filter($_POST, "string", "entrance");
							}
							if(! empty($_POST["intercom"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? "\n" : '').$this->diafan->_('домофон', false).' '.$this->diafan->filter($_POST, "string", "intercom");
							}
						}
						break;
					case 'name':
						if(! empty($_POST["firstname"]) || ! empty($_POST["lastname"]) || ! empty($_POST["fathersname"]))
						{
							$this->result["user"]['p'.$row["id"]] = '';
							if(! empty($_POST["firstname"]))
							{
								$this->result["user"]['p'.$row["id"]] = $this->diafan->filter($_POST, "string", "firstname");
							}
							if(! empty($_POST["fathersname"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? ' ' : '').$this->diafan->filter($_POST, "string", "fathersname");
							}
							if(! empty($_POST["lastname"]))
							{
								$this->result["user"]['p'.$row["id"]] = ($this->result["user"]['p'.$row["id"]] ? ' ' : '').$this->diafan->filter($_POST, "string", "lastname");
							}
						}
						break;
					default:
						if(! empty($_POST[$row["info"]]))
						{
							$this->result["user"]['p'.$row["id"]] = $this->diafan->filter($_POST, "string", $row["info"]);
						}
						break;
					}
			}
		}
	}

	/**
	 * Генерирует данные для второго шага в оформлении заказа: оплата
	 * 
	 * @return void
	 */
	public function payment()
	{
		if(empty($_GET["code"]))
		{
			Custom::inc('includes/404.php');
		}
		$this->result = $this->diafan->_payment->get_pay($this->diafan->_route->show, 'cart', $_GET["code"]);		
		$this->result["view"] = "payment";
	}

	/**
	 * Генерирует данные для третьего шага в оформлении заказа: результат оплаты
	 * 
	 * @return void
	 */
	public function result()
	{
		if ($this->diafan->_route->step == 3)
		{
			if($this->diafan->configmodules('order_redirect', 'shop'))
			{
				if(preg_match("/^([0-9]+)$/", $this->diafan->configmodules('order_redirect', 'shop')))
				{
					$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->configmodules('order_redirect', 'shop'));
				}
				else
				{
					$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->configmodules('order_redirect', 'shop').ROUTE_END;
				}
			}
			$this->result["text"] = $this->diafan->configmodules("payment_success_text", "shop");
		}
		else
		{
			$this->result["text"] = $this->diafan->configmodules("payment_fail_text", "shop");
		}
		$this->result["view"] = "result";
	}

	/**
	 * Генерирует данные для шаблонной функции: выводит информацию о заказанных товарах
	 *
	 * @param boolean $tag функция вызвана при генерировании шаблонного тега
	 * @return array
	 */
	public function show_block($tag = false)
	{
		$link = $this->diafan->_route->module("cart");
		if($link)
		{
			if($this->diafan->_site->module != 'cart')
			{
				$result = $this->form_table();
				$result['form_tag'] = 'cart_block_form';
				$this->form_errors($result, $result['form_tag'], array(''));
			}
			$result["count"] = $this->diafan->_cart->get_count();
			$result["summ"] = $this->diafan->_shop->price_format($this->diafan->_cart->get_summ());

			$result["link"] = BASE_PATH_HREF.$link.'?'.rand(0, 999999);
			$result["currency"] = $this->diafan->configmodules("currency", "shop");
			return $result;
		}
	}

	/**
	 * Генерирует данные для шаблонной функции: выводит информацию о последнем совершенном заказе
	 *
	 * @return array
	 */
	public function show_last_order()
	{
		if(empty($_SESSION["order"]))
		{
			return;
		}
		$order_id = $_SESSION["order"][count($_SESSION["order"]) - 1];
		if(! $order_id)
		{
			return;
		}
		$result = $this->diafan->_shop->order_get($order_id);
		$result["param"] = $this->diafan->_shop->order_get_param($order_id);
		
		return $result;
	}

	/**
	 * Генерирует данные для формы быстрого заказа 
	 * 
	 * @return array
	 */
	public function one_click()
	{
		$this->result['form_tag'] = 'cart_one_click';
		$this->form_errors($this->result, $this->result['form_tag'], array(''));
		$this->form_param(true);
		return $this->result;
	}
}