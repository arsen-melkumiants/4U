<div class="custom_block">
	<div class="title">
		<div class="corner"></div>
		<h2><?php echo $category_info['name']?></h2>
		<div class="view_mod">
			<ul>
				<li class="default active"></li>
				<li class="gallery"></li>
				<li class="list"></li>
			</ul>
		</div>
	</div>
	<?php if (!empty($products)) {
		foreach ($products as $item) {
	?>
	<div class="item">
		<h4 class="name"><a href="<?php echo site_url('product/'.$item['id'].'/'.url_title(translitIt($item['name']), 'underscore', TRUE))?>"><?php echo $item['name']?></a></h4>
		<img src="/img/test_thumb.jpg" />
		<div class="action">
		<div class="price"><i class="c_icon_label"></i><?php echo $item['price'].' '.$item['symbol']?></div>
			<button class="orange_btn">Buy Now</button>
		</div>
	</div>
	<?php }} else {?>
	<h3 class="empty text-center">Данная категория пуста</h3>
	<?php }?>
	<div class="clear"></div>
</div>
