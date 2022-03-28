<?php
/**
 * Редактирование вопросов для капчи
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
 * Captcha_admin
 */
class Captcha_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'captcha';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Вопрос',  
				'help' => 'Вопрос, на который должен ответить посетитель сайта для того, чтобы пройти проверку.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечено, то вопрос не будет участвовать в проверке.',
				'default' => true,
				'multilang' => true,
			),
			'is_write' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать ответы',
				'help' => 'Если отмечено, то пользователю на сайте будет предложено поле для ввода правильного ответа.',
			),
			'answers' => array(
				'type' => 'function',
				'name' => 'Ответы',
				'help' => 'Возможные варианты ответов с указанием правильного.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Вопрос', 
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
		if($this->diafan->configmodules('type') == 'qa')
		{
			echo $this->diafan->addnew_init('Добавить вопрос');
		}
	}

	/**
	 * Выводит список вопросов
	 * @return void
	 */
	public function show()
	{
		echo '<div class="unit">'.$this->diafan->_('CAPTCHA – фильтр спам-ботов и реальных пользователей сайта, которые оставляют сообщения в формах различных модулей сайта, таких как «Обратная связь», «Комментарии», «Форум» и т.д. Капча может быть трех видов, классический «Код на картинке», более сложная вариация «reCAPTCHA», или «Вопрос-Ответ», когда администратор задает вопрос и варианты ответа, а пользователь должен выбрать правильный, чтобы его сообщение было принято на сайте.').'</div>';
		if($this->diafan->configmodules('type') != 'qa')
		{
			echo '<div class="error">'.sprintf($this->diafan->_('Настройке подлежит только капча «Вопрос-Ответ». Выберите в  %sнастройках%s модуля необходимый вид капчи для модулей.'), '<a href="'.BASE_PATH_HREF.'captcha/config/">', '</a>').'</div>';
		} else
		{
			$this->diafan->list_row();
		}
	}

	/**
	 * Редактирование поля "Ответы"
	 * @return void
	 */
	public function edit_variable_answers()
	{
		$answers = array();
		if(! $this->diafan->is_new)
		{
			$answers = DB::query_fetch_all("SELECT id, [text], is_right FROM {captcha_answers} WHERE captcha_id=%d", $this->diafan->id);
		}
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';
		foreach ($answers as $row)
		{
			echo '<div class="param">
				<input type="hidden" name="answer_id[]" value="'.$row["id"].'">
				<input type="text" name="answer_text[]" value="'.str_replace('"', '&quot;', $row["text"]).'">
				<input type="hidden" name="answer_is_right[]" value="'.$row["is_right"].'">
				<input type="radio" name="is_right" class="answer_is_right"'.($row["is_right"] ? ' checked' : '').' style="width: 10px!important">
				
				<span class="label_answer_is_right">'.$this->diafan->_('правильный ответ').'</span>&nbsp;&nbsp;&nbsp;
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
			</div>';
			
		}
		echo '<div class="param">
				<input type="hidden" name="answer_id[]" value="">
				<input type="text" name="answer_text[]" value="">
				<input type="hidden" name="answer_is_right[]" value="'.(empty($answers) ? 1 : '').'">
				<input type="radio" name="is_right" '.(empty($answers) ? ' checked' : '').' style="width: 10px!important">

				<span class="label_answer_is_right">'.$this->diafan->_('правильный ответ').'</span>&nbsp;&nbsp;&nbsp;

				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
			</div>
			<a href="javascript:void(0)" class="param_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>
		</div>';
	}

	/**
	 * Сохранение поля "Ответы"
	 * @return void
	 */
	public function save_variable_answers()
	{
		if(! empty($_POST["answer_text"]))
		{
			foreach ($_POST["answer_text"] as $i => $text)
			{
				if(empty($text))
					continue;

				if(! empty($_POST["is_write"]))
				{
					$text = utf::strtolower($text);
				}

				if(! empty($_POST["answer_id"][$i]))
				{
					DB::query("UPDATE {captcha_answers} SET [text]='%h', is_right='%d' WHERE id=%d AND captcha_id=%d", $text, $_POST["answer_is_right"][$i], $_POST["answer_id"][$i], $this->diafan->id);
					$id = intval($_POST["answer_id"][$i]);
					if($id)
					{
						$ids[] = $id;
					}
				}
				else
				{
					$ids[] = DB::query("INSERT INTO {captcha_answers} ([text], is_right, captcha_id) VALUES ('%h', '%d', %d)", $text, $_POST["answer_is_right"][$i], $this->diafan->id);
				}
			}
		}
		DB::query("DELETE FROM {captcha_answers} WHERE ".(! empty($ids) ? "id NOT IN(".implode(",", $ids).") AND" : "")." captcha_id=%d", $this->diafan->id);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("captcha_answers", "captcha_id IN (".implode(",", $del_ids).")");
	}
}