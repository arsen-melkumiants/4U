<?php after_load('js', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.12/angular.min.js');?>
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
<!-- The File Upload Angular JS module -->
<?php after_load('js', '/js/upload/jquery.fileupload-angular.js');?>
<?php after_load('js', '/js/upload/app.js');?>
<style>
/* Hide Angular JS elements before initializing */
.ng-cloak {
    display: none;
}
</style>
<script>
	var upload_url = '<?php echo $upload_url?>';
</script>
<?php after_load('css', '/js/upload/blueimp-gallery.min.css');?>
<?php after_load('css', '/js/upload/jquery.fileupload.css');?>
<?php after_load('css', '/js/upload/jquery.fileupload-ui.css');?>
<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
		<div class="steps_block">
			<ul>
				<li class="active"><a href="<?php echo site_url('profile/edit_product/'.$id)?>"><span><?php echo lang('edit')?></span></a></li>
				<li class="active"><a href="<?php echo site_url('profile/product_gallery/'.$id)?>"><span><?php echo lang('product_gallery')?></span></a></li>
				<li<?php echo $type != 'image' ? ' class="active"' : ''?>><a href="<?php echo site_url('profile/product_media_files/'.$id)?>"><span><?php echo lang('product_media')?></span></a></li>
			</ul>
		</div>
		<div class="clear"></div>
		<div class="descr"><?php echo $descr?></div>
	</div>
	<form id="fileupload" class="file_upload" action="<?php echo $upload_url?>" method="POST" enctype="multipart/form-data" data-ng-app="demo" data-ng-controller="DemoFileUploadController" data-file-upload="options" data-ng-class="{'fileupload-processing': processing() || loadingFiles}">
        <!-- Redirect browsers with JavaScript disabled to the origin page -->
        <noscript><input type="hidden" name="redirect" value="<?php echo $upload_url?>"></noscript>
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
        <div class="row fileupload-buttonbar">
            <div class="col-lg-12">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button" ng-class="{disabled: disabled}">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span><?php echo lang('add_files')?>...</span>
                    <input type="file" name="userfile" multiple>
                </span>
                <button type="button" class="btn btn-primary start" data-ng-click="submit()">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span><?php echo lang('start')?></span>
                </button>
                <button type="button" class="btn btn-warning cancel" data-ng-click="cancel()">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span><?php echo lang('cancel')?></span>
                </button>
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
            <!-- The global progress state -->
            <div class="col-lg-5 fade fileupload-progress" data-ng-class="{in: active()}">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" data-file-upload-progress="progress()"><div class="progress-bar progress-bar-success" data-ng-style="{width: num + '%'}"></div></div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
		<table class="table table-striped files gallery ng-cloak">
			<tr data-ng-repeat="file in queue" data-ng-class="{'processing': file.$processing()}">
				<td data-ng-switch data-on="!!file.thumbnailUrl">
					<div class="preview" data-ng-switch-when="true">
						<a data-ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}" data-gallery><img data-ng-src="{{file.thumbnailUrl}}" alt=""></a>
					</div>
					<div class="preview" data-ng-switch-default data-file-upload-preview="file"></div>
				</td>
				<td>
					<p class="name" data-ng-switch data-on="!!file.url">
					<span data-ng-switch-when="true" data-ng-switch data-on="!!file.thumbnailUrl">
						<a data-ng-switch-when="true" data-ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}" data-gallery>{{file.name}}</a>
						<a data-ng-switch-default data-ng-href="{{file.url}}" title="{{file.name}}" download="{{file.name}}">{{file.name}}</a>
					</span>
					<span data-ng-switch-default>{{file.name}}</span>
					</p>
					<div data-ng-show="file.error"><span class="label label-danger"><?php echo lang('error')?></span> {{file.error}}</div>
					<div data-ng-show="file.success"><span class="label label-success"><?php echo lang('file_uploaded')?> {{file.success}}</span></div>
					<div data-ng-show="file.sold"><span class="label label-warning"><?php echo lang('sold')?></span></div>
				</td>
				<td>
					<p class="size">{{file.size | formatFileSize}}</p>
					<div class="progress progress-striped active fade" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-ng-class="{pending: 'in'}[file.$state()]" data-file-upload-progress="file.$progress()"><div class="progress-bar progress-bar-success" data-ng-style="{width: num + '%'}"></div></div>
				</td>
				<td>
					<button type="button" class="btn btn-primary start" data-ng-click="file.$submit()" data-ng-hide="!file.$submit || options.autoUpload" data-ng-disabled="file.$state() == 'pending' || file.$state() == 'rejected'">
						<i class="glyphicon glyphicon-upload"></i>
						<span><?php echo lang('start')?></span>
					</button>
					<button type="button" class="btn btn-warning cancel" data-ng-click="file.$cancel()" data-ng-hide="!file.$cancel">
						<i class="glyphicon glyphicon-ban-circle"></i>
						<span><?php echo lang('cancel')?></span>
					</button>
					<button data-ng-controller="FileDestroyController" type="button" class="btn btn-danger destroy" data-ng-click="file.$destroy()" data-ng-hide="!file.$destroy">
						<i class="glyphicon glyphicon-trash"></i>
						<span><?php echo lang('delete')?></span>
					</button>
				</td>
			</tr>
		</table>
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
<?php /*
	<a class="btn btn-primary" href="<?php echo site_url('profile/edit_product/'.$id)?>">Edit information</a>
	<?php if ($type != 'image') {?>
	<a class="btn btn-primary" href="<?php echo site_url('profile/product_gallery/'.$id)?>">Gallery</a>
	<?php } else {?>
	<a class="btn btn-primary" href="<?php echo site_url('profile/product_media_files/'.$id)?>">Media content</a>
	<?php } ?>
*/ ?>
	<?php if ($type == 'image') {?>
	<a class="btn btn-primary" href="<?php echo site_url('profile/edit_product/'.$id)?>"><?php echo lang('prev_step')?></a>
	<a class="btn btn-primary" style="float: right;" href="<?php echo site_url('profile/product_media_files/'.$id)?>"><?php echo lang('next_step')?></a>
	<?php } else {?>
	<form action="<?php echo site_url('profile/finish/'.$id)?>" method="post">
		<a class="btn btn-primary" href="<?php echo site_url('profile/product_gallery/'.$id)?>"><?php echo lang('prev_step')?></a>
		<button type="submit" name="finish" class="btn btn-primary" style="float: right;"><?php echo lang('finish')?></button>
	</form>
	<?php } ?>
</div>
<!-- The template to display files available for upload -->
<!-- The template to display files available for download -->
