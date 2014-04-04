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
				<div class="main_image">
					<img src="/img/test_main_image.jpg" />
				</div>
				<div class="mini_images">
					<ul>
						<li><a href="#"><img src="/img/test_mini_image.jpg" /></a></li>
						<li><a href="#"><img src="/img/test_mini_image.jpg" /></a></li>
						<li><a href="#"><img src="/img/test_mini_image.jpg" /></a></li>
						<li><a href="#"><img src="/img/test_mini_image.jpg" /></a></li>
						<li><a href="#"><img src="/img/test_mini_image.jpg" /></a></li>
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
					<?php echo $product_info['description'] ?>
				</div>
			</div>
		</div>
	</div>
</div>
