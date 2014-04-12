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
		'use strict';

		// Initialize the jQuery File Upload widget:
		$('#fileupload').fileupload({
			// Uncomment the following to send cross-domain cookies:
			//xhrFields: {withCredentials: true},
			url: '<?php echo site_url('profile/upload_gallery/'.$id)?>',
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

		if (window.location.hostname === 'blueimp.github.io') {
			// Demo settings:
			$('#fileupload').fileupload('option', {
				url: '<?php echo site_url('profile/upload_gallery/'.$id)?>',
				// Enable image resizing, except for Android and Opera,
				// which actually support image resizing, but fail to
				// send Blob objects via XHR requests:
				disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator.userAgent),
				maxFileSize: 5000000,
				acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
			});
			// Upload server status check for browsers with CORS support:
			if ($.support.cors) {
				$.ajax({
					url: '<?php echo site_url('profile/upload_gallery/'.$id)?>',
					type: 'HEAD'
					}).fail(function () {
					$('<div class="alert alert-danger"/>')
						.text('Upload server currently unavailable - ' +
						new Date())
						.appendTo('#fileupload');
					});
				}
				} else {
				// Load existing files:
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

		}
	</script>
<?php after_load('css', '/js/upload/blueimp-gallery.min.css');?>
<?php after_load('css', '/js/upload/jquery.fileupload.css');?>
<?php after_load('css', '/js/upload/jquery.fileupload-ui.css');?>
<div class="custom_block">
	<div class="title">
		<h2>Галерея продукта</h2>
	</div>
	<form id="fileupload" class="file_upload" action="<?php echo site_url('profile/upload_gallery/'.$id)?>" method="POST" enctype="multipart/form-data">
		<!-- Redirect browsers with JavaScript disabled to the origin page -->
		<noscript><input type="hidden" name="redirect" value="<?php echo site_url('profile/upload_gallery/'.$id)?>"></noscript>
		<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
		<div class="row fileupload-buttonbar">
			<div class="col-lg-12">
				<!-- The fileinput-button span is used to style the file input field as button -->
				<span class="btn btn-success fileinput-button">
					<i class="glyphicon glyphicon-plus"></i>
					<span>Add files...</span>
					<input type="file" name="userfile" multiple>
				</span>
				<button type="submit" class="btn btn-primary start">
					<i class="glyphicon glyphicon-upload"></i>
					<span>Start</span>
				</button>
				<button type="reset" class="btn btn-warning cancel">
					<i class="glyphicon glyphicon-ban-circle"></i>
					<span>Cancel</span>
				</button>
				<!-- The global file processing state -->
				<span class="fileupload-process"></span>
			</div>
			<!-- The global progress state -->
			<div class="col-lg-5 fileupload-progress fade">
				<!-- The global progress bar -->
				<div class="progress" aria-valuemin="0" aria-valuemax="100">
					<div class="progress-bar" style="width:0%;"></div>
				</div>
				<!-- The extended global progress state -->
				<div class="progress-extended">&nbsp;</div>
			</div>
		</div>
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
</div>
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
			<td>
				{% if (!i && !o.options.autoUpload) { %}
					<button class="btn btn-primary start" disabled>
						<i class="glyphicon glyphicon-upload"></i>
						<span>Start</span>
					</button>
					{% } %}
				{% if (!i) { %}
					<button class="btn btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>Cancel</span>
					</button>
					{% } %}
				<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar" style="width:0%;"></div></div>
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
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<td>
				{% if (file.deleteUrl) { %}
					<button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
						<i class="glyphicon glyphicon-trash"></i>
						<span>Delete</span>
					</button>
					{% } else { %}
					<button class="btn btn-warning cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span>Cancel</span>
					</button>
					{% } %}
			</td>
		</tr>
		{% } %}
</script>
