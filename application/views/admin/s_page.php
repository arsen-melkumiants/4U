<div class="row">
	<?php echo get_alerts();?>
</div>
<?php if(!empty($header)){?>
<div class="header_block">
	<h1><?php echo $header?></h1>
	<?php if(!empty($header_descr)){?>
		<h5><?php echo $header_descr?></h5>
	<?php }?>
</div>
<?php }?>
<div class="row">
<?php if(!empty($left_block)){?>
	<div class="col-md-4"><?php echo $left_block;?></div>
	<div class="col-md-8"><?php echo $center_block;?></div>
<?php }elseif(!empty($center_block)){?>
	<div class="col-md-12"><?php echo $center_block;?></div>
<?php }?>
</div>
