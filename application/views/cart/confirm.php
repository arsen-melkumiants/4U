<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
		<div class="steps_block">
			<ul>
				<?php if (!empty($links)) {
				$class = ' class="active"';
				foreach ($links as $link => $item) {?>
				<li<?php echo $class?>><a href="<?php echo site_url('cart/'.$link)?>"><span><?php echo $item?></span></a></li>
				<?php if ($link == $cur_step) {$class = '';}}}?>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
	<?php echo $center_block ?>
</div>
