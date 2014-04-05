<div class="row">
	<div class="col-md-8">
		<dl class="dl-horizontal">
			<?php if(!empty($profile_info)) {
			foreach ($profile_info as $field => $value) {?>
			<dt><?php echo $field?></dt>
			<dd><?php echo $value?></dd>
			<?php }} ?>
		</dl>
	</div>
	<div class="col-md-4">
		<div class="stats_block">
			<div class="title"><span>My stats</span></div>
			<ul>
				<li>
				The most popular topic
				<div class="stars">
					<i class="icon-star"></i>
					<i class="icon-star"></i>
					<i class="icon-star"></i>
					<i class="icon-star"></i>
					<i class="icon-star"></i>
				</div>
				</li>
				<li>
				The most popular topic
				<div class="stars">
					<i class="icon-star"></i>
					<i class="icon-star"></i>
					<i class="icon-star empty"></i>
					<i class="icon-star empty"></i>
					<i class="icon-star empty"></i>
				</div>
				</li>
				<li>
				The most popular topic
				<div class="stars">
					<i class="icon-star"></i>
					<i class="icon-star"></i>
					<i class="icon-star empty"></i>
					<i class="icon-star empty"></i>
					<i class="icon-star empty"></i>
				</div>
				</li>
				<li>
				The most popular topic
				<div class="stars">
					<i class="icon-star"></i>
					<i class="icon-star"></i>
					<i class="icon-star empty"></i>
					<i class="icon-star empty"></i>
					<i class="icon-star empty"></i>
				</div>
				</li>
			</ul>
		</div>
	</div>
</div>
