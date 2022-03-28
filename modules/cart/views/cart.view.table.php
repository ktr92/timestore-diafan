<?php
/**
 * Шаблон таблицы с товарами в корзине
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN')) {
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}
/*?>
 <pre><? print_r($result); ?> </pre> <?*/

//шапка таблицы
echo '<table class="cart">
	<thead><tr>
		<th class="cart_img col-md-1">Миниатюра</th>
		<th class="cart_name col-md-3">'.$this->diafan->_('Описание').'</th>';
		if(! empty($result["measure_unit"]))
		{
			echo '<th class="cart_measure_unit">'.$this->diafan->_('Единица измерения').'</th>';
		}
		echo '
		<th class="cart_price col-md-2">'.$this->diafan->_('Цена').'</th>
		<th class="cart_count col-md-2">'.$this->diafan->_('Количество').'</th>';
		if($result["discount"])
		{
			echo '<th class="cart_old_price">'.$this->diafan->_('Цена со скидкой').', '.$result["currency"].'</th>';
			echo '<th class="cart_discount">'.$this->diafan->_('Скидка').'</th>';
		}
		echo '<th class="cart_summ col-md-2">'.$this->diafan->_('Сумма').'</th>';
		if(empty($result["hide_form"]))
		{
			echo '<th class="cart_remove col-md-2">'.$this->diafan->_('Удалить').'</th>';
		}
		echo '
	</tr></thead><tbody>';
//товары
if(! empty($result["rows"]))
{
	foreach ($result["rows"] as $row)
	{
		echo '
		<tr class="cart-product">
			<td class="cart_img">';
		if(! empty($row["img"]))
		{
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'"><img src="'.$row["img"]["vs"]["forcart"].'"  alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"></a> ';
		}
		echo '</td>
			<td class="cart_name">';
			/*if(! empty($row["cat"]))
			{
				echo '<a href="'.BASE_PATH_HREF.$row["cat"]["link"].'">'.$row["cat"]["name"].'</a> / ';
			}*/
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"];
			echo (! empty($row["article"]) ? '<br/>'.$this->diafan->_('Артикул').': '.$row["article"] : '');
			echo '</a>';
			if($row["additional_cost"])
			{
				foreach($row["additional_cost"] as $a)
				{
					echo '<br>'.$a["name"];
					if($a["summ"])
					{
						echo ' + '.$a["format_summ"].' '.$result["currency"];
					}
				}
			}
			if(! $row["count"])
			{
				echo '<div class="cart_no_buy">'.$this->diafan->_('Товар временно отсутствует').'</div>';
			}
			echo '</td>';
			if(! empty($result["measure_unit"]))
			{
				echo '<td class="cart_measure_unit">'.($row["measure_unit"] ? $row["measure_unit"] : $this->diafan->_('шт.')).'</td>';
			}
			
			if($result["discount"])
			{
				echo '<td class="cart_old_price">'.($row["old_price"] ? $row["old_price"] : $row["price"]).'</td>';
				echo '<td class="cart_price">'.($row["old_price"] ? $row["price"] : '').'</td>';
				echo '<td class="cart_discount">'.($row["discount"] ? $row["discount"] : '').'</td>';
			}
			else
			{
				echo '<td class="cart_price">'.$row["price"].' <span class="rouble">a</span></td>';
			}
			echo '
			<td class="js_cart_count cart_count"><nobr>'.(empty($result["hide_form"]) ? ' <span class="js_cart_count_minus cart_count_minus">-</span> <input type="text" class="number" value="'.$row["count"].'" min="0" name="editshop'.$row["id"].'" size="2"> <span class="js_cart_count_plus cart_count_plus">+</span> ' : $row["count"]).'</nobr></td>';
			
			echo '
			<td class="cart_summ">'.$row["summ"].'&nbsp;<span class="rouble">a</span></td>
			
			'.(empty($result["hide_form"]) ? '<td class="cart_remove"><span class="js_cart_remove" confirm="'.$this->diafan->_('Вы действительно хотите удалить товар из корзины?', false).'"><input type="hidden" id="del'.$row["id"].'" name="del'.$row["id"].'" value="0">Удалить</span></td>' : '').'
		</tr>';
	}

	// общая скидка от объема
	if(! empty($result["discount_total"]) || ! empty($result["discount_next"]))
	{
		echo '
			<tr>
				<td class="cart_discount_total_text" colspan="'.($result["discount"] ? 6 : 4).'" >';
		if(! empty($result["discount_next"]) && empty($result["hide_form"]))
		{
			echo $this->diafan->_('До скидки %s осталось %s', true, $result["discount_next"]["discount"], $result["discount_next"]["summ"]);
		}
		echo '</td>';
		
		echo '
		<td class="cart_discount_total">';
		if(! empty($result["discount_total"]))
		{
			echo $this->diafan->_('Скидка').' '.$result["discount_total"]["discount"];
		}
		echo '</td>
		'.(empty($result["hide_form"]) ? '<td class="cart_last_td">&nbsp;</td>' : '').'
		</tr>';
	}

	//итоговая строка для товаров
	echo '
		<tr class="cart_last_tr">
			<td class="cart_total" colspan="2">'.$this->diafan->_('Итого за товары').'</td>';

	if(! empty($result["measure_unit"]))
	{
		echo '<td></td>';
	}
		
	echo '<td class="cart_count">'.$result["count"].'</td><td class="cart_price"></td>';
	if($result["discount"])
	{
		echo '<td class="cart_old_price"></td>';
		echo '<td class="cart_discount"></td>';
	}
	echo '
	<td class="cart_summ">';
	if(! empty($result["discount_total"]))
	{
		echo '<div class="cart_summ_old_total">'.$result["old_summ_goods"].'</div>';
	}
	echo $result["summ_goods"];
	echo '</td>
			'.(empty($result["hide_form"]) ? '<td class="cart_last_td">&nbsp;</td>' : '').'
		</tr>';

	$count_clm = 0;
	if(! empty($result["discount"]))
	{
		$count_clm += 2;
	}
	if(! empty($result["measure_unit"]))
	{
		$count_clm++;
	}
	//дополнительно
	if (! empty($result["additional_cost"])) 
	{
		echo '<tr><th colspan="'.($count_clm + 5).'" class="cart_additional_title">'.$this->diafan->_('Дополнительно').'</th>
		<th class="cart_last_th">'.$this->diafan->_('Добавить').'</th></tr>';
		foreach ($result["additional_cost"] as $row)
		{
			if(! empty($result["hide_form"]) && ! in_array($row["id"], $result["cart_additional_cost"]) && ! $row["required"])
				continue;

			if ($row['amount'])
			{
				$row['text'] .= '<br>'.$this->diafan->_('Бесплатно от суммы').' '.$row['amount'].' '.$result["currency"];
			}
			echo '
			<tr>
				<td class="cart_additional" colspan="'.($count_clm + 3).'">
					<div class="cart_additional_cost_name">'.$row["name"].'</div>
					'.(empty($result["hide_form"]) ? '<div class="cart_additional_cost_text">'.$row['text'].'</div>' : '').'
				</td>
				<td class="cart_price">'.($row['percent'] ? $row['percent'].'%' : $row["price"]).'</td>
				<td class="cart_summ">'.$row["summ"].'</td>
				'.(empty($result["hide_form"]) ? '<td class="cart_check">' : '');
			if(! $row["required"] && empty($result["hide_form"]))
			{
				echo '<input name="additional_cost_ids[]" id="additional_cost_'.$row['id'].'" value="'.$row['id'].'" type="checkbox" '.(in_array($row["id"], $result["cart_additional_cost"]) ? ' checked' : '').'><label for="additional_cost_'.$row['id'].'">&nbsp;</label>';
			}
			echo (empty($result["hide_form"]) ? '</td>' : '').'
			</tr>';
		}
	}

	//способы доставки
	if (! empty($result["delivery"])) 
	{
		echo '<tr class="shipmethods"><th colspan="'.($count_clm + 5).'" class="cart_delivery_title">'.$this->diafan->_('Способ доставки').'</th>
		<th class="cart_last_th">'.$this->diafan->_('Выбрать').'</th></tr>';
		foreach ($result["delivery"] as $row)
		{
			if(! empty($result["hide_form"]) && $row["id"] != $result["cart_delivery"])
				continue;

			if (! empty($row["thresholds"]) && empty($result["hide_form"]))
			{
				foreach ($row["thresholds"]  as $r)
				{
					if($r["amount"])
					{
						$row['text'] .= '<br>'.($r["price"] ? $this->diafan->_('Стоимость').' '.$r["price"].' '.$result["currency"].' '.$this->diafan->_('от суммы') : $this->diafan->_('Бесплатно от суммы')).' '.$r['amount'].' '.$result["currency"];
					}
					else
					{
						$row['text'] .= '<br>'.($r["price"] ? $this->diafan->_('Стоимость').' '.$r["price"].' '.$result["currency"] : $this->diafan->_('Бесплатно'));
					}
				}
			}
			echo '
			<tr class="shipmethod">
				<td colspan="'.($count_clm + 4).'" class="cart_delivery">
					<div class="cart_delivery_name">'.$row["name"].'</div>';
				if(empty($result["hide_form"]))
				{
					echo '<div class="cart_delivery_text">'.$row['text'].'</div>';
				}
				echo '</td>
				<td class="cart_summ">';

				if (is_null($row["price"]))
				{
					echo $this->diafan->_('Недоступно');
				}
				elseif ($row["price"] !== false)
				{
					echo $row["price"];
					echo '&nbsp;<span class="rouble">a</span>';
				}
				echo '</td>';
				if(empty($result["hide_form"]))
				{
					echo '<td class="cart_check"><input name="delivery_id" id="delivery_id_'.$row['id'].'" value="'.$row['id'].'" type="radio" '.($row["id"] == $result["cart_delivery"] ? ' checked' : '').'><label for="delivery_id_'.$row['id'].'">&nbsp;</label></td>';
				}
			echo '
			</tr>';
			if ($row["service"] && $row["id"] == $result["cart_delivery"])
			{
				echo '<tr><td colspan="'.($count_clm + 4).'" class="cart_delivery">';
				echo $row["service_view"];
				echo  '</td>
				</tr>';
			}
		}
	}
}


//итоговая строка таблицы
echo '
	<tr class="cart_total_tr">
		<td class="cart_total" colspan="2">'.$this->diafan->_('Итого к оплате').'</td>';
if(! empty($result["measure_unit"]))
{
	echo '<td></td>';
}
echo '<td class="cart_count"></td><td class="cart_price"></td>';
	if($result["discount"])
	{
		echo '<td class="cart_old_price"></td>';
		echo '<td class="cart_discount"></td>';
	}
	echo '<td class="cart_summ">'.$result["summ"];
	echo '&nbsp;<span class="rouble">a</span>';
	if(! empty($result["tax"]))
	{
		echo '<br>'.$this->diafan->_('в т. ч. %s', true, $result["tax_name"]).'<br>'.$result["tax"];
	}
	echo '</td>
	'.(empty($result["hide_form"]) ? '<td class="cart_last_td">&nbsp;</td>' : '').'
	</tr></tbody>
</table>';
