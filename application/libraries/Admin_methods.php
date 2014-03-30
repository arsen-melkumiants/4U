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

	public function add_method($table = false, $data = false) {
		if (!empty($table)) {
			$info = !empty($data['add_data']) ? array_merge($data['add_data'], $this->CI->input->post()) : $this->CI->input->post();
			unset($data['submit']);
			$data['add_date'] = time();
			if (!empty($data['except_fields']) && is_array($data['except_fields'])) {
				foreach ($data['except_fields'] as $field) {
					unset($data[$field]);
				}
			}

			unset($data['add_data'], $data['except_fields']);
			$this->CI->db->insert($table, $info);
			$this->CI->session->set_flashdata('success', 'Данные успешно добавлены');
		}
		if ($this->CI->IS_AJAX) {
			echo 'refresh';
		} else {
			redirect($this->CI->MAIN_URL, 'refresh');
		}
	}

	public function edit_method($table = false, $data = false) {
		if (!empty($table) && !empty($data['id'])) {
			$info = !empty($data['add_data']) ? array_merge($data['add_data'], $this->CI->input->post()) : $this->CI->input->post();
			unset($data['submit']);
			if (!empty($data['except_fields']) && is_array($data['except_fields'])) {
				foreach ($data['except_fields'] as $field) {
					unset($data[$field]);
				}
			}

			unset($data['add_data'], $data['except_fields']);
			$this->CI->db->where('id', $data['id'])->update($table, $info);
			$this->CI->session->set_flashdata('success', 'Данные успешно обновлены');
		}

		if ($this->CI->IS_AJAX) {
			echo 'refresh';
		} else {
			redirect(current_url(), 'refresh');
		}
	}

	public function delete_method($table = false, $data = false) {
		if (empty($table) || empty($data['id'])) {
			if ($this->CI->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect($this->CI->MAIN_URL, 'refresh');
			}
		}

		if ($this->CI->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->CI->db->where('id', $data['id'])->delete($table);
				$this->CI->session->set_flashdata('danger', 'Удаление успешно выполено');
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
			$this->CI->db->where('id', $data['id'])->delete($table);
			$this->CI->session->set_flashdata('danger', 'Удаление успешно выполено');
			redirect($this->CI->MAIN_URL, 'refresh');
		}
	}

	public function active_method($table = false, $data = false) {
		if (!empty($table) && !empty($data['id'])) {
			$status = isset($data['status']) ? $data['status'] : 1;
			$status = abs($status - 1);
			$this->CI->db->where('id', $data['id'])->update($table, array('status' => $status));
			$this->CI->session->set_flashdata('success', 'Данные успешно обновлены');
		}

		redirect($this->CI->MAIN_URL, 'refresh');
	}
}
