<div class="custom_block">
	<div class="title">
		<div class="corner"></div>
		<h1><?php echo $name?><a href="<?php echo site_url('profile/add_product')?>" class="btn btn-primary" style="float:right;">Add product</a></h1>
		<div class="clear"></div>
	</div>
	<?php if (!empty($type_list)) {?>
	<ul class="nav nav-justified profile_tabs">
		<?php foreach ($type_list as $name => $val) {?>
		<li<?php echo $type == $name ? ' class="active"' : ''?>><a href="<?php echo site_url('profile/products/'.$name)?>"><?php echo $name?></a></li>
		<?php }?>
	</ul>
	<?php }?>
	<div class="product_list">
		<?php echo $table ?>
	</div>
</div>
