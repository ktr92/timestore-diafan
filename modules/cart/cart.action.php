<?php
/**
 * Обрабатывает полученные данные из формы
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

class Cart_action extends Action 
{
	/**
	 * Пересчет суммы заказа
	 * 
	 * @return void
	 */
	public function recalc()
	{
		$cart = $this->diafan->_cart->get();
		$newcount = 0;
		if ($cart)
		{
			foreach ($cart as $good_id => $array)
			{
				foreach ($array as $param => $ar)
				{
					foreach ($ar as $additional_cost => $c)
					{
						$index = $good_id.'_'.str_replace(array('{',':',';','}',' ','"',"'"), '', $param).'_'.$additional_cost;
						if (! empty($_POST['del'.$index]))
						{
							$_POST['editshop'.$index] = 0;
						}
						elseif(empty($_POST['editshop'.$index]))
						{
							continue;
						}
						$_POST['editshop'.$index] = $this->diafan->filter($_POST, 'float', 'editshop'.$index);
						$newcount += $_POST['editshop'.$index];
						if($err = $this->diafan->_cart->set($_POST['editshop'.$index], $good_id, $param, $additional_cost, "count"))
						{
							$this->result["errors"][0] = $err;
						}
					}
				}
			}
		}

		if (empty($this->result["errors"][0]))
		{
			$this->diafan->_cart->write();

			$this->result["errors"][0] = $this->diafan->_('Изменения сохранены.', false);
		}
		if(! $newcount && $this->diafan->_site->module == 'cart')
		{
			Custom::inc('plugins/json.php');
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->module('cart');
			return;
		}

		$_SESSION["cart_delivery"] = $this->diafan->filter($_POST, 'int', 'delivery_id');

		$_SESSION["cart_additional_cost"] = array();
		if(! empty($_POST["additional_cost_ids"]))
		{
			foreach ($_POST["additional_cost_ids"] as $additional_cost_id)
			{
				$_SESSION["cart_additional_cost"][] = intval($additional_cost_id);
			}
		}

		$block = $this->model->show_block();
		$form = $this->model->form_table();
		
		if(! empty($this->result["errors"][0]))
		{
			$block["error"] = $this->result["errors"][0];
		}

		$this->result["data"] = array(
			"#show_cart" => $this->diafan->_tpl->get('info', 'cart', $block),
			".cart_table" => $this->diafan->_tpl->get('table', 'cart', $form),
		);
		$this->diafan->_site->js_view = array();
		if (! $this->diafan->_cart->get())
		{
			$this->result["data"]['.cart_form, .cart_autorization'] = false;
		}
	}

	/**
	 * Оформление заказа
	 * 
	 * @return void
	 */
	public function order()
	{
		$this->tag = 'cart';
		$where = "show_in_form".($_POST["action"] == "one_click" ? "_one_click" : '')."='1'";
		$params = $this->model->get_params(array("module" => "shop", "table" => "shop_order", "where" => $where, "fields" => "info"));

		$this->empty_required_field(array("params" => $params));

		if(! empty($_SESSION["cart_delivery"]))
		{
			$delivery = DB::query_fetch_array("SELECT * FROM {shop_delivery} WHERE [act]='1' AND trash='0' AND id=%d LIMIT 1", $_SESSION["cart_delivery"]);
			// стоимость доставки рассчитывается сторонним скриптом
			if($delivery["service"])
			{
				$this->diafan->_delivery->get($delivery);
				// стоимость доставки может быть равна 0 при определенной сумме заказа
				// тогда при достижении этой суммы, стоимость доставки не рассчитывается
				$threshold = DB::query_fetch_array("SELECT * FROM {shop_delivery_thresholds} WHERE delivery_id=%d AND price=0 AND amount>0", $_SESSION["cart_delivery"]);
				if($threshold)
				{
					if($threshold["amount"] <= $_SESSION["cart_summ"])
					{
						$delivery['price'] = 0;
					}
				}
				if(is_null($delivery['price']))
				{
					$this->result["errors"][0] = $this->diafan->_('Выбранный способ доставки не доступен в Вашем городе.', false);
				}
				elseif($delivery['price'] === false)
				{
					$this->result["errors"][0] = $this->diafan->_('Пожалуйста, задайте дополнительные опции доставки.', false);
				}
			}
		}

		if ($this->result())
			return;

		$cart = $this->diafan->_cart->get();
		if(! $cart)
		{
			$this->result["errors"][0] = $this->diafan->_('Корзина пуста. Обновите страницу.', false);
			if ($this->result())
				return;
		}

		$summ = 0;
		$status = DB::query_fetch_array("SELECT * FROM {shop_order_status} WHERE status='0' LIMIT 1");

		$order_id = DB::query("INSERT INTO {shop_order} (user_id, created, status, status_id, lang_id) VALUES (%d, %d, '0', %d, %d)",
			$this->diafan->_users->id,
			time(),
				$status["id"],
			_LANG
		);

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='shop_order' AND element_id=0 AND tmpcode='%s'", $order_id, $_POST["tmpcode"]);
		}

		$this->insert_values(array("id" => $order_id, "table" => "shop_order", "params" => $params));

		if ($this->result())
			return;

		// товары
		$goods_summ = 0;
		foreach ($cart as $good_id => $array)
		{
			foreach ($array as $param => $ar)
			{
				foreach ($ar as $additional_cost => $c)
				{
					if($c["count"] <= 0)
						continue;

					$price = 0;
					$select_depend = 0;
					$sparams = unserialize($param);
	
					$shop_good_id = DB::query("INSERT INTO {shop_order_goods} (order_id, good_id, count_goods) VALUES (%d, %d, %f)", $order_id, $good_id, $c["count"]);
	
					foreach ($sparams as $id => $value)
					{
						DB::query("INSERT INTO {shop_order_goods_param} (order_goods_id, value, param_id) VALUES ('%d', '%d', '%d')", $shop_good_id, $value, $id);
					}
					if($row = $this->diafan->_shop->price_get($good_id, $sparams))
					{
						$row["price"] = $this->diafan->_shop->price_format($row["price"], true);
					}

					if($additional_cost)
					{
						$additional_costs = DB::query_fetch_all("SELECT a.id, a.percent, a.price, a.amount, r.summ FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_rel} AS r ON r.additional_cost_id=a.id WHERE r.element_id=%d AND a.id IN (%s) AND a.trash='0'", $good_id, $additional_cost);
					}
					else
					{
						$additional_costs = array();
					}
					foreach($additional_costs as $a_c)
					{
						$a_c_price = 0;
						if($a_c["amount"] && $a_c["amount"] <= $row["price"])
						{
							$a_c_price = 0;
						}
						elseif($a_c["percent"])
						{
							$a_c_price = ($row["price"] * $a_c["percent"]) / 100;
						}
						else
						{
							if(! $a_c["summ"])
							{
								$a_c_price = $a_c["price"];
							}
							else
							{
								$a_c_price = $a_c["summ"];
							}
						}
						DB::query("INSERT INTO {shop_order_additional_cost} (order_goods_id, order_id, additional_cost_id, summ) VALUES (%d, %d, %d, %f)", $shop_good_id, $order_id, $a_c["id"], $a_c_price * $c["count"]);
						$goods_summ += $a_c_price * $c["count"];
					}
	
					DB::query("UPDATE {shop_order_goods} SET price=%f, discount_id=%d WHERE id=%d", $row["price"], $row["discount_id"], $shop_good_id);
					$goods_summ += $row["price"] * $c["count"];
				}
			}
		}
		$summ += $goods_summ;

		if($discount = $this->get_discount_total($goods_summ))
		{
			$summ -= $discount["discount_summ"];
		}
		else
		{
			$discount["discount_summ"] = 0;
			$discount["discount_id"] = 0;
		}

		// дополнительная стоимость
		$cart_additional_cost = ! empty($_SESSION["cart_additional_cost"]) ? $_SESSION["cart_additional_cost"] : array();
		$rows = DB::query_fetch_all("SELECT id, price, percent, amount, required FROM {shop_additional_cost} WHERE [act]='1' AND trash='0' AND shop_rel='0'");
		foreach ($rows as $row)
		{
			if (in_array($row["id"], $cart_additional_cost) || $row["required"])
			{
				$row['price'] = $this->diafan->_shop->price_format($row["price"], true);
				$row["summ"] = $row['price'];
				if($row['percent'])
				{
					$row["summ"] = $goods_summ * $row['percent'] / 100;
				}
				$row['summ'] = $this->diafan->_shop->price_format($row["summ"], true);
				if (! empty($row['amount']))
				{
					if ($row['amount'] < $goods_summ)
					{
						$row["summ"] = 0;
					}
				}
				DB::query("INSERT INTO {shop_order_additional_cost} (order_id, additional_cost_id, summ) VALUES (%d, %d, %f)", 
						$order_id, $row["id"], $row["summ"]);
				$summ += $row['summ'];
			}
		}

		// доставка
		$delivery_id = 0;
		$delivery_summ = 0;
		$delivery_info = '';
		if(! empty($_SESSION["cart_delivery"]))
		{
			if($delivery)
			{
				if($delivery["service"])
				{
					$delivery_summ = $delivery['price'];
					if (! empty($delivery["service_info"]))
					{
						$delivery_info = $delivery["service_info"];
					}
				}
				else
				{
					$delivery_summ = DB::query_result("SELECT price FROM {shop_delivery_thresholds} WHERE delivery_id=%d AND amount<=%f ORDER BY amount DESC LIMIT 1", $_SESSION["cart_delivery"], $summ);
				}
				$delivery_summ = $this->diafan->_shop->price_format($delivery_summ, true);
				$summ += $delivery_summ;
				$delivery_id = $delivery["id"];
			}
		}

		DB::query("UPDATE {shop_order} SET summ=%f, discount_id=%d, discount_summ=%f, delivery_summ=%f, delivery_info='%s', delivery_id=%d WHERE id=%d", $summ, $discount["discount_id"], $discount["discount_summ"], $delivery_summ, $delivery_info, $delivery_id, $order_id);

		if($this->diafan->configmodules('order_redirect', 'shop'))
		{
			if(preg_match("/^[0-9]+$/", $this->diafan->configmodules('order_redirect', 'shop')))
			{
				$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->configmodules('order_redirect', 'shop'));
			}
			else
			{
				$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->configmodules('order_redirect', 'shop').ROUTE_END;
			}
		}
		if(empty($_POST["payment_id"]))
		{
			if(empty($this->result["redirect"]))
			{
				$this->result["data"] = array(
					'.cart_table_form' => false,
					'form' => str_replace(
						array('%id', '%summ'),
						array($order_id, $summ),
						$this->diafan->configmodules('mes', 'shop')
					),
				);
			}
			$payment = false;
		}
		else
		{
			$this->diafan->_payment->add_pay($order_id, 'cart', $_POST["payment_id"], $summ);

			$payment = $this->diafan->_payment->get($_POST["payment_id"]);
			
			if($payment["payment"])
			{
				$this->result["redirect"] = BASE_PATH_HREF.str_replace('ROUTE_END', '', $this->diafan->_route->link($this->diafan->_site->id, 0, "cart", 'element', false)).'/step2/show'.$order_id.ROUTE_END.'?code='.$this->diafan->_payment->code;
			}
			elseif(empty($this->result["redirect"]))
			{
				$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id).'#top';
			}
		}
		$this->send_sms();

		$_SESSION["order"][] = $order_id;

		$this->diafan->_cart->set();
		$this->diafan->_cart->write();

		unset($_SESSION["cart_delivery"]);
		unset($_SESSION["cart_additional_cost"]);

		$this->send_mails($order_id, $params, $payment);

		$order = array(
			"id" => $order_id,
			"count_minus" => false,
			"lang_id" => _LANG,
		);
		$this->diafan->_shop->order_set_status($order, $status);

		// если у пользователя купон на скидку с ограниченным действием, используем один раз купон
		$rows_coupon = DB::query_fetch_all("SELECT c.id, c.count_use, c.used, c.discount_id FROM {shop_discount} AS d"
			." INNER JOIN {shop_discount_coupon} AS c ON c.discount_id=d.id"
			." INNER JOIN {shop_discount_person} AS p ON p.discount_id=d.id AND p.coupon_id=c.id AND p.used='0'"
			." WHERE d.act='1' AND d.trash='0' AND (c.count_use=0 OR c.count_use>c.used) AND (p.user_id>0 AND p.user_id=%d OR p.session_id='%s')"
			." GROUP BY c.id",
			$this->diafan->_users->id, $this->diafan->_session->id);
		foreach ($rows_coupon as $coupon)
		{
			DB::query("UPDATE {shop_discount_coupon} SET used=used+1 WHERE id=%d", $coupon["id"]);
			DB::query("UPDATE {shop_discount_person} SET used='1' WHERE discount_id=%d AND coupon_id=%d AND used='0' AND (user_id>0 AND user_id=%d OR session_id='%s')", $coupon["discount_id"], $coupon["id"], $this->diafan->_users->id, $this->diafan->_session->id);
			if(! DB::query_result("SELECT COUNT(*) FROM {shop_discount_coupon} WHERE discount_id=%d AND (count_use=0 OR count_use>used)", $coupon["discount_id"]))
			{
				DB::query("UPDATE {shop_discount} SET act='0' WHERE id=%d", $coupon["discount_id"]);
				$this->diafan->_shop->price_calc(0, $coupon["discount_id"]);
			}
		}
		$this->result["result"] = "success";
	}

	/**
	 * Получает скидку от общей суммы товаров
	 *
	 * @return float
	 */
	private function get_discount_total($cart_summ)
	{
		$discount = false;
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
			." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)"
			." AND (threshold_cumulative>0 AND threshold_cumulative<=%f"
			." OR threshold>0 AND threshold<=%f)",
			time(), time(), $order_summ, $cart_summ
		);
		foreach ($rows as $row)
		{
			$row["discount_id"] = $row["id"];
			if($row['deduction'])
			{
				if($row['deduction'] < $cart_summ)
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
				$row["discount_summ"] = $cart_summ * $row["discount"] / 100;
			}
			if(empty($discount) || $discount["discount_summ"] < $row["discount_summ"])
			{
				$discount = $row;
			}
		}
		return $discount;
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	public function upload_image()
	{
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		if(empty($_POST["images_param_id"]))
		{
			return;
		}
		$param_id = $this->diafan->filter($_POST, "int", "images_param_id");

		$this->result["result"] = 'success';
		if (! empty($_FILES['images'.$param_id]) && $_FILES['images'.$param_id]['tmp_name'] != '' && $_FILES['images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload(0, "shop_order", 'element', 0, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $_POST["tmpcode"]);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'p'.$param_id;
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$images = $this->diafan->_images->get('large', 0, "shop_order", 'element', 0, '', $param_id, 0, '', $_POST["tmpcode"]);
			$this->result["data"] = $this->diafan->_tpl->get('images', "cart", $images);
		}
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	public function delete_image()
	{
		if(empty($_POST["id"]))
		{
			return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='shop_order' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
		$this->result["result"] = 'success';
	}

	/**
	 * Оформление быстрого заказа
	 * 
	 * @return void
	 */
	public function one_click()
	{
		if(! empty($_POST["good_id"]))
		{
			$cart = $this->diafan->_cart->get();
			if ($cart)
			{
				foreach ($cart as $good_id => $array)
				{
					foreach ($array as $param => $arr)
					{
						foreach ($arr as $additional_cost => $c)
						{
							$err = $this->diafan->_cart->set(0, $good_id, $param, $additional_cost, "count");
						}
					}
				}
			}
			$good_id = $this->diafan->filter($_POST, 'int', 'good_id');
			$this->tag = 'shop'.$good_id;
	
			$row = DB::query_fetch_array("SELECT id, is_file FROM {shop} WHERE id=%d AND trash='0' AND [act]='1' LIMIT 1", $good_id);
	
			if (empty($row['id']))
			{
				$this->result["errors"][0] = 'ERROR';
				return;
			}
	
			$params = array();
	
			$rows_param = DB::query_fetch_all(
					"SELECT p.[name], p.id FROM {shop_param} AS p"
					." INNER JOIN {shop_param_element} AS e ON e.element_id=%d AND e.param_id=p.id"
					." WHERE p.`type`='multiple' AND p.required='1' GROUP BY p.id",
					$good_id
				);
			foreach ($rows_param  as $row_param)
			{
				if (empty($_POST["param".$row_param["id"]]))
				{
					$this->result["errors"][0] = $this->diafan->_('Пожалуйста, выберите %s.', false, $row_param["name"]);
					return;
				}
				$params[$row_param["id"]] = $this->diafan->filter($_POST, "int", "param".$row_param["id"]);
			}
	
			$count = $this->diafan->filter($_POST, "int", "count", 1);
			$count = $count > 0 ? $count : 1;
	
			$count_good = $this->diafan->_cart->get($good_id, $params, "count");
			$count_good += $count;

			$additional_cost_arr = array();
			$additional_cost = '';
			if(! empty($_POST["additional_cost"]))
			{
				$a_cs = array();
				foreach($_POST["additional_cost"] as $a_c)
				{
					$a_c = $this->diafan->filter($a_c, "integer");
					if($a_c)
					{
						$a_cs[] = $a_c;
					}
				}
				if($a_cs)
				{
					$additional_cost_arr = DB::query_fetch_value("SELECT additional_cost_id FROM {shop_additional_cost_rel} WHERE element_id=%d AND trash='0' AND additional_cost_id IN (%s)", $good_id, implode(',', $a_cs), "additional_cost_id");
				}
			}
			
			$additional_cost_arr_2 = DB::query_fetch_value("SELECT r.additional_cost_id FROM {shop_additional_cost_rel} AS r INNER JOIN {shop_additional_cost} AS a ON a.id=r.additional_cost_id WHERE r.element_id=%d AND r.trash='0' AND a.required='1'", $good_id, "additional_cost_id");
			if($additional_cost_arr_2)
			{
				$additional_cost_arr = array_unique(array_merge($additional_cost_arr, $additional_cost_arr_2));
			}
	
			if($additional_cost_arr)
			{
				sort($additional_cost_arr);
				$additional_cost = implode(',', $additional_cost_arr);
			}
	
			$cart = array(
					"count" => $count_good,
					"is_file" => $row['is_file'],
				);
	
			if($err = $this->diafan->_cart->set($cart, $good_id, $params, $additional_cost))
			{
				$this->result["errors"][0] = $err;
				return;	
			}
			$this->diafan->_cart->write();
	
			DB::query("UPDATE {shop} SET counter_buy=counter_buy+1 WHERE id=%d", $good_id);
		}
		return $this->order();
	}

	/**
	 * Отправляет письма администратору сайта и пользователю, сделавшему заказ
	 *
	 * @param integer $order_id номер заказа
	 * @param array $params поля формы, заполняемой пользователем
	 * @param array $payment платежная система
	 * @return void
	 */
	private function send_mails($order_id, $params, $payment)
	{
		Custom::inc('includes/mail.php');

		$cart = $this->diafan->_tpl->get('table_mail', 'cart', $this->diafan->_shop->order_get($order_id));
		$payment_name = '';
		if($payment)
		{
			$payment_name = $payment["name"];
			if($payment["payment"] == 'non_cash')
			{
				$p = DB::query_fetch_array("SELECT code, id FROM {payment_history} WHERE module_name='cart' AND element_id=%d", $order_id);
				$payment_name .= ', <a href="'.BASE_PATH.'payment/get/non_cash/ul/'.$p["id"].'/'.$p["code"].'/">'.$this->diafan->_('Счет для юридических лиц', false).'</a>,
				<a href="'.BASE_PATH.'payment/get/non_cash/fl/'.$p["id"].'/'.$p["code"].'/">'.$this->diafan->_('Квитанция для физических лиц', false).'</a>';
			}
		}

		$user_email = $this->diafan->_users->mail;
		$user_phone = $this->diafan->_users->phone;
		$user_fio = $this->diafan->_users->fio;
		foreach ($params as $param)
		{
			if ($param["type"] == "email" && ! empty($_POST["p".$param["id"]]))
			{
				$user_email = $_POST["p".$param["id"]];
			}
			if ($param["info"] == "phone" && ! empty($_POST["p".$param["id"]]))
			{
				$user_phone = $_POST["p".$param["id"]];
			}
			if ($param["info"] == "name" && ! empty($_POST["p".$param["id"]]))
			{
				$user_fio = $_POST["p".$param["id"]];
			}
		}

		//send mail admin
		$subject = str_replace(array('%title', '%url', '%id', '%message'),
				   array(TITLE, BASE_URL, $order_id, $this->message_admin_param),
				   $this->diafan->configmodules('subject_admin', 'shop')
				  );

		$message = str_replace(
			array('%title',
				'%url',
				'%id',
				'%message',
				'%order',
				'%payment',
				'%fio'
			),
			array(
				TITLE,
				BASE_URL,
				$order_id,
				$this->message_admin_param,
				$cart,
				$payment_name,
				$user_fio
			),
			$this->diafan->configmodules('message_admin', 'shop'));

		send_mail(
				$this->diafan->configmodules("emailconfadmin", 'shop') ? $this->diafan->configmodules("email_admin", 'shop') : EMAIL_CONFIG,
				$subject,
				$message,
				$this->diafan->configmodules("emailconf", 'shop') ? $this->diafan->configmodules("email", 'shop') : ''
			);

		if(in_array("subscription", $this->diafan->installed_modules))
		{
			if(! empty($user_phone))
			{
				$user_phone = preg_replace('/[^0-9]+/', '', $user_phone);
				if(! DB::query_result("SELECT id FROM {subscription_phones} WHERE phone='%s' AND trash='0'", $user_phone))
				{
					DB::query("INSERT INTO {subscription_phones} (phone, name, created, act) VALUES ('%s', '%h', %d, '1')", $user_phone, $user_fio, time());
				}
			}
		}

		//send mail user
		if (empty($user_email))
		{
			return;
		}

		if(in_array("subscription", $this->diafan->installed_modules) && (! empty($_POST['subscribe_in_order']) || ! $this->diafan->configmodules('subscribe_in_order', 'subscription')))
		{
			$row_subscription = DB::query_fetch_array("SELECT * FROM {subscription_emails} WHERE mail='%s' AND trash='0' LIMIT 1", $user_email);
			
			if(empty($row_subscription))
			{
				$code = md5(rand(111, 99999));
				DB::query("INSERT INTO {subscription_emails} (created, mail, name, code, act) VALUES (%d, '%s', '%s', '%s', '1')", time(), $user_email, $user_fio, $code);
			}
			elseif(! $row_subscription["act"])
			{
				DB::query("UPDATE {subscription_emails} SET act='1', created=%d WHERE id=%d", $row_subscription['id'], time());
			}
		}
		
		$subject = str_replace(
				array('%title', '%url', '%id'),
				array(TITLE, BASE_URL, $order_id),
				$this->diafan->configmodules('subject', 'shop')
			);

		$message = str_replace(
				array('%title', '%url', '%id', '%message', '%order', '%payment', '%fio'),
				array(
					TITLE,
					BASE_URL,
					$order_id,
					$this->message_param,
					$cart,
					$payment_name,
					$user_fio
				),
				$this->diafan->configmodules('message', 'shop')
			);
		send_mail(
			$user_email,
			$subject,
			$message,
			$this->diafan->configmodules("emailconf", 'shop') ? $this->diafan->configmodules("email", 'shop') : ''
		);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 * 
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'shop'))
			return;
			
		$message = $this->diafan->configmodules("sms_message_admin", 'shop');

		$to   = $this->diafan->configmodules("sms_admin", 'shop');

		Custom::inc('includes/sms.php');
		Sms::send($message, $to);
	}
}