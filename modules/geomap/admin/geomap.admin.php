<?php
/**
 * Настройки модуля
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
 * Geomap_admin
 */
class Geomap_admin extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'backend' => array(
				'type' => 'function',
				'name' => 'Бэкенд',
				'help' => 'Тип карты.',
			),
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'config', // файл настроек модуля
	);

	/**
	 * Редактирование поля "Бэкенд"
	 * @return void
	 */
	public function edit_config_variable_backend()
	{
		$rows = $this->get_rows();
		$values = array();
		$config = $this->diafan->values('config');
		if($config)
		{
			$values = unserialize($config);
		}
		echo '		
		<div class="unit">
			<div class="infofield">
				'.$this->diafan->variable_name().'
				'.$this->diafan->help().'
			</div>
			<select name="backend"><option value="">-</option>';
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
						$config_class = 'Geomap_'.$row["name"].'_admin';
						$class = new $config_class($this->diafan);
						if (is_callable(array(&$class, "edit_variable_".$key)))
						{
							call_user_func_array(array(&$class, "edit_variable_".$key), array((! empty($values[$key]) ? $values[$key] : ''), $values));
							continue;
						}
					}
					$name = (! empty($name["name"]) ? $name["name"] : '');
				}
				else
				{
					$type = 'text';
					$help = '';
				}
				echo '<div class="unit tr_geomap" backend="'.$row["name"].'" style="display:none">
					';
		
				switch($type)
				{
					case 'checkbox':
					echo '<input type="checkbox" value="1"'.(! empty($values[$key]) ? ' checked' : '').' name="'.$row["name"].'_'.$key.'" id="input_'.$row["name"].'_'.$key.'"><label for="input_'.$row["name"].'_'.$key.'">'.$this->diafan->_($name).$this->diafan->help($help).'</label> ';
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
	 * Сохранение поля "Бэкенд"
	 * @return void
	 */
	public function save_config_variable_backend()
	{
		if ( empty($_POST['backend']))
			return;
		
		$backend = $this->diafan->filter($_POST, "string", "backend");		
		if (! Custom::exists('modules/geomap/backend/'.$backend.'/geomap.'.$backend.'.admin.php'))
		{
			return;
		}
		Custom::inc('modules/geomap/backend/'.$backend.'/geomap.'.$backend.'.admin.php');
		$config_class = 'Geomap_'.$backend.'_admin';
		$class = new $config_class($this->diafan);

		$values = array();
		foreach ($class->config["params"] as $key => $name)
		{
			if(! empty($name["type"]) && $name["type"] == 'function')
			{
				if (is_callable(array(&$class, "save_variable_".$key)))
				{
					$values[$key] = call_user_func_array(array(&$class, "save_variable_".$key), array());
					continue;
				}
			}
			if ( ! empty($_POST[$backend.'_'.$key]))
			{				
				$values[$key] = $this->diafan->filter($_POST, 'string', $backend.'_'.$key);
			}
		}
		$this->diafan->set_query("backend='%s'");
		$this->diafan->set_value($backend);

		$this->diafan->set_query("config='%s'");
		$this->diafan->set_value(serialize($values));
	}

	/**
	 * Получает список всех платежных бэкендов
	 *
	 * @return array
	 */
	private function get_rows()
	{
		$rows = array();
		$rs = Custom::read_dir("modules/geomap/backend");
		foreach($rs as $row)
		{
			if (Custom::exists('modules/geomap/backend/'.$row.'/geomap.'.$row.'.admin.php'))
			{
				Custom::inc('modules/geomap/backend/'.$row.'/geomap.'.$row.'.admin.php');
				$config_class = 'Geomap_'.$row.'_admin';
				$class = new $config_class($this->diafan);
				$rows[] = array("name" => $row, "config" => $class->config);
			}
		}
		return $rows;
	}
}