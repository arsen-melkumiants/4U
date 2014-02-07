<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Form_creator {

	public $form_data = array();

	public $btn_data = array();

	public $grid_type = 'col-md';

	public $ajax_mode = false;

	public function __construct($grid_type = false) {
		$this->grid_type = $grid_type ? $grid_type : $this->grid_type;
		$this->ajax_mode = isset($_GET['ajax']) ? true : false;
	}

	public function __call($method, $arguments) {
		if (!method_exists( $this, $method)) {
			throw new Exception('Undefined method Form_creator::' . $method . '() called');
		}

		return call_user_func_array(array($this, $method), $arguments);
	}

	public function attributes($list = false, $data = false) {
		if (empty($list) || empty($data)) {
			return false;
		}
		$attrs = '';
		foreach ($list as $name) {
			if (empty($data[$name])) {
				continue;
			}
			$attrs .= ' '.$name.'="'.$data[$name].'"';
		}
		return $attrs;
	}

	public function input($name = false, $params = false, $type = "text") {
		if (empty($name)) {
			return $this;
		}

		$params['name'] = $name;
		$params['type'] = $type;
		$params['value'] = isset($params['value']) ? $params['value'] : '';
		$params['label'] = !empty($params['label']) ? $params['label'] : '';

		if (!empty($params['valid_rules'])) {
			$CI =& get_instance();
			$CI->load->library('form_validation');
			$field_name = !empty($params['label']) ? $params['label'] : (!empty($params['placeholder']) ? $params['placeholder'] : ucfirst($name));
			$CI->form_validation->set_rules($name, $field_name, $params['valid_rules']);
			$CI->form_validation->run();
			$params['value'] = $CI->form_validation->set_value($name, $params['value']);
			$params['error'] = form_error($name);
			if (function_exists('set_alert')) {
				set_alert(form_error($name), false, 'danger');
			}
		}

		$params['class'] = !empty($params['class']) ? 'form-control '.$params['class'] : 'form-control';

		$label = !empty($params['label']) ? '<label class="'.$this->grid_type.'-3 control-label">'.$params['label'].'</label>' : '';

		$addon = '';
		if (!empty($params['icon'])) {
			$addon = '<i class="icon-'.$params['icon'].'"></i>';
		}elseif (!empty($params['symbol'])) {
			$addon = $params['symbol'];
		}

		if (empty($params['width'])) {
			$params['width'] = !empty($params['label']) ? $this->grid_type.($this->ajax_mode ? '-7' : '-4') : $this->grid_type.'-12';
		}else{
			$params['width'] = $this->grid_type.'-'.($this->ajax_mode ? $params['width'] + 2 : $params['width']);
		}

		//offset
		$params['width'] = !empty($params['offset']) ? $params['width'].' '.$this->grid_type.'-offset-'.$params['offset'] : $params['width'] ;


		//radio-buttons
		$input = '';
		if ($type == 'radio') {
			if (is_array($params['inputs'])) {
				foreach ($params['inputs'] as $value => $info) {
					$info['checked'] = !empty($params['value']) && $params['value'] == $value ? ' checked="checked"' : '';
					$input .= '<label class="radio-inline">'.PHP_EOL;
					$input .= '<input type="radio" name="'.$name.'" value="'.$value.'"'.$info['checked'].'> '.$info['name'].PHP_EOL;
					$input .= '</label>'.PHP_EOL;
				}
			}
		}elseif ($type == 'textarea') {
			$attrs_list = array('class','name','readonly','rows');
			$input .= '<textarea'.$this->attributes($attrs_list, $params).'>'.$params['value'].'</textarea>';
		}else{
			$attrs_list = array('type','class','name','value','placeholder','readonly');
			$input .= '<input'.$this->attributes($attrs_list, $params).'/>';
		}

		if (!empty($addon)) {
			$addon = '<span class="input-group-addon">'.$addon.'</span>';
			if (isset($params['icon_pos']) && $params['icon_pos'] == 'right') {
				$input = $input.$addon;
			}else{
				$input = $addon.$input;
			}
			$input = '<div class="input-group '.$params['width'].'">'.$input.'</div>'.PHP_EOL;
		}else{
			$input = '<div class="'.$params['width'].'">'.$input.'</div>'.PHP_EOL;
		}

		$this->form_data[] = array(
			'form' => $label.$input,
			'params' => $params
		);
		return $this;
	}

	public function text($name = false, $params = false) {
		$this->input($name, $params, 'text');
		return $this;
	}

	public function file($name = false, $params = false) {
		$this->input($name, $params, 'file');
		return $this;
	}

	public function password($name = false, $params = false) {
		$this->input($name, $params, 'password');
		return $this;
	}

	public function date($name = false, $params = false) {
		$params['width'] = !empty($params['width']) ? $params['width'] : 3;
		$params['icon'] = isset($params['icon']) ? $params['width'] : 'calendar';
		$params['icon_pos'] = !empty($params['icon_pos']) ? $params['icon_pos'] : 'right';
		$params['class'] = isset($params['class']) ? $params['class'] : 'date_time';
		$params['type'] = !empty($params['type']) ? $params['type'] : 'Y-m-d';
		$params['value'] = !empty($params['value']) ? date($params['type'], $params['value']) : '';

		$this->input($name, $params, 'text');
		return $this;
	}

	public function btn($params = false) {
		$name = !empty($params['name']) ? $params['name'] : 'submit';
		$params['type'] = 'submit';
		$params['class'] = !empty($params['class']) ? 'btn '.$params['class'] : 'btn btn-primary';
		$params['value'] = isset($params['value']) ? $params['value'] : ucfirst($name);
		$params['modal'] = !empty($params['modal']) ? ($params['modal'] == 'close' ? ' data-dismiss="modal"' : ' data-toggle="modal" data-target="#ajaxModal"') : '' ;

		$attrs_list = array('class', 'name', 'modal', 'value', 'type');
		$btn = '<button'.$this->attributes($attrs_list, $params).'>'.$params['value'].'</button>'.PHP_EOL;
		$this->btn_data[] = array(
			'form' => $btn,
			'params' => $params
		);
		return $this;
	}

	public function link($params = false) {
		$params['name'] = !empty($params['name']) ? $params['name'] : 'link';
		$params['class'] = !empty($params['class']) ? 'btn '.$params['class'] : 'btn btn-primary';
		$params['href'] = isset($params['href']) ? $params['href'] : '#';
		$params['modal'] = !empty($params['modal']) ? ($params['modal'] == 'close' ? ' data-dismiss="modal"' : ' data-toggle="modal" data-target="#ajaxModal"') : '' ;

		$attrs_list = array('class','href','modal');
		$btn = '<a'.$this->attributes($attrs_list, $params).'>'.$params['name'].'</a>'.PHP_EOL;
		$this->btn_data[] = array(
			'form' => $btn,
			'params' => $params
		);
		return $this;
	}

	public function separator() {
		$this->form_data[] = array(
			'form' => '&nbsp;',
			'params' => false
		);
		return $this;
	}

	public function hidden($name = false, $value = false) {
		if (empty($name)) {
			return $this;
		}
		$this->form_data[] = array(
			'form' => '<input type="hidden" name="'.$name.'" value="'.$value.'" />',
			'params' => array('type' => 'hidden') 
		);
		return $this;
	}

	public function radio($name = false, $inputs = false, $params = false) {
		if (empty($name) || empty($inputs)) {
			return $this;
		}
		$params['inputs'] = $inputs;
		$this->input($name, $params, 'radio');
		return $this;
	}

	public function textarea($name = false, $params = false) {
		$this->input($name, $params, 'textarea');
		return $this;
	}

	public function create($params = false) {

		$html = '';
		$params['method'] = !empty($params['method']) ? $params['method'] : 'post';
		$params['class'] = !empty($params['class']) ? ' class="'.$params['class'].'" ' : '';

		$params['action'] = !empty($params['action']) ? $params['action'] : '';
		$get_vars = !empty($_GET) ? '?'.http_build_query($_GET) : '';
		$params['action'] = $params['action'].$get_vars;
		$params['upload'] = !empty($params['upload']) ? ' enctype="multipart/form-data"' : false;

		$html .= '<form class="form-horizontal" method="'.$params['method'].'" action="'.$params['action'].'"'.$params['upload'].'>'.PHP_EOL.
			'<div'.$params['class'].'>'.PHP_EOL;
		$html .= !empty($params['title']) ? '<h3>'.$params['title'].'</h3>'.PHP_EOL : '';
		$html .= !empty($params['info']) ? '<p>'.$params['info'].'</p>'.PHP_EOL : '';
		foreach ($this->form_data as $item) {
			if ($item['params']['type'] == 'hidden') {
				$html .= $item['form'].PHP_EOL;
			}else{
				$item['params']['id'] = !empty($item['params']['id']) ? ' id="'.$item['params']['id'].'"' : '';
				$html .= '<div class="form-group'.(!empty($item['params']['error']) ? ' has-error' : '').'"'.$item['params']['id'].'>'.PHP_EOL.
					$item['form'].PHP_EOL.
					'</div>'.PHP_EOL;
			}
		}

		if (!empty($this->btn_data)) {
			$item['params']['id'] = !empty($item['params']['id']) ? ' id="'.$item['params']['id'].'"' : '';
			$html .= '<div class="form-group'.'"'.$item['params']['id'].'>'.PHP_EOL;

			$params['btn_offset'] = isset($params['btn_offset']) ? $params['btn_offset'] : 3;
			$params['class'] = !empty($params['btn_offset']) ? $this->grid_type.'-'.(12 - $params['btn_offset']).' '.$this->grid_type.'-offset-'.$params['btn_offset'] : $this->grid_type.'-12';

			$html .= '<div class="'.$params['class'].'">'.PHP_EOL;;

			foreach ($this->btn_data as $item) {
				$html .= $item['form'].PHP_EOL;
			}
			$html .= '</div></div>'.PHP_EOL;
		}

		$html .= '</div>'.PHP_EOL.'</form>'.PHP_EOL;
		$this->form_data = array();
		$this->btn_data = array();
		return $html;

	}

	public function clear() {
		$this->form_data = array();
		return $this;
	}
}
