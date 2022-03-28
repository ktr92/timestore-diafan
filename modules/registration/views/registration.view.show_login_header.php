<?php
/**
 * Шаблон блока авторизации
 *
 * Шаблонный тег <insert name="show_login" module="registration" [template="шаблон"]>:
 * блок авторизации
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

if (! $result["user"])
{
	?>
	<div class="account">
		<a href="/login/" class="signin">Вход</a>
		<a href="/registration/" class="signup">Регистрация</a>
	</div>
	<?
}
else
{
	?>
	<div class="account" style="margin-top: 5px; text-align: right;">
			<a href="/login/" class="signin">Профиль</a>

		<?	/*echo '<a class="signin" href="'.$result['userpage'].'">'.$this->diafan->_('Личный кабинет').'</a>'; */?>
		<?/*	echo '<a href="'.BASE_PATH_HREF.'logout/?'.rand(0, 99999).'" class="button">'.$this->diafan->_('Выйти', false).'</a>'; */?>

	</div>
	<?	
}