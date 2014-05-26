<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_special extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'special_content';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'                => 'Информационный контент',
			'header_descr'          => 'Переводы описаний и дополнительной информации',
		),
		'edit'             => array(
			'header'                => 'Редактирование контента',
			'header_descr'          => 'Информация и описание для разных блоков сайта',
		),
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 90
			))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->create(function($CI) {
				return $CI->db->get($CI->DB_TABLE);
			});

		load_admin_views();
	}
	
	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$content_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($content_info)) {
			custom_404();
		}
		set_header_info($content_info);

		$this->data['center_block'] = $this->edit_form($content_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($content_info = false) {
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $content_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Название',
				'readonly'    => 1,
				'width'       => 9,
			))
			->textarea('content_ru', array(
				'value'       => $content_info['content_ru'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Текст(RU)',
			))
			->textarea('content_en', array(
				'value'       => $content_info['content_en'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Текст(EN)',
			))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
	}
}
