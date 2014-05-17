<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4>Товар прошел модерацию</h4>
		<p>Поздравляем! Ваш товар (номер №<?php echo $id?>) прошел модерацию и был успешно опубликован.</p>
		<p>Посмотреть товар Вы можете пройдя по ссылке <?php echo product_url($id, $name);?></p>
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
		<h4>This product passed moderation</h4>
		<p>Congratulations! Your product (№<?php echo $order_id?>) was moderated and was successfully published.</p>
		<p>You can view the product following the link <?php product_url($id, $name);?></p>
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