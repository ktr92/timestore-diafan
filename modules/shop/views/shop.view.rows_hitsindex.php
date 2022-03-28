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
?>



<? foreach ($result['rows'] as $row)
{	?>
	<div class="col-md-3">
		<div class="product-hit">
			<div class="hits-image"><? echo '<img src="'.$row["img"][0]["src"].'">'; ?></div>
			<div class="name-hit"><a href="<?=$row["link"]?>"><? echo $row["name"]; ?></a></div>
			<div class="params-hit"><? echo $row["anons"]; ?></div>
			<div class="price-hit">
			<? 	echo $this->get('buy_form_catalog', 'shop', array("row" => $row, "result" => $result)); ?>
			</div>
			<span class="mark-hit">Хит продаж</span>	</div>					
	</div>

<? } ?>
	


