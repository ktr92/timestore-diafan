<?php
/**
 * Редактирование методов оплаты
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
 * Shop_admin_payment
 */
class Payment_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'payment';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название метода оплаты, выводится на сайте.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
				'multilang' => true,
			),
			'payment' => array(
				'type' => 'function',
				'name' => 'Платежная система',
				'help' => 'Система безналичной оплаты заказа. Если платежная система не задана, при оформлении заказа сразу перекидывает на страницу завершения заказа. Параметры подключения выдаются платежными системами при одобрении Вашего магазина.',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Описание',
				'help' => 'Описание метода оплаты, выводится на сайте в форме заказа.',
				'multilang' => true,
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего метода оплаты среди других методов. В списке методов можно сортировать методы простым перетаскиванием мыши.',
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
	 * Выводит список методов оплаты
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Платежная система"
	 * @return void
	 */
	public function edit_variable_payment()
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
			<select name="payment"><option value="">-</option>';
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
				$select = array();
				if(is_array($name))
				{
					$type = (! empty($name["type"]) ? $name["type"] : 'text');
					$help = (! empty($name["help"]) ? $name["help"] : '');
					$select = (! empty($name["select"]) ? $name["select"] : array());
					if($type == 'function')
					{
						$config_class = 'Payment_'.$row["name"].'_admin';
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
				echo '<div class="unit tr_payment" payment="'.$row["name"].'" style="display:none">';
		
				switch($type)
				{
					case 'checkbox':
					echo '<input type="checkbox" value="1"'.(! empty($values[$key]) ? ' checked' : '').' name="'.$row["name"].'_'.$key.'" id="input_'.$row["name"].'_'.$key.'">
					<label for="input_'.$row["name"].'_'.$key.'"><b>'.$this->diafan->_($name).'</b>'.$this->diafan->help($help).'</label>';
					break;

					case 'select':
					echo '<b>'.$this->diafan->_($name).'</b>'.$this->diafan->help($help).'
					<select name="'.$row["name"].'_'.$key.'">';
					foreach($select as $k => $v)
					{
						echo '<option value="'.$k.'"'.(! empty($values[$key])  && $values[$key] == $k ? ' selected' : '').'>'.$v.'</option>';
					}
					echo '</select>';
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
	 * Сохранение поля "Платежная система"
	 * @return void
	 */
	public function save_variable_payment()
	{
		if (empty($_POST['payment']))
		{
			$this->diafan->set_query("payment='%s'");
			$this->diafan->set_value('');
			return;
		}
		
		$payment = $this->diafan->filter($_POST, "string", "payment");		
		if (! Custom::exists('modules/payment/backend/'.$payment.'/payment.'.$payment.'.admin.php'))
		{
			$this->diafan->set_query("payment='%s'");
			$this->diafan->set_value('');
			return;
		}
		Custom::inc('modules/payment/backend/'.$payment.'/payment.'.$payment.'.admin.php');
		$config_class = 'Payment_'.$payment.'_admin';
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
			if ( ! empty($_POST[$payment.'_'.$key]))
			{				
				$values[$key] = $this->diafan->filter($_POST, 'string', $payment.'_'.$key);
			}
		}
		$this->diafan->set_query("payment='%s'");
		$this->diafan->set_value($payment);

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
		$rs = Custom::read_dir("modules/payment/backend");
		foreach($rs as $row)
		{
			if (Custom::exists('modules/payment/backend/'.$row.'/payment.'.$row.'.admin.php'))
			{
				Custom::inc('modules/payment/backend/'.$row.'/payment.'.$row.'.admin.php');
				$config_class = 'Payment_'.$row.'_admin';
				$class = new $config_class($this->diafan);
				$rows[] = array("name" => $row, "config" => $class->config);
			}
		}
		return $rows;
	}
}
