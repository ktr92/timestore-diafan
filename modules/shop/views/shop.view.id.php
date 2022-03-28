<?php
/**
 * Шаблон страницы товара
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
echo '<div class="js_shop_id js_shop shop shop_id shop-item-container">'; ?>
<? /* <pre><? print_r($result); ?> </pre> */ ?>
<div class="product-page">
	<div class="itemtop">
		<? echo $this->htmleditor('<insert name="show_breadcrumb" current="true">'); ?>
		
		<?
		//ссылки на предыдущий и последующий товар
		if(! empty($result["previous"]) || ! empty($result["next"]))
		{
			echo '<div class="linksrel">';
			if(! empty($result["previous"]))
			{
				echo '<a class="colored" href="'.BASE_PATH_HREF.$result["previous"]["link"].'"><i class="fa fa-angle-left" aria-hidden="true"> </i> Предыдущие</a>&nbsp;&nbsp;';
			}
			if(! empty($result["next"]))
			{
				echo '<a class="colored" href="'.BASE_PATH_HREF.$result["next"]["link"].'"> Следующие <i class="fa fa-angle-right" aria-hidden="true"> </i></a>';
			}
			echo '</div>';
		}
		?>				
	</div>
	<div class="clearfix"></div>


<div class="product">
	<div class="row">
		<div class="col-md-5 col-sm-5 col-xs-12">
		<? echo '<div class="shop-item-left">'; ?>
			<div class="product-images">
				
				<?

//вывод изображений товара
if(! empty($result["img"]))
{
	echo '<div class="js_shop_all_img shop_all_img shop-item-big-images">';
	$k = 0;
	foreach($result["img"] as $img)
	{
		switch ($img["type"])
		{
			case 'animation':
				echo '<a class="js_shop_img shop-item-image'.(empty($k) ? ' active' : '').'" href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'shop" image_id="'.$img["id"].'">';
				break;
			case 'large_image':
				echo '<a class="js_shop_img shop-item-image'.(empty($k) ? ' active' : '').'" href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'" image_id="'.$img["id"].'">';
				break;
			default:
				echo '<a class="js_shop_img shop-item-image'.(empty($k) ? ' active' : '').'" href="'.BASE_PATH.$img["link"].'" image_id="'.$img["id"].'">';
				break;
		}
		echo '<img src="'.BASE_PATH.$img["link"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" image_id="'.$img["id"].'" class="shop_id_img">';
		echo '</a>';
		$k++;
	}
	echo '<span class="shop-photo-labels">';
		if (!empty($result['hit']))
		{
			echo '<img src="'.BASE_PATH.Custom::path('img/label_hot_big.png').'">';
		}
		if (!empty($result['action']))
		{
			echo '<img src="'.BASE_PATH.Custom::path('img/label_special_big.png').'">';
		}
		if (!empty($result['new']))
		{
			echo '<img src="'.BASE_PATH.Custom::path('img/label_new_big.png').'">';
		}
	echo '</span>';

	echo '<span class="icon-zoom">&nbsp;</span>
          <span class="js_shop_wishlist shop_wishlist shop-like'.(! empty($result["wish"]) ? ' active' : '').'"><i class="fa fa-heart'.(! empty($result["wish"]) ? '' : '-o').'">&nbsp;</i></span>';

	echo '</div>';
	if($result["preview_images"])
	{
		echo '<a class="control-prev shop-previews-control" href="#"><i class="fa fa-toggle-left"></i></a>
	          <a class="control-next shop-previews-control" href="#"><i class="fa fa-toggle-right"></i></a>';
		echo '<div class="shop_preview_img shop-item-previews items-scroller" data-item-per-screen="3" data-controls="shop-previews-control">';
		foreach($result["img"] as $img)
		{
			echo ' <a class="js_shop_preview_img item" href="#" style="background-image:url('.$img["preview"].')" image_id="'.$img["id"].'">&nbsp;</a>';
		}
		echo '</div>';
	}
} ?>

	</div>
</div>
	<!--<a href="#" class="colored zoom">Кликни, чтобы повернуть изображение</a>-->


<? echo '</div>'; ?> 

<?
echo '<div class="col-md-4 col-sm-4 col-xs-12">';
echo '<div class="shop-item-right">';
	echo '<div class="shop-item-info1">
		<div class="product-actions">
		<div class="product-buy">
			<div class="price-cart">
				<div class="product-price">';

		//вывод артикула
		/*if(! empty($result["article"]))
		{
			echo '<h4 class="shop-item-artikul">'.$this->diafan->_('Артикул').': '.$result["article"].'</h4>';
		}*/

		//вывод производителя
		/*if (! empty($result["brand"]))
		{
			echo '<div class="shop_brand">';
			echo $this->diafan->_('Производитель').': ';
			echo '<a href="'.BASE_PATH_HREF.$result["brand"]["link"].'">'.$result["brand"]["name"].'</a>';
			if (! empty($result["brand"]["img"]))
			{
				echo ' ';
				foreach ($result["brand"]["img"] as $img)
				{
					switch ($img["type"])
					{
						case 'animation':
							echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'shop_brand">';
							break;
						case 'large_image':
							echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
							break;
						default:
							echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
							break;
					}
					echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
					. '</a> ';
				}
			}
			echo '</div>';
		}*/

		//вывод рейтинга товара
		/*if(! empty($result["rating"]))
		{
			echo '<div class="shop-item-rate rate">'.$this->diafan->_('Рейтинг').": ";
			echo $result["rating"];
			echo '</div>';
		}*/

		//скидка на товар
		if(! empty($result["discount"]))
		{
			echo '<span class="shop_discount">'.$this->diafan->_('Скидка').': <span class="shop_discount_value">'.$result["discount"].' '.$result["discount_currency"].($result["discount_finish"] ? ' ('.$this->diafan->_('до').' '.$result["discount_finish"].')' : '').'</span></span>';
		}

		//кнопка "Купить"
		echo $this->get('buy_form', 'shop', array("row" => $result, "result" => $result));

		 echo '</div>'; echo '</div>'; echo '</div>';
		echo '<div class="addlinks">';
		
		if(empty($result["hide_compare"]))
		{
			echo '<div class="comparison">';
		    echo $this->get('compare_form', 'shop', $result);
		    //echo $this->get('compared_goods_list', 'shop', array("site_id" => $result["site_id"], "shop_link" => $result['shop_link']));
			 echo '</div>';
		}
		
		
		echo '
		<div class="wishlist">
		
          <span class="js_shop_wishlist shop_wishlist shop-like'.(! empty($result["wish"]) ? ' active' : '').'">
		  <img src="/images/star.png" alt="">
		  &nbsp;<span class="colored">'.(! empty($result["wish"]) ? 'Добавить в избранные' : 'Добавить в избранные').'</span></span></div>';
		 
		 
		 	/*echo '
			<span class="js_shop_wishlist shop_wishlist shop-like'.(! empty($result["wish"]) ? ' active' : '').'">
			<i class="fa fa-star'.(! empty($result["wish"]) ? '' : '-o').'">&nbsp;</i></span>';*/
		  
		 echo '</div>';
		
		/*if(! empty($result["weight"]))
		{
			echo '<div class="shop_weight">'.$this->diafan->_('Вес').': '.$result["weight"].'</div>';
		}
		if(! empty($result["length"]))
		{
			echo '<div class="length">'.$this->diafan->_('Длина').': '.$result["length"].'</div>';
		}
		if(! empty($result["width"]))
		{
			echo '<div class="shop_width">'.$this->diafan->_('Ширина').': '.$result["width"].'</div>';
		}
		if(! empty($result["height"]))
		{
			echo '<div class="shop_height">'.$this->diafan->_('Высота').': '.$result["height"].'</div>';
		}*/
		
		?>
		<div class="shareblock">
			<div class="condensed-medium-black">Понравился товар? Расскажи друзьям:</div>
			<div class="share-socials">				
				<!--<span><img src="/images/share-1.png" alt=""></span>
				<span><img src="/images/share-2.png" alt=""></span>
				<span><img src="/images/share-3.png" alt=""></span>
				<span><img src="/images/share-4.png" alt=""></span>-->
			<?	echo $this->htmleditor('<insert name="show_social_links">');       ?>              

			</div>
		</div>
		
		<?

	echo '</div>';
	 echo '</div>';echo '</div>';echo '</div>';
	 
	 
	 echo '<div class="col-md-3 col-sm-3 hidden-xs">
							<div class="infoblock-right">
								<div class="info">
									<div class="info-title">
										<img src="/images/target.png" alt="">
										<span class="condensed-medium-black">Способы доставки</span>	
									</div>
									<div class="info-items">
										<ul>
											<li>Самовывоз</li>
											<li>Доставка курьером</li>
											<li>Новая почта</li>
											<li>Другие курьерские службы</li>
										</ul>
									</div>
								</div>
								<div class="info">
									<div class="info-title">
										<img src="/images/wallet.png" alt="">									
										<span class="condensed-medium-black">Способы оплаты</span>
									</div>
									<div class="info-items">
										<ul>
											<li>Наличными курьеру</li>
											<li>На карту Приватбанк</li>
											<li>QIWI Терминал</li>
											<li>Webmoney</li>
										</ul>
									</div>								
								</div>
								<div class="shipping">
									<img src="/images/location.png" alt="">
									<div>
										<span class="condensed-big">Бесплатная доставка</span><br/>
										<span class="condensed-small-gray">по России при покупке от 5 000 Р</span>
									</div>
								</div>
							</div>
						</div>';

	/*echo '<div class="shop-item-info2">
		<div class="shop-item-info2">
			<div class="block">
				<h4><i class="fa fa-truck"></i>'.$this->diafan->_('Условия доставки').'</h4>
				'.$this->htmleditor('<insert name="show_block" module="site" id="3" defer="emergence">').'
				</div>
			<div class="block">
				<h4><i class="fa fa-refresh"></i>'.$this->diafan->_('Условия возврата').'</h4>
				'.$this->htmleditor('<insert name="show_block" module="site" id="4" defer="emergence">').'
			</div>
		</div>
	</div>';*/

  	echo $this->htmleditor('<insert name="show_block_order_rel" module="shop" count="2" images="1" defer="emergence" defer_title="C этим товаром покупают">');  	

echo '</div>';

//счетчик просмотров
if(! empty($result["counter"]))
{
	echo '<div class="shop_counter">'.$this->diafan->_('Просмотров').': '.$result["counter"].'</div>';
}

//теги товара
if (!empty($result["tags"]))
{
	echo $result["tags"];
}

?>

<div class="product-info">
					<div class="tabs">
						  <ul class="tabs__caption">
							<li class="active">Основное</li>
							<li>Характеристики</li>
							<li>Отзывы</li>
						  </ul>

						  <div class="tabs__content active">
							 <article>
							 	<header>
							 		<h1><? echo $result["name"]; ?></h1>
							 		<p class="minidescr">
									<? foreach ($result['all_param'] as $param) { 
										if ($param['id']==8) {
											echo $param['value'];
										}
									}?></p>
							 	</header>
								<div class="description">
									<? echo '<div class="shop_text">'.$this->htmleditor($result['text']).'</div>'; ?>
								</div>
								<div class="product-params">
									<? //характеристики товара
									if (!empty($result["param"]))
									{
										echo $this->get('param_table', 'shop', array("rows" => $result["param"], "id" => $result["id"]));
									} 
?>
								</div>
							 </article>
						  </div>

						  <div class="tabs__content">
							 <div class="product-params">
									<? //характеристики товара
									if (!empty($result["param"]))
									{
										echo $this->get('param_table', 'shop', array("rows" => $result["param"], "id" => $result["id"]));
									} 
									?>
								</div>
						  </div>
						  
						  <div class="tabs__content">
							<?
							
							//комментарии к товару
							if (!empty($result["comments"]))
							{
								echo $result["comments"];
							}

							echo '</div>';
							?>
						  </div>
					</div><!-- .tabs-->

				</div>
				
			</div>	

<?







//ссылки на предыдущий и последующий товар
/*if(! empty($result["previous"]) || ! empty($result["next"]))
{
	echo '<div class="previous_next_links">';
	if(! empty($result["previous"]))
	{
		echo '<div class="previous_link"><a href="'.BASE_PATH_HREF.$result["previous"]["link"].'">&larr; '.$result["previous"]["text"].'</a></div>';
	}
	if(! empty($result["next"]))
	{
		echo '<div class="next_link"><a href="'.BASE_PATH_HREF.$result["next"]["link"].'">'.$result["next"]["text"].' &rarr;</a></div>';
	}
	echo '</div>';
}*/
echo $this->htmleditor('<insert name="show_block_rel" module="shop" count="4" images="1" defer="emergence" defer_title="Похожие товары">');