<?php
/**
 * Редактирование способов доставки
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

class Delivery_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_delivery';

	/**
	 * @var string тип элементов
	 */
	public $element_type = 'element';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название способа доставки, выводится на сайте в форме заказа.',
				'multilang' => true,
			),
			'thresholds' => array(
				'type' => 'floattext',
				'name' => 'Стоимость',
				'help' => 'Можно указать несколько порогов стоимости доставки в зависимости от суммы заказа. Например, «300 от суммы 0», «150 от суммы 2000», и «0 от суммы 5000».',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
				'multilang' => true,
			),
			'service' => array(
				'type' => 'function',
				'name' => 'Служба доставки',
				'help' => 'Ключ (для калькулятора, API, виджетов)',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Описание',
				'help' => 'Описание способа доставки, выводится в форме заказа.',
				'multilang' => true,
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего способа доставки среди других способов. В списке способов можно сортировать методы простым перетаскиванием мыши.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'sort' => array(
			'name' => 'Сортировка',
			'type' => 'numtext',
			'sql' => true,
			'fast_edit' => true,
		),
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить');
	}

	/**
	 * Выводит список способов доставки
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Стоимость"
	 * @return void
	 */
	public function edit_variable_thresholds()
	{
		$rows  = array();
		if(! $this->diafan->is_new)
		{
			$rows = DB::query_fetch_all("SELECT amount, price FROM {shop_delivery_thresholds} WHERE delivery_id=%d ORDER BY price DESC", $this->diafan->id);
		}
		if(! $rows)
		{
			$rows[] = array("amount" => 0, "price" => 0);
		}
		echo '
		<div class="unit" id="thresholds">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<table>';
			foreach ($rows as $row)
			{
				echo '<tr class="threshold"><td>
				<input type="number" name="price[]" value="'.$row["price"].'">
				'.$this->diafan->_('от суммы').' <input type="number" name="amount[]" value="'.$row["amount"].'">
				<span class="threshold_actions">
				<a href="javascript:void(0)" action="delete_threshold" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
				</td></tr>';
			}
			echo '</table>
			<a href="javascript:void(0)" class="threshold_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>';
			echo '
		</div>';
	}

	/**
	 * Сохранение поля "Стоимость"
	 * @return void
	 */
	public function save_variable_thresholds()
	{
		if(! $this->diafan->is_new)
		{
			DB::query("DELETE FROM {shop_delivery_thresholds} WHERE delivery_id=%d", $this->diafan->id);
		}

		if (! empty($_POST["price"]))
		{
			foreach ($_POST["price"] as $i => $price)
			{
				$amount = $_POST["amount"][$i];
				if($price || $amount)
				{
					DB::query("INSERT INTO {shop_delivery_thresholds} (delivery_id, price, amount) VALUES (%d, %f, %f)", $this->diafan->id, $price, $amount);
				}
			}
		}
	}
	
	
	/**
	 * Редактирование поля "Служба доставки"
	 * @return void
	 */
	public function edit_variable_service()
	{
		$rows = $this->get_rows();
		$values = array();
		if ( ! $this->diafan->is_new)
		{
			$values = unserialize(DB::query_result("SELECT params FROM {" . $this->diafan->table . "} WHERE id=%d LIMIT 1", $this->diafan->id));
		}
		echo '		
		<div class="unit">
			<div class="infofield">
				' . $this->diafan->variable_name() . '
			</div>
			<select name="service"><option value="">-</option>';
		foreach($rows as $row)
		{
			echo '<option value="'.$row["name"].'"'.($this->diafan->value == $row["name"] ? ' selected' : '').'>'.$this->diafan->_($row["config"]["name"]).'</option>';
		}
		echo '</select>
		</div>';

		foreach ($rows as $row)
		{
			foreach ($row["config"]["params"] as $key => $name)
			{
				if(is_array($name))
				{
					$type = (! empty($name["type"]) ? $name["type"] : 'text');
					$help = (! empty($name["help"]) ? $name["help"] : '');
					if($type == 'function')
					{
						$config_class = 'Delivery_'.$row["name"].'_admin';
						$class = new $config_class($this->diafan);
						if (is_callable(array(&$class, "edit_variable_".$key)))
						{
							call_user_func_array(array(&$class, "edit_variable_".$key), array((! empty($values[$key]) ? $values[$key] : '')));
							continue;
						}
					}
					$name = $name["name"];
				}
				else
				{
					$type = 'text';
					$help = '';
				}
				echo '<div class="unit tr_service " service="'.$row["name"].'" style="display:none">';
		
				switch($type)
				{
					case 'checkbox':
					echo '<input type="checkbox" value="1"'.(! empty($values[$key]) ? ' checked' : '').' name="'.$row["name"].'_'.$key.'" id="input_'.$row["name"].'_'.$key.'">
					<label for="input_'.$row["name"].'_'.$key.'"><b>'.$this->diafan->_($name).'</b>'.$this->diafan->help($help).'</label>';
					break;
		
					default:
					echo '<div class="infofield">'.$this->diafan->_($name).$this->diafan->help($help).'</div>
					<input type="text" value="'.(! empty($values[$key]) ? $values[$key] : '').'" name="'.$row["name"].'_'.$key.'">';
					
				}
				echo '</div>';
			}
		}
	}

	/**
	 * Сохранение поля "Служба доставки"
	 * @return void
	 */
	public function save_variable_service()
	{
		if (empty($_POST['service']))
		{
			$this->diafan->set_query("service='%s'");
			$this->diafan->set_value('');
			return;
		}
		
		$service = $this->diafan->filter($_POST, "string", "service");		
		if (! Custom::exists('modules/delivery/backend/'.$service.'/delivery.'.$service.'.admin.php'))
		{
			$this->diafan->set_query("service='%s'");
			$this->diafan->set_value('');
			return;
		}
		Custom::inc('modules/delivery/backend/'.$service.'/delivery.'.$service.'.admin.php');
		$config_class = 'Delivery_'.$service.'_admin';
		$class = new $config_class($this->diafan);

		$values = array();
		foreach ($class->config["params"] as $key => $name)
		{
			if(! empty($name["type"]) && $name["type"] == 'function')
			{
				if (is_callable(array(&$class, "save_variable_".$key)))
				{
					$value = call_user_func_array(array(&$class, "save_variable_".$key), array());
					if($value)
					{
						$values[$key] = $value;
					}
					continue;
				}
			}
			if ( ! empty($_POST[$service.'_'.$key]))
			{				
				$values[$key] = $this->diafan->filter($_POST, 'string', $service.'_'.$key);
			}
		}
		$this->diafan->set_query("service='%s'");
		$this->diafan->set_value($service);

		$this->diafan->set_query("params='%s'");
		$this->diafan->set_value(serialize($values));
	}

	/**
	 * Получает список всех платежных систем
	 *
	 * @return array
	 */
	private function get_rows()
	{
		$rows = array();
		$rs = Custom::read_dir("modules/delivery/backend");
		foreach($rs as $row)
		{
			if (Custom::exists('modules/delivery/backend/'.$row.'/delivery.'.$row.'.admin.php'))
			{
				Custom::inc('modules/delivery/backend/'.$row.'/delivery.'.$row.'.admin.php');
				$config_class = 'Delivery_'.$row.'_admin';
				$class = new $config_class($this->diafan);
				$rows[] = array("name" => $row, "config" => $class->config);
			}
		}
		return $rows;
	}
}