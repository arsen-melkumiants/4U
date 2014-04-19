<?php 
$options = array();
if (!empty($select_contents)) {
	foreach ($select_contents as $key => $item) {
		if (is_array($item)) {
			$options[$key] = '';
		} else {
			$options[$key]['text'] = $item;
			continue;
		}
		foreach ($item as $sub_item) {
			$options[$key] .= '<option value="'.$sub_item['id'].'">'.$sub_item['name'].'</option>'.PHP_EOL;
		}
	}
}
?>
<script>
var options = <?php echo json_encode($options)?>;
var update_list = function() {
	$(document).on('change', '.type_menu_list', function() {
		var type = $(this).val();
		if (typeof options[type] != 'undefined') {
			if (typeof options[type]['text'] != 'undefined') {
				$('.items_list select').remove();
				$('.items_list > div div').remove();
				$('.items_list > div').html('<input type="hidden" class="form-control" value="" />');
			} else {
				$('.items_list input').remove();
				$('.items_list > div').html('<select class="form-control selectpicker" name="item_id" data-live-search="true"></select>');
				$('.items_list select').html(options[type]);
				$('.selectpicker').selectpicker('refresh');
			}
		}
	});
}

<?php echo ($this->IS_AJAX) ? 'update_list()' : 'window.onload = function() {update_list();}';?>
</script>

<style>
.items_list input {display: none;}
</style>
