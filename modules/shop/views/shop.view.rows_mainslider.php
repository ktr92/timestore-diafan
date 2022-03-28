<?php
/**
 * Шаблон элементов в списке товаров
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

if(empty($result['rows'])) return false;
foreach ($result['rows'] as $row)
{		
/*?>
<pre><? print_r($row); ?> </pre> <? */ ?>
<div class="slide-item">
	<div class="container">
		<div class="row">
			<div class="col-md-3 col-md-offset-1 col-sm-3 col-xs-12">
				<? echo '<img src="'.$row["img"][0]["src"].'">'; ?>
			</div>
			<div class="col-md-8 col-sm-9 col-xs-12">
				<div class="slide-info">
					<div class="slide-name"><a href="<?=$row["link"]?>"><? echo $row["name"]; ?></a></div>
					<div class="slide-descr"><? foreach ($row['all_param'] as $param) { 
							if ($param['id']==8) {
								echo $param['value'];
							}
						}?></div>
						<div class="slide-anons"><? foreach ($row['all_param'] as $param) { 
							if ($param['id']==11) {
								echo $param['value'];
							}
						}?></div>

						<?
						echo $this->get('buy_form', 'shop', array("row" => $row, "result" => $result));
						
									?>
					<!-- <div class="slide-price">35 790 <span class="rouble">р</span></div>
					<div class="slide-form">
						<button class="btn-slide-orange"><img src="/images/cart-btn.png"> В корзину</button>
						<button class="btn-slide">Больше деталей <i class="fa fa-long-arrow-right" aria-hidden="true"></i></button>
					</div> -->
					<a class="btn-slide" href="<?=$row["link"]?>">Больше деталей <i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>

				</div>
			</div>
		</div>
	</div>
</div>
<? } ?>
