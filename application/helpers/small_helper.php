<?php
function load_views() {
	$CI =& get_instance();
	if (empty($CI->admin_methods)) {
		$CI->load->library('user_methods');
	}
	
	$CI->user_methods->load_views();
}

function load_admin_views() {
	$CI =& get_instance();
	if (empty($CI->admin_methods)) {
		$CI->load->library('admin_methods');
	}
	
	$CI->admin_methods->load_admin_views();
}

function admin_constructor() {
	$CI =& get_instance();
	if (empty($CI->admin_methods)) {
		$CI->load->library('admin_methods');
	}
	
	$CI->admin_methods->admin_constructor();
}

function admin_method($type = false, $table = false, $data = false) {
	$CI =& get_instance();
	if (empty($CI->admin_methods)) {
		$CI->load->library('admin_methods');
	}
	
	$CI->admin_methods->{$type.'_method'}($table, $data);
}

function set_header_info($data = false) {
	$CI =& get_instance();
	$method = $CI->router->fetch_method();
	if (!isset($CI->PAGE_INFO[$method])) {
		return false;
	}

	if (!empty($data) && is_array($data)) {
		foreach ($data as $key => $item) {
			$data['%'.$key] = $item;
			unset($data[$key]);
		}

		$CI->PAGE_INFO[$method]['header'] = str_replace(array_keys($data), array_values($data), $CI->PAGE_INFO[$method]['header']);
		$CI->PAGE_INFO[$method]['header_descr'] = str_replace(array_keys($data), array_values($data), $CI->PAGE_INFO[$method]['header_descr']);
	}

	$CI->data = array_merge($CI->data, $CI->PAGE_INFO[$method]);
	$CI->data['title'] = '4U :: '.$CI->data['header'];
}

function after_load($type, $url = false) {
	$type_list = array(
		'css' => '<link rel="stylesheet" type="text/css" href="$url" />',
		'js'  => '<script src="$url"></script>',
	);

	if (!in_array($type, array_keys($type_list))) {
		return false;
	}

	if (!empty($url)) {
		$GLOBALS['after_load'][$type][$url] = $url;
		return true;
	}
	if (!empty($GLOBALS['after_load'][$type])) {
		$result = PHP_EOL;
		foreach ($GLOBALS['after_load'][$type] as $item) {
			$result .= str_replace('$url', $item, $type_list[$type]).PHP_EOL;
		}
		return $result.PHP_EOL;
	}

	return false;
}

function custom_404() {
	$CI =& get_instance();
	if ($CI->input->is_ajax_request()) {
		echo 'refresh';
		exit;
	}
	show_404();
}

function get_human_time($date) {
	$time = date("Ymd",time())-date("Ymd", ($date));
	if($time == 0){
		echo 'Cегодня, в '.date("H:i",$date);
	}elseif($time == 1){
		echo 'Вчера, в '.date("H:i",$date);
	}else{
		echo date("d.m.Y, H:i",$date);
	}
}

function product_url($id, $name) {
	if (!empty($id) && !empty($name)) {
		return site_url('product/'.$id.'/'.url_title(translitIt($name), 'underscore', TRUE));
	}
}

function translitIt($str) {
	$tr = array(
		"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
		"Д"=>"d","Е"=>"e", "Ё"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
		"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
		"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
		"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
		"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
		"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
		"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j",
		"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
		"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
		"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
		"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
		"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
		" "=> "_", "."=> "", "/"=> "_"
	);
	return strtr($str,$tr);
}

function select_tree($all_branch, $select = 0, $id = 0, $deep = ''){
	$text = '';
	for($i=0; $i<count($all_branch); $i++){
		if(!empty($all_branch[$i]) && $all_branch[$i]->parent_id == $id){
			if($select == $all_branch[$i]->id && $select != 0){
				$text.='<option selected="selected" value="'.$all_branch[$i]->id.'">'.$deep.$all_branch[$i]->name.'</option>';
			}else{
				$text.='<option value="'.$all_branch[$i]->id.'">'.$deep.$all_branch[$i]->name.'</option>';
			}
			$text.=select_tree($all_branch, $select, $all_branch[$i]->id, $deep.'&nbsp;&nbsp;&nbsp;');
		}
	}
	return $text;
}

function pagination($total = false, $per_page = false, $size = 5){
	if (!$total || !$per_page) {
		return false;
	}
	$pages = ceil($total/$per_page);
	if ($pages < 2) {
		return false;
	}
	$cur_page = empty($_GET['page']) ? 1 : $_GET['page'];
	$text = '<div class="pagination"><ul>';
	if ($cur_page < 2) {
		$text .= '<li class="disabled"><a>'.lang('first').'</a></li>';
		$text .= '<li class="disabled"><a>← '.lang('previous').'</a></li>';
	} else {
		$text .= '<li><a href="?page=1">'.lang('first').'</a></li>';
		$text .= '<li><a href="?page='.($cur_page-1).'">← '.lang('previous').'</a></li>';
	}

	if ($cur_page >= $size - floor($size / 2) && ($pages - $cur_page) >= ceil($size / 2)) {
		$start_off =  $cur_page - floor($size / 2);
	} elseif (($pages - $cur_page) < ceil($size / 2)) {
		$start_off = $pages - $size + 1;
		$start_off = $start_off < 1 ? 1 : $start_off;
	} else {
		$start_off = 1;
	}
	$n = 0;
	for ($i = $start_off;$i <= $pages;$i++) {
		if ($n == $size) {
			break;
		}

		if ($i == $cur_page) {
			$text .= '<li class="active"><a>'.$i.'</a></li>';
		} else {
			$text .= '<li><a href="?page='.$i.'">'.$i.'</a></li>';
		}

		$n++;
	}

	if($cur_page >= $pages){
		$text .= '<li class="disabled"><a>'.lang('next').' → </a></li>';
		$text .= '<li class="disabled"><a>'.lang('last').'</a></li>';
	}else{
		$text .= '<li><a href="?page='.($cur_page+1).'">'.lang('next').' → </a></li>';
		$text .= '<li><a href="?page='.$pages.'">'.lang('last').'</a></li>';
	}
	$text .= '</ul></div>';

	return $text;
}
