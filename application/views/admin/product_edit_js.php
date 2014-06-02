<script>
var update_count = function() {
	var set_unlimited = function() {
		var selected = $('input[name="unlimited"]:checked').val();
		if (selected === '1') {
			$('.amount_field').slideUp('fast');
		} else {
			$('.amount_field').slideDown('fast');
		}
	};

	set_unlimited();
	$('input[name="unlimited"]').on('change', function() {
		set_unlimited();
	});
};

<?php echo ($this->IS_AJAX) ? 'update_count();' : 'window.onload = function() {update_count();}';?>
</script>
