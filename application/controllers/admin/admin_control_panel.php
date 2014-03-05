<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_control_panel extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()) {
			redirect($this->admin_url.'auth/login', 'refresh');
		}

		$this->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');

		$this->data['title'] = '4U :: ';
	}

	function index() {
		$this->data['title'] .= 'Админ-панель';
		$this->load->view(ADM_FOLDER.'header', $this->data);
		$this->load->view(ADM_FOLDER.'s_page', $this->data);
		$this->load->view(ADM_FOLDER.'footer', $this->data);
	}

	public function global_settings() {
		$this->data['header']       = 'Настройки сайта';
		$this->data['header_descr'] = 'Глобальные настройки сайта';
		$this->data['title']        = $this->data['header'];

		set_alert($this->session->flashdata('success'), false, 'success');

		$this->load->library('form');
		$this->data['center_block'] = $this->form
			->text('SITE_NAME', array(
				'value'       => (defined('SITE_NAME') ? SITE_NAME : ''),
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Название сайта',
			))
			->text('SITE_DESCR', array(
				'value'       => (defined('SITE_DESCR') ? SITE_DESCR : ''),
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Описание сайта',
			))
			->text('SITE_KEYWORDS', array(
				'value'       => (defined('SITE_KEYWORDS') ? SITE_KEYWORDS : ''),
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Ключевые слова',
			))
			->btn(array('offset' => 3, 'value' => 'Изменить'))
			->create();

		if ($this->form_validation->run() == FALSE) {

			$this->load->view(ADM_FOLDER.'header', $this->data);
			$this->load->view(ADM_FOLDER.'s_page', $this->data);
			$this->load->view(ADM_FOLDER.'footer', $this->data);
		} else {
			$data = $this->input->post();
			$add_sets = '';
			foreach($data as $key => $row) {
				if(strtolower($key) == 'submit') {
					continue;
				}
				$add_sets .= 'define(\''.$key.'\', \''.$row.'\');'."\n";
			}
			$this->load->helper('file');
			$main_sets = '<?php'."\n".$add_sets;
			write_file('./application/config/add_constants.php', $main_sets, 'w+');
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
			redirect(current_url(),'refresh');

		}
	}
}
