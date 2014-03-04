<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_manage_store extends CI_Controller {

	public $ADMIN_FOLDER = 'admin/'; 
	
	public $MAIN_SEGMENT = '/manage_store/';
	
	public $VIEW_URL = '';
	
	public $STATUS = array(
		'0' => array(
			'name'  => 'Неисправно',
			'class' => 'warning'
		),
		'1' => array(
			'name'  => 'Исправно',
			'class' => 'success'
		)
	);
	
	
	function __construct(){
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()){
			redirect('auth/login');
		}
		
		$this->load->model($this->ADMIN_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');
		
		$this->data['title'] = '4U :: ';
		
		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}
	
	public function index(){
		show_404();
	}
	
	public function add_equip(){
		$this->data['header'] = 'Добавление снаряжения';
		$this->data['header_descr'] = 'Добавления прокатного снаряжения на склад';
		$this->data['title'] = $this->data['header'];
		
		$this->load->library('form_creator');
		$this->STATUS[1]['checked'] = 1;
		$this->data['center_block'] = $this->form_creator
			->text('name', array('valid_rules' => 'required|trim|xss_clean', 'label' => 'Название', 'width' => 6))
			->text('number', array('valid_rules' => 'required|trim|xss_clean|is_natural', 'label' => 'Количество', 'width' => 2))
			->radio('status', $this->STATUS, array('label' => 'Состояние снаряжения'))	
			->btn(array('value' => 'Добавить'))
			->create(array('action' => current_url()));
		
		if ($this->form_validation->run() == FALSE){
			$this->load->view($this->VIEW_URL.'header', $this->data);
			$this->load->view($this->VIEW_URL.'s_page', $this->data);
			$this->load->view($this->VIEW_URL.'footer', $this->data);
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$this->db->insert('equipment', $data); 
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			redirect($this->MAIN_SEGMENT.'all_equip', 'refresh');
		}
	}
	
	public function edit_equip($id = false){
		if(!$id){
			redirect($this->MAIN_SEGMENT.'all_equip', 'refresh');
		}
		$equip_info = $this->db->where('id', $id)->get('equipment')->row_array();
		if(empty($equip_info)){
			redirect($this->MAIN_SEGMENT.'all_equip', 'refresh');
		}
		
		$this->data['header'] = 'Редактирование сняряжения "'.$equip_info['name'].'"';
		$this->data['header_descr'] = 'Изменения информации о снаряжении';
		$this->data['title'] = $this->data['header'];
		
		$this->load->library('form_creator');
		$this->STATUS[$equip_info['status']]['checked'] = 1;
		$this->data['center_block'] = $this->form_creator
			->text('name', array('value' => $equip_info['name'], 'valid_rules' => 'required|trim|xss_clean',  'label' => 'Имя', 'width' => 6))
			->text('number', array('value' => $equip_info['number'], 'valid_rules' => 'required|trim|xss_clean|is_natural', 'label' => 'Количество', 'width' => 2))
			->radio('status', $this->STATUS, array('label' => 'Состояние снаряжения'))	
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
		
		if ($this->form_validation->run() == FALSE){
			$this->load->view($this->VIEW_URL.'header', $this->data);
			$this->load->view($this->VIEW_URL.'s_page', $this->data);
			$this->load->view($this->VIEW_URL.'footer', $this->data);
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$this->db->where('id', $id)->update('equipment', $data); 
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
			redirect(current_url(), 'refresh');
		}
	}
	
	public function delete_equip($id = false){
		$id = intval($id);
		if(!$id){
			redirect($this->MAIN_SEGMENT.'all_equip', 'refresh');
		}
		$equip_info = $this->db->where('id', $id)->get('equipment')->row_array();
		if(empty($equip_info)){
			redirect($this->MAIN_SEGMENT.'all_equip', 'refresh');
		}
		
		$this->data['header'] = 'Удаление снаряжения "'.$equip_info['name'].'"';
		
		if(isset($_POST['delete'])){
			$this->db->where('id', $id)->delete('equipment');
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			echo 'refresh';
		}else{
			$this->load->library('form_creator');
			$this->data['center_block'] = $this->form_creator
				->btn(array('value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
				->hidden('delete')
				->create(array('action' => current_url(), 'btn_offset' => 4));
			echo $this->load->view($this->VIEW_URL.'ajax', '', true);
		}
	}
	
	public function all_equip(){
		$this->data['header'] = 'Снаряжение';
		$this->data['header_descr'] = 'Сняряжение для игр';
		$this->data['title'] = $this->data['header'];
		
		$equip_data = $this->db->get('equipment')->result_array();
		
		$this->load->library('table_creator');
		$this->data['center_block'] = $this->table_creator
			->text('name', array('title' => 'Имя', 'p_width' => 70))
			->text('number', array('title' => 'Количество'))
			->text('status', array('title' => 'Статус', 'extra' => $this->STATUS, 'func' => function($row, $params = false){
				return '<span class="label label-'.$params['extra'][$row['status']]['class'].'">'.$params['extra'][$row['status']]['name'].'</span>';
			}))
			->edit(array('link' => $this->MAIN_SEGMENT.'edit_equip/%d'))
			->delete(array('link' => $this->MAIN_SEGMENT.'delete_equip/%d', 'modal' => 1))
			->create($equip_data);
		
		$this->load->view($this->VIEW_URL.'header', $this->data);
		$this->load->view($this->VIEW_URL.'s_page', $this->data);
		$this->load->view($this->VIEW_URL.'footer', $this->data);
	}
	
	public function balls_store(){
		$balls_info = $this->db->where('id', 1)->get('balls_store')->row_array();
		if(empty($balls_info)){
			redirect($this->MAIN_SEGMENT.'all_equip', 'refresh');
		}
		
		$this->data['header'] = 'Игровые шары';
		$this->data['header_descr'] = 'информации о наличии шаров на складе';
		$this->data['title'] = $this->data['header'];
		
		$this->load->library('form_creator');
		$this->data['center_block'] = $this->form_creator
			->text('number', array('value' => $balls_info['number'], 'valid_rules' => 'required|trim|xss_clean',  'label' => 'Количество шаров', 'width' => 2, 'readonly' => 1))
			->text('box', array('value' => round($balls_info['box'], 1, PHP_ROUND_HALF_DOWN), 'valid_rules' => 'required|trim|xss_clean', 'label' => 'Количество упаковок', 'width' => 2, 'readonly' => 1))
			->text('update_balls', array('valid_rules' => 'trim|xss_clean|numeric', 'label' => 'Добавить или отнять (количество шаров)', 'width' => 2))
			->btn(array('value' => 'Обновить'))
			->create(array('action' => current_url()));
		
		if ($this->form_validation->run() == FALSE){
			$this->load->view($this->VIEW_URL.'header', $this->data);
			$this->load->view($this->VIEW_URL.'s_page', $this->data);
			$this->load->view($this->VIEW_URL.'footer', $this->data);
		} else {
			$data = $this->input->post();
			$this->load->model('admin/admin_game_model');
			$this->admin_game_model->update_store($data['update_balls']);
			redirect(current_url(), 'refresh');
		}
	}
	
}
