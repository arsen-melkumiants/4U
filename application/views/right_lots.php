<div class="custom_block">
	<div class="title stars">
		<h2><?php echo $name?></h2>
	</div>
	<?php if (!empty($products)) {
	foreach ($products as $item) {
		$item['price'] += $this->shop_model->product_commission($item);?>

	<div class="item">
		<h4 class="name" title="<?php echo $item['name']?>"><a href="<?php echo product_url($item['id'], $item['name'])?>"><?php echo $item['name']?></a></h4>
		<a href="<?php echo product_url($item['id'], $item['name'])?>">
			<div class="image"<?php echo !empty($item['file_name']) ? ' style="border:0;background:none;"' : '' ?>>
				<?php echo !empty($item['file_name']) ? '<img src="/uploads/gallery/'.$item['folder'].'small_thumb/'.$item['file_name'].'" />' : '';?>
				<?php /*
				<div class="bg"></div>
				<div class="bg_text"><?php echo lang('read_more')?></div>
				*/ ?>
			</div>
		</a>
		<div class="action">
			<div class="price"><i class="c_icon_label"></i><?php echo floatval($item['price']).' '.$item['symbol']?></div>
			<button class="orange_btn add_to_cart"
				data-name="<?php echo $item['name']?>"
				data-id="<?php echo $item['id']?>"
				data-href="<?php echo product_url($item['id'], $item['name'])?>"
				><?php echo lang('buy')?></button>
		</div>
		<div class="clear"></div>
	</div>
	<?php }}?>
	<div class="clear"></div>
</div>
