<?php
/**
 * Шаблонный тег: формирует часть HTML-шапки сайта. Включает в себя в том числе теги: show_title, show_description, show_keywords.
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

?>
	<footer id="footer">
		<div class="container">
			<div class="row">
				<div class="col-md-3 col-sm-3 col-xs-12">
					<div class="logo-footer">
						<img src="/images/logo-footer.png">
						<div class="contacts"><span class="caption-footer">Адрес:</span><span class="footer-contacts">Москва, ул. Строительная, 32</span></div>
						<div class="contacts">
							<span class="caption-footer">Телефон:</span><span class="footer-contacts">+7 (800) 430 80 91<br/>+7 (499) 332 42 72</span>
						</div>
						<div class="contacts"><span class="caption-footer">E-mail:</span><span class="footer-contacts">info@timestore.ru</span></div>
						<div class="contacts"><span class="caption-footer">Skype:</span><span class="footer-contacts">time_store</span></div>
					</div>
				</div>
				<div class="col-md-3 col-sm-3 col-xs-12">
					<div class="footer-menu">
						<h3>Информация</h3>
							<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_block" module="menu" id="3" template="topline">'); ?>
					</div>
				</div>
				<div class="col-md-3 col-sm-3 col-xs-12">
					<div class="footer-menu">
						<h3>Продукция</h3>
						<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_block" module="menu" id="4" template="topline">'); ?>
					</div>
				</div>
				<div class="col-md-3 col-sm-3 col-xs-12">
					<div class="footer-menu">
						<h3>Пользователь</h3>
						<ul>
							<li>	<a href="/login/">Вход</a>	</li>
							<li>	<a href="/registration/">Регистрация</a>	</li>
							<li>	<a href="#" data-toggle="modal" data-target="#myModal_zakazat_zvonok">Обратный звонок</a>	</li>
						</ul>
					</div>
				</div>
			</div>	
			<div class="social-links">
				<a href=""><img src="/images/socials-1.png" alt=""></a>
				<a href=""><img src="/images/socials-2.png" alt=""></a>
				<a href=""><img src="/images/socials-3.png" alt=""></a>
			</div>
		</div>
	</footer> 
  
					
 
  
  <!-- Модальные окно - Заказать звонок -->
  <div id="myModal_zakazat_zvonok" class="modal fade">
  <div class="modal-dialog" id="modal_zvonok_size">
    <div class="modal-content">
      <!-- Заголовок модального окна -->
		<div class="modal-header">
			<div class="modal-title">
				<button type="button" class="close" id="modal_zvonok_close" data-dismiss="modal" aria-hidden="true"><img src="/images/close.png"></button>
				<!--<header>Мы перезвоним Вам</header>
				<p>и ответим на все интересующие вопросы</p>-->
            </div>
		</div>
      <!-- Основное содержимое модального окна -->
      <div class="modal-body" id="modal_zvonok_container">
        <div class="modal_zvonok_content">          
          <div class="modal_zvonok_form_content">
				<?=$this->diafan->_parser_theme->get_function_in_theme('<insert name="show_form" module="feedback">');?>		
          </div>          
        </div>
      </div>
    </div>
  </div>
  </div>