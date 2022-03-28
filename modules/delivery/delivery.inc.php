<?php
/**
 * Подключение модуля «Доставка»
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

class Delivery_inc extends Diafan
{
	/**
	 * Подключает способ доставки
	 * 
	 * @return string
	 */
	public function get(&$row)
	{
		$result = $this->diafan->_delivery->get_info();
		$result["id"] = $row["id"];
		$result["params"] = unserialize($row["params"]);

		ob_start();

		$js = Custom::path('modules/delivery/backend/'.$row["service"].'/delivery.'.$row["service"].'.js');
		if($js)
		{
			$this->diafan->_site->js_view[] = $js;
		}

		include_once(Custom::path('modules/delivery/backend/'.$row["service"].'/delivery.'.$row["service"].'.model.php'));
		$name_class_model = 'Delivery_'.$row["service"].'_model';
		$model = new $name_class_model($this->diafan);
		if (! is_subclass_of($model, 'Delivery_model_interface'))
		{
			throw new Exception($name_class_model.' должен реализовать интерфейс Delivery_model_interface.');
		}
		$model->get($result);
		$row["price"] = $model->calculate($result["params"]);
		$row["service_info"] = $model->get_info($result["params"]);

		include(Custom::path('modules/delivery/backend/'.$row["service"].'/delivery.'.$row["service"].'.view.php'));
		$text = ob_get_contents();
		ob_end_clean();

		return $text;
	}

	/**
	 * Получает информацию о заказе: высоту, ширину, длину, вес, сумму
	 * 
	 * @return array
	 */
	private function get_info()
	{
		if(isset($this->cache["info"]))
		{
			return $this->cache["info"];
		}
		$weight = 0;
		$owidth = 0;
		$olength = 0;
		$oheight = 0;
		$summ = $this->diafan->_cart->get_summ();
		$cart = $this->diafan->_cart->get();
		if ($cart)
		{
			$goods = DB::query_fetch_key("SELECT id, [name], width, weight, length, height, [measure_unit] FROM {shop} WHERE [act]='1' AND id = %d AND trash='0' LIMIT 1", implode(',', array_keys($cart)), "id");
			foreach ($cart as $good_id => $array)
			{
				if (empty($goods[$good_id]))
				{
					continue;
				}
				$row = $goods[$good_id];
				if (empty($row["weight"]))
				{
					$row["weight"] = 0;
				}
				if (empty($row["width"]))
				{
					$row["width"] = 0;
				}
				if (empty($row["length"]))
				{
					$row["length"] = 0;
				}
				if (empty($row["height"]))
				{
					$row["height"] = 0;
				}
				foreach ($array as $param => $ar)
				{
					foreach ($ar as $id => $value)
					{
						for ($i = 0; $i < $value['count']; $i++)
						{
							$weight += $row["weight"];
							if (min($olength, $owidth, $oheight) == $olength)
							{
								if ($row["width"] > $owidth)
								{
									$owidth = $row["width"];
								}
								if ($row["height"] > $oheight)
								{
									$oheight = $row["height"];
								}
								$olength += $row["length"];
							}
							elseif (min($olength, $owidth, $oheight) == $owidth)
							{
								if($row["length"] > $olength)
								{
									$olength = $row["length"];
								}
								if($row["height"] > $oheight)
								{
									$oheight = $row["height"];
								}
								$owidth += $row["width"];
							}
							elseif (min($olength, $owidth, $oheight) == $oheight)
							{
								if($row["width"] > $owidth)
								{
									$owidth = $row["width"];
								}
								if($row["length"] > $olength)
								{
									$olength = $row["length"];
								}
								$oheight += $row["height"];
							}
						}
					}
				}
			}
			$result['weight'] = $weight;
			$result['width'] = $owidth;
			$result['length'] = $olength;
			$result['height'] = $oheight;
			$result['summ'] = $summ;
		}
		$this->cache["info"] = $result;
		return $result;
	}
}

/**
 * Delivery_model_interface
 * 
 * Интерфейс модели модуля службы доставки
 */
interface Delivery_model_interface
{
	/*
	 * Подключает способ доставки. Данные о заказе и способе доставки, переданные аргументом могут быть дополнены и использованы в дальнейшем в шаблоне бэкенда
	 *
	 * @param array $result данные о заказе (высота, ширина, длина, сумма, вес), идентификационный номер способа доставки ("id"), настройки способа доставки (array "params")
	 * @return array
	 */
	public function get(&$result);

	/*
	 * Получает стоимость доставки
	 *
	 * @param array $params настройки способа доставки
	 * @return mixed
	 */
	public function calculate($params);

	/*
	 * Получает данные, введенные пользователем в интерфейсе службы доставки
	 *
	 * @param array $params настройки способа доставки
	 * @return mixed
	 */
	public function get_info($params);
}