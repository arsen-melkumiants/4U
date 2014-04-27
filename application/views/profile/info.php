<div class="custom_block">
	<div class="title">
		<h2>My profile <a class="link" href="<?php echo site_url('personal/edit_profile')?>">Edit</a></h2>
	</div>
	<div class="row">
		<div class="col-md-8">
			<dl class="dl-horizontal">
				<?php if(!empty($user_info)) {
				foreach ($user_info as $field => $value) {?>
				<dt><?php echo $field?></dt>
				<dd><?php echo $value?></dd>
				<?php }} ?>
			</dl>
		</div>
		<div class="col-md-4">
			<?php if (!empty($stats)) {?>
			<div class="stats_block">
				<div class="title"><span>My stats</span></div>
				<ul>
					<?php foreach ($stats as $name => $value) {
					$name = ucfirst(strtolower(str_replace('_', ' ', $name)))?>
					<li><?php echo $name?> <span class="value"><?php echo $value?></span></li>
					<?php }?>
				</ul>
			</div>
			<?php }?>
		</div>
	</div>

	<div class="clear"></div>
</div>
