<a href="<?php echo product_url($id, $name)?>">
	<div class="image"<?php echo empty($file_name) ? 'style="min-height: 90px;"' : ''?>>
		<?php echo !empty($file_name) ? '<img src="/uploads/gallery/small_thumb/'.$file_name.'" />' : '';?>
	</div>
</a>
<div class="info">
	<a class="name" href="<?php echo product_url($id, $name)?>"><?php echo $name ?></a>
	<span><?php echo date('d.m.Y', $add_date)?></span>
</div>
