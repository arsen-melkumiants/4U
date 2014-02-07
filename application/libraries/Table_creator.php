<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Table_creator {

	public $table_data = array();

	public $active_data = array();

	public $grid_type = 'col-md';

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
		$params['link'] = !empty($params['link']) ? $params['link'] : '#';
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
		if (empty($this->table_data) ) {
			return false;
		}

		if (empty($rows_data)) {
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

		$this->table_data = array();
		return $html;

	}

	public function clear() {
		$this->table_data = array();
		$this->active_data = array();
		return $this;
	}
}
