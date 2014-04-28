<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?> <a class="link" href="<?php echo site_url('personal/edit_profile')?>"><?php echo lang('edit')?></a></h2>
	</div>
	<div class="row">
		<div class="col-md-7">
			<dl class="dl-horizontal">
				<?php if(!empty($user_info)) {
				foreach ($user_info as $field => $value) {?>
				<dt><?php echo $labels[$field]?></dt>
				<dd><?php echo $value?></dd>
				<?php }} ?>
			</dl>
		</div>
		<div class="col-md-5">
			<?php if (!empty($stats)) {?>
			<div class="stats_block">
				<div class="title"><span><?php echo lang('my_stats')?></span></div>
				<ul>
					<?php foreach ($stats as $name => $value) {?>
					<li><?php echo lang($name)?> <span class="value"><?php echo $value?></span></li>
					<?php }?>
				</ul>
			</div>
			<?php }?>
		</div>
	</div>
	<div class="clear"></div>
</div>
