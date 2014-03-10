<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <?php echo !empty($this->data['header']) ? '<h4 class="modal-title">'.$this->data['header'].'</h4>' : '';?>
</div>
<div class="modal-body">
	<?php echo get_alerts();?>
	<?php echo $this->data['center_block']?>
</div>
