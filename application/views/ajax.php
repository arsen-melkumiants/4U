
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<div class="modal-body">
	<?php echo get_alerts();?>
	<div class="custom_block">
		<div class="title">
			<h2><?php echo !empty($header) ? $header : ''?></h2>
		</div>
			<?php echo $center_block?>
		<div class="clear"></div>
	</div>
</div>
<?php echo after_load('css');?>
<?php echo after_load('js');?>
