<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_menu extends CI_Controller {

	public $MAIN_URL = '';

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');

		$this->data['title'] = '4U :: ';

		$this->load->model(ADM_FOLDER.'admin_menu_model');

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');

		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
	}

	public function index() {
		show_404();
		$this->data['title'] .= 'Админ-панель';
		$this->load->view(ADM_FOLDER.'header', $this->data);
		$this->load->view(ADM_FOLDER.'s_page', $this->data);
		$this->load->view(ADM_FOLDER.'footer', $this->data);
	}

	public function menu($name = false) {
		if ($this->input->is_ajax_request() && !empty($_POST['tree'])) {
			$this->admin_menu_model->update_menu_tree($_POST['tree'], $name);
			exit;
		}
		$menu_items = $this->admin_menu_model->get_menu_items($name);
		if (empty($menu_items)) {
			show_404();
		}

		$this->data['header'] = '"'.$menu_items[0]['ru_name'].'" меню';
		$this->data['title'] .= $this->data['header'];
		$this->data['header_descr'] = 'Список пунктов меню';
		$this->data['center_block'] = $this->admin_menu_model->get_menu_tree($menu_items, 0, $this->MAIN_URL);

		$this->load->view(ADM_FOLDER.'header', $this->data);
		$this->load->view(ADM_FOLDER.'s_page', $this->data);
		$this->load->view(ADM_FOLDER.'footer', $this->data);
	}

	public function add() {
		$this->data['header'] = 'Создание группы';
		$this->data['header_descr'] = 'Планирование события-игры';
		$this->data['title'] = $this->data['header'];

		$this->load->library('form_creator');
		$this->data['center_block'] = $this->form_creator
			->text('name', array('valid_rules' => 'required|trim|xss_clean',  'label' => 'Имя'))
			->text('phone', array('valid_rules' => 'required|trim|xss_clean', 'label' => 'Контактный телефон'))
			->text('gamers_number', array('valid_rules' => 'required|trim|is_natural', 'label' => 'Количество игроков', 'width' => 1))
			->text('balls_number', array('valid_rules' => 'required|trim|is_natural', 'label' => 'Количество шаров(по 100шт)', 'width' => 1))
			->date('time', array('value' => (isset($_GET['time']) && intval($_GET['time']) ? intval($_GET['time']) : ''), 'valid_rules' => 'required|trim', 'label' => 'Дата события', 'readonly' => 1))
			->btn(array('value' => 'Создать'))
			->create(array('action' => current_url()));

		if ($this->form_validation->run() == FALSE) {
			if ($this->input->is_ajax_request()) {
				$output = $this->load->view(ADM_FOLDER.'ajax', '', true);
				echo $output;
			} else {
				$this->load->view(ADM_FOLDER.'header', $this->data);
				$this->load->view(ADM_FOLDER.'s_page', $this->data);
				$this->load->view(ADM_FOLDER.'footer', $this->data);
			}
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$this->db->insert('games', $data); 
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			if ($this->input->is_ajax_request()) {
				echo 'refresh';
			} else {
				redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
			}
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			show_404();
		}
		$menu_info = $this->admin_menu_model->get_one_menu_item($id);

		if (empty($menu_info)) {
			show_404();
		}

		$this->data['header'] = 'Редактирование "'.$menu_info['name'].'"';
		$this->data['header_descr'] = 'Редактирование пункта меню';
		$this->data['title'] .= $this->data['header'];

		$this->load->library('form');
		$this->data['center_block'] = $this->form
			->text('name', array('value' => $menu_info['name'], 'valid_rules' => 'required|trim|xss_clean',  'label' => 'Имя'))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));

		if ($this->form_validation->run() == FALSE) {
			if ($this->input->is_ajax_request()) {
				$output = $this->load->view(ADM_FOLDER.'ajax', '', true);
				echo $output;
			} else {
				$this->load->view(ADM_FOLDER.'header', $this->data);
				$this->load->view(ADM_FOLDER.'s_page', $this->data);
				$this->load->view(ADM_FOLDER.'footer', $this->data);
			}
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$menu_info = $this->admin_menu_model->update_menu_item($data, $id);
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
			if ($this->input->is_ajax_request()) {
				echo 'refresh';
			} else {
				redirect(current_url(), 'refresh');
			}
		}
	}

	public function delete($id = false) {
		$id = intval($id);
		if (!$id) {
			redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
		}
		$game_info = $this->db->where('id', $id)->get('games')->row_array();
		if (empty($game_info)) {
			redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
		}

		$this->data['header'] = 'Удаление группы "'.$game_info['name'].'"';

		if (isset($_POST['delete'])) {
			$this->db->where('id', $id)->delete('games');
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			echo 'refresh';
		} else {
			$this->load->library('form_creator');
			$this->data['center_block'] = $this->form_creator
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			echo $this->load->view(ADM_FOLDER.'ajax', '', true);
		}
	}
}
