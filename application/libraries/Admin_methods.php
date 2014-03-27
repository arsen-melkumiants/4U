<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_methods {

	public $CI;

	public function __construct() {
		$this->CI =& get_instance();
	}

	public function load_admin_views() {
		if ($this->CI->IS_AJAX) {
			$output = $this->CI->load->view(ADM_FOLDER.'ajax', '', true);
			echo $output;
		} else {
			$this->CI->load->view(ADM_FOLDER.'header', $this->CI->data);
			$this->CI->load->view(ADM_FOLDER.'s_page', $this->CI->data);
			$this->CI->load->view(ADM_FOLDER.'footer', $this->CI->data);
		}
	}

	public function admin_constructor() {
		$this->CI->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->CI->data['top_menu'] = $this->CI->admin_control_menu_model->get_control_menu('top');

		$this->CI->IS_AJAX = $this->CI->input->is_ajax_request();

		set_alert($this->CI->session->flashdata('success'), false, 'success');
		set_alert($this->CI->session->flashdata('danger'), false, 'danger');
		set_header_info();
	}

	public function add_method($table = false, $except_fields = false, $add_data = false) {
		if (!empty($table)) {
			$data = !empty($add_data) ? array_merge($data, $this->CI->input->post()) : $this->CI->input->post();
			unset($data['submit']);
			$data['add_date'] = time();
			if (!empty($except_fields) && is_array($except_fields)) {
				foreach ($except_fields as $field) {
					unset($data[$field]);
				}
			}
			$this->CI->db->insert($table, $data);
			$this->CI->session->set_flashdata('success', 'Данные успешно добавлены');
		}
		if ($this->CI->IS_AJAX) {
			echo 'refresh';
		} else {
			redirect($this->CI->MAIN_URL, 'refresh');
		}
	}

	public function edit_method($table = false, $id = false, $except_fields = false, $add_data = false) {
		if (!empty($table) && !empty($id)) {
			$data = !empty($add_data) ? array_merge($data, $this->CI->input->post()) : $this->CI->input->post();
			unset($data['submit']);
			if (!empty($except_fields) && is_array($except_fields)) {
				foreach ($except_fields as $field) {
					unset($data[$field]);
				}
			}
			$this->CI->db->where('id', $id)->update($table, $data);
			$this->CI->session->set_flashdata('success', 'Данные успешно обновлены');
		}

		if ($this->CI->IS_AJAX) {
			echo 'refresh';
		} else {
			redirect(current_url(), 'refresh');
		}
	}

	public function delete_method($table = false, $id = false) {
		if ($this->CI->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->CI->db->where('id', $id)->delete($table);
				$this->CI->session->set_flashdata('danger', 'Данные успешно удалены');
				echo 'refresh';
			} else {
				$this->CI->load->library('form');
				$this->CI->data['center_block'] = $this->CI->form
					->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 4));
				echo $this->CI->load->view(ADM_FOLDER.'ajax', '', true);
			}
		} else {
			$this->CI->db->where('id', $id)->delete($table);
			$this->CI->session->set_flashdata('danger', 'Данные успешно удалены');
			redirect($this->CI->MAIN_URL, 'refresh');
		}
	}
}
