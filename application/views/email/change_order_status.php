<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4>Заказ №<?php echo $id?></h4>
		<p>Статус Вашего заказ был обновлён.</p>
		<p>Изменения по заказу Вы можете просмотреть в личном кабинете.</p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<?php }?>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'en') { ?>
		<h4>Order №<?php echo $id?></h4>
		<p>Your order status has been updated.</p>
		<p>changes of order you can see in your personal account.</p>
		<br />
		<p>Sincerely, administration of this site <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<?php }?>
	</body>
</html>
