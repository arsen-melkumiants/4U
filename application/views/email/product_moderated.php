<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4><?php echo lang('mail_product_moderation');?></h4>
		<p>Поздравляем! Ваш товар (номер №<?php echo $id?>) прошел модерацию и был успешно опубликован.</p>
		<p>Просмотреть товар Вы можете пройдя по ссылке <?php echo product_url($id, $name);?></p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<?php }?>


		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'en') { ?>
		<h4><?php echo lang('mail_product_moderation');?></h4>
		<p>Congratulations! Your product (№<?php echo $id?>) was moderated and was successfully published.</p>
		<p>You can see the product following the link <?php product_url($id, $name);?></p>
		<br />
		<p>Sincerely, administration of this site <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<?php }?>
	</body>
</html>
