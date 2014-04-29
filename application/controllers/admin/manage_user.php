<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_user extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'users';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Все пользователи',
			'header_descr' => 'Список пользователей',
		),
		'activated'        => array(
			'header'       => 'Активированные пользователи',
			'header_descr' => 'Список пользователей',
		),
		'inactivated'      => array(
			'header'       => 'Неактивированные пользователи',
			'header_descr' => 'Список пользователей',
		),
		'edit'             => array(
			'header'       => 'Редактирование пользователя "%username"',
			'header_descr' => 'Редактирование информации о пользователе',
		),
	);

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_user_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index($status = false) {
		$this->data['status'] = $status;

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('username', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->date('last_login', array(
				'title' => 'Дата последней авторизации',
			))
			->date('created_on', array(
				'title' => 'Дата регистрации'
			))
			->text('active', array(
				'title' => 'Статус',
				'func'  => function($row, $params, $that, $CI) {
					if ($row['active'] == 0) {
						return '<span class="label label-danger">Неактивированный</span>';
					} elseif ($row['active'] == 1) {
						return '<span class="label label-success">Активированный</span>';
					}
				}
		))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->btn(array(
				'func' => function($row, $params, $html, $that, $CI) {
					if (!$row['status']) {
						$params['title'] = 'Активировать';
						$params['icon'] = 'ok';
					} else {
						$params['title'] = 'Деактивировать';
						$params['icon'] = 'ban-circle';
					}
					return '<a href="'.site_url($CI->MAIN_URL.'active/'.$row['id']).'" title="'.$params['title'].'"><i class="icon-'.$params['icon'].'"></i> </a>';
				}
		))
			->create(function($CI) {
				return $CI->admin_user_model->get_all_users($CI->data['status']);
			});

		load_admin_views();
	}

	public function inactivated() {
		$this->index(0);
	}

	public function activated() {
		$this->index(1);
	}

	public function add() {
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE);
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$user_info = $this->admin_user_model->get_user_info($id);

		if (empty($user_info )) {
			custom_404();
		}
		set_header_info($user_info);

		$this->data['center_block'] = $this->edit_form($user_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($user_info = false) {
		$this->load->library('form');
		return $this->form
			->text('username', array(
				'value'       => $user_info['username'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[150]',
				'label'       => 'Администратор' 
			))
			->text('company', array(
				'value'       => $user_info['company'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Компания'
			))
			->text('address', array(
				'value'       => $user_info['address'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Адрес'
			))
			->text('city', array(
				'value'       => $user_info['city'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Город'
			))
			->text('state', array(
				'value'       => $user_info['state'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Штат/Регион'
			))
			->text('country', array(
				'value'       => $user_info['country'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Страна'
			))
			->text('zip', array(
				'value'       => $user_info['zip'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',
				'label'       => 'Индекс'
			))
			->text('phone', array(
				'value'       => $user_info['phone'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',
				'label'       => 'Phone'
			))
			->btn(array('value' => empty($id) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$user_info = $this->admin_user_model->get_user_info($id);

		if (empty($user_info )) {
			custom_404();
		}
		set_header_info($user_info);

		if (!empty($user_info['id'])) {
			$active = isset($user_info['active']) ? $user_info['active'] : 1;
			$active = abs($active - 1);
			$this->db->where('id', $user_info['id'])->update('users', array('active' => $active));
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
		}
		redirect($this->MAIN_URL, 'refresh');
	}

}
