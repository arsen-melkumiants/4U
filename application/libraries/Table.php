<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Table {

	public $table_data  = array();

	public $active_data = array();

	public $grid_type   = 'col-md';

	public $limit_page  = 5;

	public function __construct($grid_type = false) {
		$this->grid_type = $grid_type ? $grid_type : $this->grid_type;
	}

	public function __call($method, $arguments) {
		if (!method_exists( $this, $method)) {
			throw new Exception('Undefined method ' . $method . '() called');
		}

		return call_user_func_array(array($this, $method), $arguments);
	}

	public function text($name = false, $params = false) {
		if (!$name) {
			return $this;
		}
		$params['width'] = !empty($params['width']) ? $params['width'] : '';
		$params['width'] = ' width="'.(!empty($params['p_width']) ? $params['p_width'].'%' : $params['width']).'"';
		$this->table_data[] = array(
			'name'   => $name,
			'params' => $params
		);

		return $this;
	}

	public function date($name = false, $params = false) {
		$params['type'] = !empty($params['type']) ? $params['type'] : 'Y-m-d H:i';
		$params['date'] = true;
		$this->text($name, $params);
		return $this;
	}

	public function btn($params = false) {
		$params['name'] = !empty($params['name']) ? ucfirst($params['name']) : '';
		$params['class'] = 'class="'.(!empty($params['class']) ? $params['class']: 'btn-default').'"';
		$params['icon'] = !empty($params['icon']) ? '<i class="icon-'.$params['icon'].'"></i> ' : '';
		$params['title'] = !empty($params['title']) ? ' title="'.$params['title'].'"' : '';
		$params['link'] = !empty($params['link']) ? site_url($params['link']) : '#';
		$params['modal'] = !empty($params['modal']) ? ' data-toggle="modal" data-target="#ajaxModal"' : '';

		$this->active_data[] = array(
			'html' => '<a href="'.$params['link'].'" '.$params['class'].$params['title'].$params['modal'].'>'.$params['icon'].$params['name'].'</a>',
			'params' => $params
		);

		return $this;
	}

	public function edit($params = false) {
		$params['title'] = !empty($params['title']) ? $params['title'] : 'Редактировать';
		$params['icon'] = !empty($params['icon']) ? $params['icon'] : 'edit';
		$this->btn($params);
		return $this;
	}

	public function delete($params = false) {
		$params['title'] = !empty($params['title']) ? $params['title'] : 'Удалить';
		$params['icon'] = isset($params['icon']) ? $params['icon'] : 'trash';
		$params['class'] = isset($params['class']) ? $params['class'] : '';
		$this->btn($params);
		return $this;
	}

	public function create($rows_data = false, $table_params = false) {
		if (empty($this->table_data)) {
			return false;
		}

		if (is_callable($rows_data)) {
			$sql_result = $this->sql_construct($rows_data);
			if (empty($sql_result['items'])) {
				return false;
			} else {
				$rows_data = $sql_result['items'];
			}
		}

		if (empty($rows_data) || is_string($rows_data)) {
			return $html = '<div class="alert alert-info">Записи отсутствуют</div>';
		}

		$table_params['class'] = !empty($table_params['class']) ? $table_params['class'] : 'table table-bordered table-hover';

		$html = '<table class="'.$table_params['class'].'">'."\n";
		$html .= '<tr>'."\n";

		foreach ((array)$this->table_data as $item) {
			if (!isset($rows_data[0][$item['name']])) {
				continue;
			}
			$title = !empty($item['params']['title']) ? $item['params']['title'] : ucfirst($item['name']);
			$html .= '<th'.$item['params']['width'].'>'.$title.'</th>'."\n";
		}
		if (!empty($this->active_data)) {
			$html .= '<th>Действия</th>'."\n";
		}
		$html .= '</tr>';

		foreach ((array)$rows_data as $row) {
			$html .= '<tr>';
			foreach ((array)$this->table_data as $item) {
				if (!isset($row[$item['name']])) {
					continue;
				}
				if (isset($item['params']['date'])) {
					$row[$item['name']] = date($item['params']['type'], $row[$item['name']]);
				}

				$row[$item['name']] = isset($item['params']['func']) ? $item['params']['func']($row, $item['params']) : $row[$item['name']];

				$html .= '<td>'.$row[$item['name']].'</td>';
			}

			if (!empty($this->active_data)) {
				$html .= '<td class="active_block">';
				foreach ($this->active_data as $item) {
					if (!empty($row['id'])) {
						$html .= sprintf($item['html'], $row['id']);
					}else{
						$html .= $item['html'];
					}
				}
				$html .= '</td>';
			}
			$html .= '</tr>';
		}

		$html .= '</table>';

		if(!empty($sql_result['pages'])){
			$html .= $sql_result['pages'];
		}

		$this->table_data = array();
		return $html;

	}

	public function clear() {
		$this->table_data = array();
		$this->active_data = array();
		return $this;
	}

	public function sql_construct($sql) {
		$CI =& get_instance();
		$CI->load->database();
		$limit = !empty($table_params['limit']) ? $table_params['limit'] : $this->limit_page;
		if (intval($limit)) {
			$sql_all = $sql($CI);
			if (isset($sql_all->conn_id)) {
				$result['pages'] = $this->pagination($sql_all->num_rows(), $limit);
			}
			$offset = isset($_GET['page']) && intval($_GET['page']) > 1 ? (intval($_GET['page']) - 1) * $limit : 0;
			$CI->db->limit($limit, $offset);
		}

		$sql = $sql($CI);
		$result['items'] = is_string($sql) ? $CI->query($sql)->result_array() : $sql->result_array();

		return $result;
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
		$text = '<ul class="pagination">';
		if ($cur_page < 2) {
			$text .= '<li class="disabled"><a>«</a></li>';
			$text .= '<li class="disabled"><a>‹</a></li>';
		} else {
			$text .= '<li><a href="?page=1">«</a></li>';
			$text .= '<li><a href="?page='.($cur_page-1).'">‹</a></li>';
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
			$text .= '<li class="disabled"><a>›</a></li>';
			$text .= '<li class="disabled"><a>»</a></li>';
		}else{
			$text .= '<li><a href="?page='.($cur_page+1).'">›</a></li>';
			$text .= '<li><a href="?page='.$pages.'">»</a></li>';
		}
		$text .= '</ul>';

		return $text;
	}
}
