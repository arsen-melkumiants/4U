<?php 
$options = array();
if (!empty($select_contents)) {
	foreach ($select_contents as $key => $item) {
		if (empty($item)) {
			continue;
		}
		$options[$key] = '';
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
			$('select.items_list').html(options[type]);
			$('.selectpicker').selectpicker('refresh');
		}
	});
}

<?php echo ($this->IS_AJAX) ? 'update_list()' : 'window.onload = function() {update_list();}';?>
</script>
