<!-- The Templates plugin is included to render the upload/download listings -->
<?php after_load('js', '/js/upload/vendor/tmpl.min.js');?>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<?php after_load('js', '/js/upload/vendor/load-image.min.js');?>
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<?php after_load('js', '/js/upload/vendor/canvas-to-blob.min.js');?>
<!-- blueimp Gallery script -->
<?php after_load('js', '/js/upload/vendor/jquery.blueimp-gallery.min.js');?>
<!-- The file upload form used as target for the file upload widget -->
<?php after_load('js', '/js/upload/vendor/jquery.ui.widget.js');?>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<?php after_load('js', '/js/upload/jquery.iframe-transport.js');?>
<!-- The basic File Upload plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload.js');?>
<!-- The File Upload processing plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload-process.js');?>
<!-- The File Upload image preview & resize plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload-image.js');?>
<!-- The File Upload audio preview plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload-audio.js');?>
<!-- The File Upload video preview plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload-video.js');?>
<!-- The File Upload validation plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload-validate.js');?>
<!-- The File Upload user interface plugin -->
<?php after_load('js', '/js/upload/jquery.fileupload-ui.js');?>
<script>
window.onload = function() {

	// Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: '<?php echo $upload_url?>',
	});

	// Enable iframe cross-domain access via redirect option:
	$('#fileupload').fileupload(
		'option',
		'redirect',
		window.location.href.replace(
			/\/[^\/]*$/,
			'/cors/result.html?%s'
		)
	);

	$('#fileupload').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		url: $('#fileupload').fileupload('option', 'url'),
			dataType: 'json',
			context: $('#fileupload')[0]
	}).always(function () {
		$(this).removeClass('fileupload-processing');
	}).done(function (result) {
		$(this).fileupload('option', 'done')
			.call(this, $.Event('done'), {result: result});
	});

}
</script>
<?php after_load('css', '/js/upload/blueimp-gallery.min.css');?>
<?php after_load('css', '/js/upload/jquery.fileupload.css');?>
<?php after_load('css', '/js/upload/jquery.fileupload-ui.css');?>
<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
		<?php $archive_file = glob(FCPATH.'media_files/'.$id.'/'.$id.'-*.zip');
		if (!empty($archive_file[0])) {?>
		<br />
		<div class="archive_link"><a class="btn btn-success" href="<?php echo base_url($this->MAIN_URL.'media_file/'.$id.'/archive')?>"><i class="icon-download-alt"></i> <?php echo lang('file_in_arc')?></a></div>
		<br />
		<?php }?>
		<div class="clear"></div>
	</div>
	<form id="fileupload" class="file_upload" action="<?php echo $upload_url?>" method="POST" enctype="multipart/form-data">
		<!-- The table listing the files available for upload/download -->
		<table role="presentation" class="table table-striped gallery"><tbody class="files"></tbody></table>
	</form>
	<br>
	<!-- The blueimp Gallery widget -->
	<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
		<div class="slides"></div>
		<h3 class="title"></h3>
		<a class="prev">‹</a>
		<a class="next">›</a>
		<a class="close">×</a>
		<a class="play-pause"></a>
		<ol class="indicator"></ol>
	</div>
	<div class="clear"></div>
	<?php if ($type == 'image') {?>
	<a class="btn btn-primary" href="<?php echo site_url($this->MAIN_URL.'edit/'.$id)?>">Редактирование</a>
	<a class="btn btn-primary" style="float: right;" href="<?php echo site_url($this->MAIN_URL.'media_files/'.$id)?>">Медиа контент</a>
	<?php } else {?>
	<a class="btn btn-primary" href="<?php echo site_url($this->MAIN_URL.'gallery/'.$id)?>">Галерея</a>
	<a class="btn btn-primary" style="float: right;" href="<?php echo site_url($this->MAIN_URL.'edit/'.$id)?>">Редактирование</a>
	<?php } ?>
</div>
<style>
	table.gallery .preview img {max-width: 80px;}
</style>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
			<td>
				<span class="preview"></span>
			</td>
			<td>
				<p class="name">{%=file.name%}</p>
				<strong class="error text-danger"></strong>
			</td>
			<td>
				<p class="size">Processing...</p>
			</td>
		</tr>
		{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-download fade">
			<td>
				<span class="preview">
					{% if (file.thumbnailUrl) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
						{% } %}
				</span>
			</td>
			<td>
				<p class="name">
				{% if (file.url) { %}
					<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
					{% } else { %}
					<span>{%=file.name%}</span>
					{% } %}
				</p>
				{% if (file.error) { %}
					<div><span class="label label-danger">Error</span> {%=file.error%}</div>
					{% } %}
				{% if (file.sold) { %}
					<div><span class="label label-warning">Sold</span></div>
					{% } %}
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
		</tr>
		{% } %}
</script>
