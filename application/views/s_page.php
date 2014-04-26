<?php if (!empty($header)) {
$center_block = '<div class="custom_block">
	<div class="title">
		<h2>'.$header.'</h2>
	</div>
		'.(!empty($center_block) ? $center_block : '').'
	<div class="clear"></div>
</div>';
}?>
<div class="container main_block">
	<div class="row">
		<?php echo get_alerts();?>
	</div>
	<div class="row">
	<?php if(!empty($left_block)){?>
		<div class="col-md-3 left_block">
			<div class="search_block">
			<form action="<?php echo site_url('search')?>">
					<input type="text" name="q" placeholder="What are you looking for?"/>
					<input type="submit" value=""/>
				</form>
			</div>
			<?php echo $left_block;?>
		</div>
		<?php if (!empty($right_block)) {?>
		<div class="col-md-6"><?php echo !empty($center_block) ? '<div class="center_block"><hr />'.$center_block.'</div>' : '';?></div>
		<div class="col-md-3"><div class="right_block"><?php echo $right_block;?></div></div>
		<?php } else {?>
		<div class="col-md-9"><?php echo !empty($center_block) ? '<div class="center_block"><hr />'.$center_block.'</div>' : '';?></div>
		<?php }?>
	<?php } elseif(!empty($center_block)) {?>
		<?php if (!empty($right_block)) {?>
		<div class="col-md-9"><div class="center_block"><hr /><?php echo $center_block;?></div></div>
		<div class="col-md-3"><div class="right_block"><?php echo $right_block;?></div></div>
		<?php } else {?>
		<div class="col-md-12"><div class="center_block"><hr /><?php echo $center_block;?></div></div>
		<?php }?>
	<?php }?>
	</div>
</div>
