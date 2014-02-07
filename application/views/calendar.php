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
	<div class="col-md-12">
		<div id='loading' style='display:none'>loading...</div>
		<div id='calendar'></div>
		<?php // echo $center_block;?>
	</div>
</div>
