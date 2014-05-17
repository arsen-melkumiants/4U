<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4>Заказ №<?php echo $id?> успешно оплачен</h4>
		<p>Поздравляем Вас! Оплата заказа была успешно проведена!</p>
		<p>В ближайшее время с вами свяжеться менеджер</p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<?php }?>


		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'en') { ?>
		<h4>Order № <?php echo $id?> successfully paid</h4>
		<p>Congratulations! Payment order has been successfully held!</p>
		<p>In the near future manager will contact you</p>
		<br />
		<p>Sincerely, administration of this site <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<?php }?>
	</body>
</html>
