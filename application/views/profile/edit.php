<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
		<div class="steps_block">
			<ul>
				<li class="active"><a href="<?php echo site_url('profile/edit_product/'.$id)?>"><span><?php echo lang('edit')?></span></a></li>
				<li><a href="<?php echo site_url('profile/product_gallery/'.$id)?>"><span><?php echo lang('product_gallery')?></span></a></li>
				<li><a href="<?php echo site_url('profile/product_media_files/'.$id)?>"><span><?php echo lang('product_media')?></span></a></li>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
	<?php echo $center_block ?>
	<div class="clear"></div>
</div>
