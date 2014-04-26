<div class="custom_block">
	<div class="title stars">
		<h2><?php echo $name?></h2>
	</div>
	<?php if (!empty($products)) {
	foreach ($products as $item) {?>
	<div class="item">
		<h4 class="name"><a href="<?php echo product_url($item['id'], $item['name'])?>"><?php echo $item['name']?></a></h4>
		<a href="<?php echo product_url($item['id'], $item['name'])?>">
			<div class="image">
				<?php echo !empty($item['file_name']) ? '<img src="/uploads/gallery/small_thumb/'.$item['file_name'].'" />' : '';?>
				<div class="bg"></div>
				<div class="bg_text">Read more</div>
			</div>
		</a>
		<div class="action">
			<div class="price"><i class="c_icon_label"></i><?php echo $item['price'].' '.$item['symbol']?></div>
			<button class="orange_btn add_to_cart"
				data-name="<?php echo $item['name']?>"
				data-id="<?php echo $item['id']?>"
				data-href="<?php echo product_url($item['id'], $item['name'])?>"
				>Buy Now</button>
		</div>
		<div class="clear"></div>
	</div>
	<?php }}?>
	<div class="clear"></div>
</div>