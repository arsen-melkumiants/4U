<?php
function get_human_time($date)
{	
	$time = date("Ymd",time())-date("Ymd", ($date));
	if($time == 0){
		echo 'Cегодня, в '.date("H:i",$date);
	}elseif($time == 1){
		echo 'Вчера, в '.date("H:i",$date);
	}else{
		echo date("d.m.Y, H:i",$date);
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
	if(!$total || !$per_page){
		return false;
	}
	$pages = ceil($total/$per_page);
	if($pages < 2){
		return false;
	}
	$cur_page = empty($_GET['page']) ? 1 : $_GET['page'];
	echo '<div class="pagination">
	  <ul>';
	  	if($cur_page < 2){
			echo '<li class="active"><a>Первая</a></li>';
			echo '<li class="active"><a><</a></li>';
		}else{
			echo '<li><a href="?page=1">Первая</a></li>';
			echo '<li><a href="?page='.($cur_page-1).'"><</a></li>';
		}
		
			if($cur_page >= $size - floor($size / 2) && ($pages - $cur_page) >= ceil($size / 2)){
				$start_off =  $cur_page - floor($size / 2);
			}elseif(($pages - $cur_page) < ceil($size / 2)){
				$start_off = $pages - $size + 1;
				$start_off = $start_off < 1 ? 1 : $start_off;
			}else{
				$start_off = 1;
			}
			$n = 0;
			for($i = $start_off;$i <= $pages;$i++){
				if($n == $size){break;}
				if($i == $cur_page){
					echo '<li class="active"><a>'.$i.'</a></li>';
				}else{
					echo '<li><a href="?page='.$i.'">'.$i.'</a></li>';
				}
				
			$n++;
			}

		if($cur_page >= $pages){
	  		echo '<li class="active"><a>></a></li>';
			echo '<li class="active"><a>Последняя</a></li>';
		}else{
			echo '<li><a href="?page='.($cur_page+1).'">></a></li>';
			echo '<li><a href="?page='.$pages.'">Последняя</a></li>';
		}
	echo '</ul>
	</div>';
}