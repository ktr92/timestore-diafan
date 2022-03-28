<?php
/**
 * Брошенные корзины
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
 * Shop_admin_cart
 */
class Shop_admin_cart extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'users';

	/**
	 * @var string часть SQL-запроса - соединение с таблицей
	 */
	public $join = " INNER JOIN {shop_cart} AS c ON e.id=c.user_id";

	/**
	 * @var string SQL-условия для списка
	 */
	public $where = " AND e.trash='0' AND e.act='1'";

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'goods' => array(
			'name' => 'Товары',
		),
		'fio' => array(
			'name' => 'Пользователь',
			'sql' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
	);

	/**
	 * @var array дополнительные групповые операции
	 */
	public $group_action = array(
		"group_abandonmented_cart_mail" => array(
			'name' => "Отправить письмо",
			'module' => 'shop'
		),
	);

	/**
	 * Выводит список корзин
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит товары в заказе
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_goods($row, $var)
	{
		if(! isset($this->cache["prepare"]["cart"]))
		{
			$cart = DB::query_fetch_all(
				"SELECT * FROM {shop_cart} WHERE user_id IN (%s)",
				implode(",", $this->diafan->rows_id),
				"user_id"
			);
			$good_ids = array();
			$param_ids = array();
			$param_values = array();
			$this->cache["prepare"]["cart"] = array();
			foreach($cart as $c)
			{
				$this->cache["prepare"]["cart"][$c["user_id"]][] = $c;
				if(! in_array($c["good_id"], $good_ids))
				{
					$good_ids[] = $c["good_id"];
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
				$this->cache["prepare"]["goods"] = DB::query_fetch_key("SELECT id, [name], article FROM {shop} WHERE id IN (%s)", implode(",", $good_ids), "id");
			}
			if ($param_ids)
			{
				$this->cache["prepare"]["param_names"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param} WHERE id IN (%s)", implode(",", $param_ids), "id", "name");
			}
			if ($param_values)
			{
				$this->cache["prepare"]["select_names"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE id IN (%s)", implode(",", $param_values), "id", "name");
			}
		}
		if(empty($this->cache["prepare"]["cart"][$row["id"]]))
		{
			return '';
		}
		$text = '<div>';
		foreach($this->cache["prepare"]["cart"][$row["id"]] as $i => $c)
		{
			if(empty($this->cache["prepare"]["goods"][$c["good_id"]]))
			{
				continue;
			}
			$good = $this->cache["prepare"]["goods"][$c["good_id"]];
			if($i)
			{
				$text .= '<br>';
			}
			$text .= date("d.m.Y H:i", $c["created"]).' <a href="'.BASE_PATH_HREF.'shop/edit'.$c["good_id"].'/">'.$good["name"].($good["article"] ? " ".$good["article"] : '');

			$params = unserialize($c["param"]);
			foreach ($params as $id => $value)
			{
				if(! empty($this->cache["prepare"]["param_names"][$id]) && ! empty($this->cache["prepare"]["select_names"][$value]))
				{
					$text .= ', '.$this->cache["prepare"]["param_names"][$id].': '.$this->cache["prepare"]["select_names"][$value];
				}
			}
			$text .= '</a>';
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит пользователя
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_fio($row, $var)
	{
		return '<div class="sum"><a href="'.BASE_PATH_HREF.'users/edit'.$row["id"].'/">'.$row["fio"].'</a></div>';
	}
}