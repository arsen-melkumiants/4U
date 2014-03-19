<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_content extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Контент',
			'header_descr' => 'Список статических страниц',
		),
		'add'              => array(
			'header'       => 'Добавления контента',
			'header_descr' => 'Создание статической страницы',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование контента',
		),
		'delete'           => array(
			'header'       => 'Удаление контента "%name"',
			'header_descr' => false,
		),
		'category'         => array(
			'header'       => 'Список категорий',
			'header_descr' => 'Список категорий контента',
		),
		'add_category'     => array(
			'header'       => 'Добавления категории',
			'header_descr' => 'Создание категории для статических страницы',
		),
		'edit_category'    => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование категории контента',
		),
		'delete_category'  => array(
			'header'       => 'Удаление контента "%name"',
			'header_descr' => false,
		),
	);

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');
		$this->load->model(ADM_FOLDER.'admin_content_model');
		
		$this->data['title'] = '4U :: ';

		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		$this->IS_AJAX = $this->input->is_ajax_request();
		
		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
		set_header_info();
	}

	public function index() {
		$content_categories = $this->admin_content_model->get_content_categories();

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('cat_id', array(
				'title' => 'Категория',
				'extra' => $content_categories ,
				'func'  => function($row, $params) {
					if (isset($params['extra'][$row['cat_id']]['name'])) {
						return '<span class="label label-info">'.$params['extra'][$row['cat_id']]['name'].'</span>';
					} else {
						return '<span class="label label-warning">Отсутствует</span>';
					}
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
			->btn(array(
				'link' => $this->MAIN_URL.'add',
				'name' => 'Добавить',
				'header' => true,
				'footer' => true,
			))
			->create(function($CI) {
				return $CI->admin_content_model->get_all_content();
			});

		load_admin_views();
	}

	public function add() {
		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $_POST['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$data['add_date'] = time();
			$this->admin_content_model->add_content($data);
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect($this->MAIN_URL, 'refresh');
			}
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$content_info = $this->admin_content_model->get_content_info($id);

		if (empty($content_info )) {
			custom_404();
		}
		set_header_info($content_info);

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $content_info['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_form($content_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$content_info = $this->admin_content_model->update_content($data, $id);
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect(current_url(), 'refresh');
			}
		}
	}

	private function edit_form($content_info = false) {
		$content_categories = $this->admin_content_model->get_content_categories();
		array_unshift($content_categories, array('id' => 0, 'name' => 'Без категории'));
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $content_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('alias', array(
				'value'       => $content_info['alias'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|'.(!$content_info['id'] ? 'is_unique[content.alias]' : 'is_unique_without[content.alias.'.$content_info['id'].']'),
				'label'       => 'Ссылка',
			))
			->select('cat_id', array(
				'value'       => $content_info['cat_id'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Категория',
				'options'     => $content_categories,
				'search'      => true,
			))
			->textarea('content', array(
				'value'       => $content_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Текст',
			))
			->text('keywords', array(
				'value'       => $content_info['keywords'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Ключевые слова',
			))
			->text('title', array(
				'value'       => $content_info['title'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Заголовок страницы',
			))
			->text('description', array(
				'value'       => $content_info['description'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Описание',
			))
			->btn(array('value' => empty($id) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function delete($id = false, $type = false) {
		if (empty($id)) {
			custom_404();
		}
		
		if ($type == 'category') {
			$get_method    = 'get_content_category_info';
			$delete_method = 'delete_content_category';
		} else {
			$get_method    = 'get_content_info';
			$delete_method = 'delete_content';
		}

		$content_info = $this->admin_content_model->$get_method($id);

		if (empty($content_info)) {
			custom_404();
		}
		set_header_info($content_info);

		if ($this->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->admin_content_model->$delete_method($id);
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
			$this->admin_content_model->$delete_method($id);
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			redirect($this->MAIN_URL, 'refresh');
		}
	}

	function delete_category($id) {
		$this->delete($id, 'category');
	}

	function category() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('alias', array(
				'title' => 'Ссылка',
			))
			->edit(array('link' => $this->MAIN_URL.'edit_category/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete_category/%d', 'modal' => 1))
			->btn(array(
				'link'   => $this->MAIN_URL.'add_category',
				'name'   => 'Добавить',
				'header' => true,
				'footer' => true,
			))
			->create(function($CI) {
				return $CI->admin_content_model->get_content_categories(true);
			});

		load_admin_views();
	}

	public function edit_category($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$category_info = $this->admin_content_model->get_content_category_info($id);

		if (empty($category_info)) {
			custom_404();
		}
		set_header_info($category_info);

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $category_info['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_category_form($category_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$category_info = $this->admin_content_model->update_content_category($data, $id);
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect(current_url(), 'refresh');
			}
		}
	}

	public function add_category() {
		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $_POST['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_category_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$this->admin_content_model->add_content_category($data);
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect($this->MAIN_URL.'category', 'refresh');
			}
		}
	}

	private function edit_category_form($category_info = false) {
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $category_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('alias', array(
				'value'       => $category_info['alias'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|'.(!$category_info['id'] ? 'is_unique[content_categories.alias]' : 'is_unique_without[content_categories.alias.'.$category_info['id'].']'),
				'label'       => 'Ссылка',
			))
			->btn(array('value' => empty($id) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url()));
	}
}
