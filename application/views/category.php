<div class="custom_block<?php echo !empty($right_block) ? ' with_right' : ''?>">
	<div class="title">
		<div class="corner"></div>
		<h2><?php echo $name?></h2>
		<div class="view_mode">
			<ul>
			<?php foreach ($types as $item) {?>
				<li data-type="<?php echo $item?>" class="<?php echo $item.($view_mode == $item ? ' active' : '')?>"></li>
			<?php }?>
			</ul>
		</div>
	</div>
	<div class="item_container">
		<?php if (!empty($products)) {
		foreach ($products as $item) {
		if ($view_mode == 'default') {?>
		<div class="item horizontal">
			<a href="<?php echo product_url($item['id'], $item['name'])?>">
				<div class="image">
					<?php echo !empty($item['file_name']) ? '<img src="/uploads/gallery/small_thumb/'.$item['file_name'].'" />' : '';?>
					<div class="bg"></div>
					<div class="bg_text"><?php echo lang('read_more')?></div>
				</div>
			</a>
			<div class="info">
				<h4 class="name"><a href="<?php echo product_url($item['id'], $item['name'])?>"><?php echo $item['name']?></a></h4>
				<small><?php echo date('d.m.Y, H:i', $item['add_date'])?></small>
				<div class="price"><i class="c_icon_label"></i><?php echo floatval($item['price']).' '.$item['symbol']?></div>
			</div>
			<div class="action">
				<?php if (isset($user_info['id']) && $user_info['id'] == $item['author_id']) {?>
				<div class="controls">
					<a href="#"><i class="c_icon_up"></i></a>
					<a href="#"><i class="c_icon_edit"></i></a>
				</div>
				<?php }?>
				<button class="orange_btn add_to_cart"
					data-name="<?php echo $item['name']?>"
					data-id="<?php echo $item['id']?>"
					data-href="<?php echo product_url($item['id'], $item['name'])?>"
					><?php echo lang('buy')?></button>
			</div>
			<div class="clear"></div>
		</div>
		<?php } elseif ($view_mode == 'gallery') {?>
		<div class="item">
			<h4 class="name"><a href="<?php echo product_url($item['id'], $item['name'])?>"><?php echo $item['name']?></a></h4>
			<a href="<?php echo product_url($item['id'], $item['name'])?>">
				<div class="image">
					<?php echo !empty($item['file_name']) ? '<img src="/uploads/gallery/small_thumb/'.$item['file_name'].'" />' : '';?>
					<div class="bg"></div>
					<div class="bg_text"><?php echo lang('read_more')?></div>
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
		</div>
		<?php } else {?>
		<div class="item list">
			<h4 class="name"><a href="<?php echo product_url($item['id'], $item['name'])?>"><?php echo $item['name']?></a></h4>
			<div class="price"><i class="c_icon_label"></i><?php echo floatval($item['price']).' '.$item['symbol']?></div>
			<button class="orange_btn add_to_cart"
				data-name="<?php echo $item['name']?>"
				data-id="<?php echo $item['id']?>"
				data-href="<?php echo product_url($item['id'], $item['name'])?>"
				><?php echo lang('buy')?></button>
			<div class="clear"></div>
		</div>
		<?php } ?>
		<?php }?>
		<div class="clear"></div>
		<?php echo pagination($total, $per_page);?>
		<?php } else {?>
		<h3 class="empty text-center"><?php echo lang('category_empty') ?></h3>
		<?php }?>
		<div class="clear"></div>
	</div>
</div>
<script>
window.onload = function() {
	$('.view_mode li').on('click', function() {
		$('.view_mode li').removeClass('active');
		$(this).addClass('active');
		$.cookie('view_mode', $(this).data('type'));
		location.reload(true);
	});
}
</script>
<?php after_load('js', '/dist/js/jquery.cookie.js');?>
