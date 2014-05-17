<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4><?php echo lang('mail_services_mark_product');?></h4>
		<p>Оплата услуги "Выделить товар" выполнена успешно.</p>
		<p>Детали Вы можете просмотреть в личном кабинете.</p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<?php }?>


		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'en') { ?>
		<h4><?php echo lang('mail_services_mark_product');?></h4>
		<p>Payment services "Select product" completed successfully.</p>
		<p>You can see details in your personal cabinet</p>
		<br />
		<p>Sincerely, administration of this site <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<?php }?>
	</body>
</html>
