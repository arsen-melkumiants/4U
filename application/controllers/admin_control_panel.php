<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_control_panel extends CI_Controller {

	public $ADMIN_FOLDER = 'admin/'; 
	
	public $VIEW_URL = '';
	
	function __construct(){
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()){
			redirect('auth/login', 'refresh');
		}
		
		$this->load->model($this->ADMIN_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');
		
		$this->data['title'] = '4U :: ';
	}
	
	function index(){
		$this->data['title'] .= 'Админ-панель';
		$this->load->view('header', $this->data);
		$this->load->view('s_page', $this->data);
		$this->load->view('footer', $this->data);
	}
	
	public function global_settings()
	{
		$this->data['header'] = 'Настройки сайта';
		$this->data['header_descr'] = 'Глобальные настройки сайта';
		$this->data['title'] = $this->data['header'];
		
		set_alert($this->session->flashdata('success'), false, 'success');
		
		$this->load->library('form_creator');
		$this->data['center_block'] = $this->form_creator
			->text('SITE_NAME', array('value' => (defined('SITE_NAME') ? SITE_NAME : ''), 'valid_rules' => 'required|trim|xss_clean',  'label' => 'Название сайта'))
			->text('SITE_DESCR', array('value' => (defined('SITE_DESCR') ? SITE_DESCR : ''), 'valid_rules' => 'required|trim|xss_clean', 'label' => 'Описание сайта'))
			->text('EQUIP_PRICE', array('value' => (defined('EQUIP_PRICE') ? EQUIP_PRICE : ''), 'valid_rules' => 'required|trim|xss_clean|is_natural', 'label' => 'Цена проката снаряжения', 'symbol' => '&#8372;', 'width' => '2'))
			->text('BALL_PRICE', array('value' => (defined('BALL_PRICE') ? BALL_PRICE : ''), 'valid_rules' => 'required|trim|xss_clean|is_natural', 'label' => 'Цена шаров (100шт)', 'symbol' => '&#8372;', 'width' => '2'))
			->text('RAW_BALL_PRICE', array('value' => (defined('RAW_BALL_PRICE') ? RAW_BALL_PRICE : ''), 'valid_rules' => 'required|trim|xss_clean|is_natural', 'label' => 'Закупочная цена шаров (100шт)', 'symbol' => '&#8372;', 'width' => '2'))
			->text('BALLS_IN_BOX', array('value' => (defined('BALLS_IN_BOX') ? BALLS_IN_BOX : ''), 'valid_rules' => 'required|trim|xss_clean|is_natural', 'label' => 'Количество шаров в упаковке', 'width' => '2'))
			->btn(array('offset' => 3, 'value' => 'Изменить'))
			->create();
		
		if ($this->form_validation->run() == FALSE){
			
			$this->load->view($this->VIEW_URL.'header', $this->data);
			$this->load->view($this->VIEW_URL.'s_page', $this->data);
			$this->load->view($this->VIEW_URL.'footer', $this->data);
		} else {
			$data = $this->input->post();
			$add_sets = '';
			foreach($data as $key => $row){
				if(strtolower($key) == 'submit'){
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
