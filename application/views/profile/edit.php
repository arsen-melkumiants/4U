<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
		<div class="steps_block">
			<ul>
				<li class="active"><a href="<?php echo site_url('profile/edit_product/'.$id)?>"><span>Edit</span></a></li>
				<li><a href="<?php echo site_url('profile/product_gallery/'.$id)?>"><span>Gallery</span></a></li>
				<li><a href="<?php echo site_url('profile/product_media_files/'.$id)?>"><span>Media</span></a></li>
			</ul>
		</div>
	</div>
	<?php echo $center_block ?>
	<div class="clear"></div>
</div>
