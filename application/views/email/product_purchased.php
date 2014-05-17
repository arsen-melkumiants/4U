<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4><?php echo lang('mail_product_purchased');?></h4>
		<p>Поздравляем! Ваш товар (номер №<?php echo $type_id?>) куплен на сумму с учетом комиссии <?php echo $amount.$currency;?>.</p>
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
		<h4><?php echo lang('mail_product_purchased');?></h4>
		<p>Congratulations! Your product (№<?php echo $type_id?>) purchased for the sum view of the commission <?php echo $amount.$currency;?>.</p>
		<p>You can see details in your personal cabinet</p>
		<br />
		<p>Sincerely, administration of this site <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<?php }?>
	</body>
</html>
