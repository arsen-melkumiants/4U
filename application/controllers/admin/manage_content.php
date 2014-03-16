<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_content extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');

		$this->data['title'] = '4U :: ';

		$this->load->model(ADM_FOLDER.'admin_content_model');

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');

		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		$this->IS_AJAX = $this->input->is_ajax_request();
	}

	public function index() {
		$this->data['header']        = 'Контент';
		$this->data['title']        .= $this->data['header'];
		$this->data['header_descr']  = 'Список статических страниц';

		$content_categories = $this->admin_content_model->get_content_categories();

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('cat_id', array(
				'title' => 'Статус',
				'extra' => $content_categories ,
				'func'  => function($row, $params) {
					if (isset($params['extra'][$row['cat_id']]['name'])) {
						return '<span class="label label-info">'.$params['extra'][$row['cat_id']]['name'].'</span>';
					} else {
						return '<span class="label label-warning">Категория отсутствует</span>';
					}
				}
			))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->edit(array('link' => $this->MAIN_URL.'edit_equip/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete_equip/%d', 'modal' => 1))
			->create(function($CI) {
				return $CI->admin_content_model->get_all_content();
			});

		$this->load->view(ADM_FOLDER.'header', $this->data);
		$this->load->view(ADM_FOLDER.'s_page', $this->data);
		$this->load->view(ADM_FOLDER.'footer', $this->data);
	}

	public function add() {
		$content_info = $this->admin_content_model->get_content_categories();
		if (empty($content_info)) {
			custom_404();
		}

		$this->data['header']        = 'Создание меню';
		$this->data['header_descr']  = 'Создание пункта меню';
		$this->data['title']        .= $this->data['header'];

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $_POST['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->load->library('form');
		$this->data['center_block'] = $this->form
			->text('name', array(
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('alias', array(
				'valid_rules' => 'required|trim|xss_clean|is_unique[menu_items.alias]',
				'label'       => 'Ссылка',
			))
			->btn(array('value' => 'Добавить'))
			->create(array('action' => current_url()));

		if ($this->form_validation->run() == FALSE) {
			if ($this->IS_AJAX) {
				$output = $this->load->view(ADM_FOLDER.'ajax', $this->data, true);
				echo $output;
			} else {
				$this->load->view(ADM_FOLDER.'header', $this->data);
				$this->load->view(ADM_FOLDER.'s_page', $this->data);
				$this->load->view(ADM_FOLDER.'footer', $this->data);
			}
		} else {
			$data = $this->input->post();
			$data['menu_id'] = $menu_info['id'];
			unset($data['submit']);
			$this->admin_menu_model->add_menu_item($data);
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect($this->MAIN_URL.$name, 'refresh');
			}
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$menu_info = $this->admin_menu_model->get_one_menu_item($id);

		if (empty($menu_info)) {
			custom_404();
		}

		$this->data['header']        = 'Редактирование "'.$menu_info['name'].'"';
		$this->data['header_descr']  = 'Редактирование пункта меню';
		$this->data['title']        .= $this->data['header'];

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $menu_info['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->load->library('form');
		$this->data['center_block'] = $this->form
			->text('name', array(
				'value'       => $menu_info['name'],
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('alias', array(
				'value'       => $menu_info['alias'],
				'valid_rules' => 'required|trim|xss_clean|is_unique_without[menu_items.alias.'.$id.']',
				'label'       => 'Ссылка',
			))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));

		if ($this->form_validation->run() == FALSE) {
			if ($this->IS_AJAX) {
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
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect(current_url(), 'refresh');
			}
		}
	}

	public function delete($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$menu_info = $this->admin_menu_model->get_one_menu_item($id);

		if (empty($menu_info)) {
			custom_404();
		}

		$this->data['header'] = 'Удаление пункта меню "'.$menu_info['name'].'"';

		if ($this->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->admin_menu_model->delete_menu_item($id);
				$this->session->set_flashdata('danger', 'Данные успешно удалены');
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 4));
				echo $this->load->view(ADM_FOLDER.'ajax', '', true);
			}
		} else {
			$this->admin_menu_model->delete_menu_item($id);
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			$menu_name = $this->admin_menu_model->get_menu_name($menu_info['menu_id']);
			redirect(($menu_name ? $this->MAIN_URL.$menu_name : ADM_URL), 'refresh');
		}
	}
}
