<div class="custom_block">
	<div class="title">
		<div class="corner"></div>
		<?php if(!empty($categories)){?>
		<div class="breadcrumps">
			<ul>
			<?php foreach(array_reverse($categories) as $item) {?>
				<li><a href="<?php echo site_url('category/'.$item['alias'])?>"><?php echo $item['name']?></a></li>
			<?php }?>
			</ul>
		</div>
		<?php }?>
		<h1><?php echo $product_info['name']?></h1>
		<div class="clear"></div>
	</div>
	<div class="product">
		<div class="row">
			<div class="col-md-8" id="images">
				<?php
				$main_image = false;
				if (!empty($images)) {
					foreach ($images as $item) {
						if ($item['main']) {
							$main_image = '<a href="'.base_url('uploads/gallery/'.$item['file_name']).'"><img src="'.base_url('uploads/gallery/'.$item['file_name']).'" /></a>';
							break;
						}
					}
				}?>
				<div class="main_image" <?php echo empty($main_image) ? 'style="min-height: 290px;"' : ''?>><?php echo $main_image?></div>
				<div class="mini_images">
					<ul>
					<?php
						if (!empty($images)) {
							foreach ($images as $item) {
								if (!$item['main']) {
									if(!preg_match('/\.(jpg|jpeg|png|gif)/iu', $item['file_name'])) {
										$attach_files[] = $item;
										continue;
									}
									?>
									<li><a href="<?php echo base_url('uploads/gallery/'.$item['file_name']);?>"><img src="<?php echo base_url('uploads/gallery/small_thumb/'.$item['file_name']);?>" /></a></li>
					<?php }}}?>
					</ul>
					<div class="clear"></div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="text-center">
				<div class="price"><?php echo $product_info['price'].' '.$product_info['symbol']?></div>
					<button class="orange_btn add_to_cart" 
						data-name="<?php echo $product_info['name']?>" 
						data-id="<?php echo $product_info['id']?>" 
						data-href="<?php echo product_url($product_info['id'], $product_info['name'])?>"
					><?php echo lang('buy')?></button>
					<?php if (isset($user_info['id']) && $user_info['id'] == $product_info['author_id']) {?>
					<div class="actions">
						<ul>
							<li><a href="#"><i class="c_icon_up"></i>Go to up</a></li>
							<li><a href="#"><i class="c_icon_edit"></i>Allocate lot</a></li>
							<li><a href="#"><i class="c_icon_star"></i>Make VIP</a></li>
						</ul>
					</div>
					<?php }?>
					<div class="info">
						<ul>
							<li><?php echo lang('product_amount').': '.$product_info['amount']?>
							<li>Views: <?php echo $product_info['views']?></li>
							<li>Added: <?php echo date('d.m.Y, H:i', $product_info['add_date'])?></li>
							<li>ID: <?php echo $product_info['id']?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="seller_info">
					<ul>
						<li><span>Seller:</span> <?php echo $product_info['username']?></li>
						<li><span>Phone:</span> <?php echo $product_info['phone']?></li>
					</ul>
				</div>
				<div class="description">
				<h2><?php echo lang('description')?></h2>
					<?php echo $product_info['content'] ?>
				</div>
				<div class="files_list">
					<?php if (!empty($attach_files)) {?>
					<ul>
						<?php foreach ($attach_files as $item) {
						$ext = strtolower(end(explode('.', $item['file_name'])));?>
						<li><i><span><?php echo $ext?></span></i><a href="<?php echo base_url('uploads/gallery/'.$item['file_name'])?>"><?php echo $item['file_name']?></a></li>
						<?php }?>
					</ul>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php after_load('js', '/js/upload/vendor/jquery.blueimp-gallery.min.js');?>
<?php after_load('css', '/js/upload/blueimp-gallery.min.css');?>
<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
	<div class="slides"></div>
	<h3 class="title"></h3>
	<a class="prev">‹</a>
	<a class="next">›</a>
	<a class="close">×</a>
	<a class="play-pause"></a>
	<ol class="indicator"></ol>
</div>
<script>
document.getElementById('images').onclick = function (event) {
	event = event || window.event;
	var target = event.target || event.srcElement,
		link = target.src ? target.parentNode : target,
		options = {index: link, event: event},
		links = this.getElementsByTagName('a');
	blueimp.Gallery(links, options);
};
</script>
