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
			<div class="col-md-8">
					<?php
					$main_image = false;
					if (!empty($images)) {
						foreach ($images as $item) {
							if ($item['main']) {
								$main_image = '<img src="'.base_url('uploads/gallery/'.$item['file_name']).'" />';
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
							if (!$item['main']) {?>
								<li><a href="#"><img src="<?php echo base_url('uploads/gallery/small_thumb/'.$item['file_name']);?>" /></a></li>
					<?php }}}?>
					</ul>
					<div class="clear"></div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="text-center">
				<div class="price"><?php echo $product_info['price'].' '.$product_info['symbol']?></div>
					<button class="orange_btn">Buy Now</button>
					<div class="actions">
						<ul>
							<li><a href="#"><i class="c_icon_up"></i>Go to up</a></li>
							<li><a href="#"><i class="c_icon_edit"></i>Allocate lot</a></li>
							<li><a href="#"><i class="c_icon_star"></i>Make VIP</a></li>
						</ul>
					</div>
					<div class="info">
						<ul>
							<li>Views: <?php echo $product_info['views']?></li>
							<li>Added: <?php echo date('d.m.Y, H:i')?></li>
							<li>ID: <?php echo $product_info['id']?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="saller_info">
					<ul>
					<li><span>Saller:</span> <?php echo $product_info['username']?></li>
						<li><span>Phone:</span> <?php echo $product_info['phone']?></li>
					</ul>
				</div>
				<div class="description">
					<h2>Info</h2>
					<?php echo $product_info['content'] ?>
				</div>
			</div>
		</div>
	</div>
</div>
