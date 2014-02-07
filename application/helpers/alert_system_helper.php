<?php

function init_system_alerts(){
		
		if(!empty($GLOBALS['system_alerts_data'])){
			$system_alerts = $GLOBALS['system_alerts_data'];
		}
		
		if(empty($system_alerts)){
			return false;
		}
		
		$html = '';
		foreach($system_alerts as $key => $item){
			if(empty($item['title'])){
				continue;
			}
			$item['type'] = !empty($item['type']) ? $item['type'] : 'info';
			$html .= '<div class="alert alert-'.$item['type'].' fade in">'."\n".
				'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
			if(empty($item['content'])){
				$html .= $item['title'];
			}else{
				$html .= '<h4>'.$item['title'].'</h4>'."\n".$item['content'];
			}
			$html .= '</div>'."\n";
		}
		$GLOBALS['system_alerts_data'] = '';
		$GLOBALS['system_alerts'] = $html;
}

function set_alert($title = false, $content = false, $type = false){
	if(empty($title)){
		return false;
	}
	
	$GLOBALS['system_alerts_data'][] = array(
		'title' => $title,
		'content' => $content,
		'type' => $type,
	);
}

function get_alerts(){
	if(!empty($GLOBALS['system_alerts'])){
		return $GLOBALS['system_alerts'];
	}
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
