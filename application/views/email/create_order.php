<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4>Заказ успешно принят</h4>
		<p>Ваш заказ(номер №<?php echo $order_id?>) принят и будет рассмотрен в ближайшем времени.</p>
		<p>Статус заказа Вы можете просмотреть в личном кабинете</p>
		<?php if($auto_reg){?>
		<br />
		<p>Вы не зарегистрированы в системе. Не стоит беспокоиться, <font color="red">регистрация продёт автоматически</font>, а Вашим паролем будет "<font color="red"><?php echo $email?></font>"</p>
		<p>После первого входа в систему <font color="red">в целях безопасности рекомендуем Вам сменить пароль</font></p>
		<?php }?>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<?php }?>


		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'en') { ?>
		<h4>Orders received successfully</h4>
		<p>Your order №<?php echo $order_id?> accepted and will be considered in the near future.</p>
		<p>You can see order status in your personal account</p>
		<?php if($auto_reg){?>
		<br />
		<p>You are not registered in the system. Do not worry, <font color="red"> registration threaded automatically </font>, and your password will be "<font color="red"> <?php echo $email?></font>"</p>
		<p>After the first login <font color="red">for security reasons we recommend you change your password</font></p>
		<?php }?>
		<br />
		<p>Sincerely, administration of this site <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<?php }?>
	</body>
</html>
