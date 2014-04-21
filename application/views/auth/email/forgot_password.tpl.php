<html>
<body>
	<h4>Пароль был успешно сброшен</h4>
	<p>Пожалуйста перейдите по ссылке <?php echo anchor('personal/reset_password/'. $forgotten_password_code, 'Сбросить пароль');?>.</p>
    <br />
    <p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
</body>
</html>