<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<div class="modal-body">
	<?php echo get_alerts();?>
	<?php echo $this->data['center_block']?>
</div>
<?php echo after_load('css');?>
<?php echo after_load('js');?>
