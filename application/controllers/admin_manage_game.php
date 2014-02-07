<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_manage_game extends CI_Controller {

	public $ADMIN_FOLDER = 'admin/'; 
	
	public $MAIN_SEGMENT = '/manage_game/';
	
	public $VIEW_URL = '';
	
	public $STATUS = array(
		'0' => array(
			'name'  => 'В процессе',
			'class' => 'warning'
		),
		'1' => array(
			'name'  => 'Завершена',
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
		
		$this->data['title'] .= 'Админ-панель';
		$this->load->view('header', $this->data);
		$this->load->view('s_page', $this->data);
		$this->load->view('footer', $this->data);
	}
	
	/*function control_method($library = false, $method = false, $param1 = false, $param2 = false, $param3 = false){
		if(!file_exists(APPPATH.'library/'.$this->ADMIN_FOLDER.$library.'.php')){
			//show_404();
			//return false;
		}
		
		//$this->load->library($library);
		
	}*/
	
	public function add(){
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
		
		if ($this->form_validation->run() == FALSE){
			if(!empty($_GET['ajax'])){
				$output = $this->load->view($this->VIEW_URL.'ajax', '', true);
				echo $output;
			}else{
				$this->load->view($this->VIEW_URL.'header', $this->data);
				$this->load->view($this->VIEW_URL.'s_page', $this->data);
				$this->load->view($this->VIEW_URL.'footer', $this->data);
			}
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$data['time'] = strtotime($data['time']);
			$this->db->insert('games', $data); 
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			if(!empty($_GET['ajax'])){
				echo 'refresh';
			}else{
				redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
			}
		}
	}
	
	public function edit($id = false){
		if(!$id){
			redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
		}
		$game_info = $this->db->where('id', $id)->get('games')->row_array();
		if(empty($game_info)){
			redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
		}
		
		$this->data['header'] = 'Редактирование группы "'.$game_info['name'].'"';
		$this->data['header_descr'] = 'Редактирование события-игры';
		$this->data['title'] = $this->data['header'];
		
		$this->load->library('form_creator');
		$this->data['center_block'] = $this->form_creator
			->text('name', array('value' => $game_info['name'], 'valid_rules' => 'required|trim|xss_clean',  'label' => 'Имя'))
			->text('phone', array('value' => $game_info['phone'], 'valid_rules' => 'required|trim|xss_clean', 'label' => 'Контактный телефон'))
			->text('gamers_number', array('value' => $game_info['gamers_number'], 'valid_rules' => 'required|trim|is_natural', 'label' => 'Количество игроков', 'width' => 1))
			->text('balls_number', array('value' => $game_info['balls_number'], 'valid_rules' => 'required|trim|is_natural', 'label' => 'Количество шаров(по 100шт)', 'width' => 2))
			->date('time', array('value' => $game_info['time'], 'valid_rules' => 'required|trim', 'label' => 'Дата события', 'readonly' => 1))
			->text('balls_profit', array('value' => $game_info['balls_profit'], 'valid_rules' => 'trim|xss_clean', 'label' => 'Доход от шаров(в расчёте шаров: '.$game_info['calc_balls_number'].' х 100шт)', 'symbol' => '&#8372;', 'width' => '2', 'readonly' => 1))
			->text('equip_profit', array('value' => $game_info['equip_profit'], 'valid_rules' => 'trim|xss_clean', 'label' => 'Доход от снаряжения(в расчёте игроков: '.$game_info['calc_gamers_number'].')', 'symbol' => '&#8372;', 'width' => '2', 'readonly' => 1))
			->text('total', array('value' => $game_info['total'], 'valid_rules' => 'trim|xss_clean', 'label' => 'Итоговая прибыль', 'symbol' => '&#8372;', 'width' => '2', 'readonly' => 1))
			->text('clear_total', array('value' => $game_info['clear_total'], 'valid_rules' => 'trim|xss_clean', 'label' => 'Чистая прибыль', 'symbol' => '&#8372;', 'width' => '2', 'readonly' => 1))
			->btn(array('value' => 'Изменить'))
			->btn(array('name' => 'calc', 'value' => 'Подсчитать', 'class' => 'btn-success'))
			->create(array('action' => current_url()));
		
		if ($this->form_validation->run() == FALSE){
			if(!empty($_GET['ajax'])){
				$output = $this->load->view($this->VIEW_URL.'ajax', '', true);
				echo $output;
			}else{
				$this->load->view($this->VIEW_URL.'header', $this->data);
				$this->load->view($this->VIEW_URL.'s_page', $this->data);
				$this->load->view($this->VIEW_URL.'footer', $this->data);
			}
		} else {
			$data = $this->input->post();
			$data['time'] = strtotime($data['time']);
			unset($data['submit']);
			if(isset($data['calc'])){
				unset($data['calc']);
				//balls store
				$balls = ($game_info['calc_balls_number'] - $data['balls_number']) * 100;
				//profit
				$data['calc_balls_number'] = $data['balls_number'];
				$data['calc_gamers_number'] = $data['gamers_number'];
				$data['equip_profit'] = $data['gamers_number'] * EQUIP_PRICE;
				$data['balls_profit'] = $data['balls_number'] * BALL_PRICE;
				$data['total'] = $data['equip_profit'] + $data['balls_profit'];
				$data['clear_total'] = $data['equip_profit'] + $data['balls_profit'] - ($data['balls_number'] * RAW_BALL_PRICE);
				$data['status'] = 1;
				$this->load->model('admin/admin_game_model');
				if($this->admin_game_model->update_store($balls)){
					$this->db->where('id', $id)->update('games', $data); 
				}
			}else{
				unset($data['calc_balls_number'], $data['total'], $data['equip_profit'], $data['balls_profit'], $data['clear_total'], $data['status']);
				$this->db->where('id', $id)->update('games', $data); 
				$this->session->set_flashdata('success', 'Данные успешно обновлены');
			}
			
			if(!empty($_GET['ajax'])){
				echo 'refresh';
			}else{
				redirect(current_url(), 'refresh');
			}
		}
	}
	
	public function delete($id = false){
		$id = intval($id);
		if(!$id){
			redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
		}
		$game_info = $this->db->where('id', $id)->get('games')->row_array();
		if(empty($game_info)){
			redirect($this->MAIN_SEGMENT.'all/future', 'refresh');
		}
		
		$this->data['header'] = 'Удаление группы "'.$game_info['name'].'"';
		
		if(isset($_POST['delete'])){
			$this->db->where('id', $id)->delete('games');
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			echo 'refresh';
		}else{
			$this->load->library('form_creator');
			$this->data['center_block'] = $this->form_creator
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			echo $this->load->view($this->VIEW_URL.'ajax', '', true);
		}
	}
	
	public function all($type = 'future'){
		if($type == 'notice'){
			$this->data['header'] = 'Напоминания';
			$this->data['header_descr'] = 'Незакрытые события-игры';
		}elseif ($type == 'past') {
			$this->data['header'] = 'Прошлые группы';
			$this->data['header_descr'] = 'Запланированные события-игры';
		}else{
			$this->data['header'] = 'Будущие группы';
			$this->data['header_descr'] = 'Запланированные события-игры';
		}
		$this->data['title'] = $this->data['header'];

		$this->load->model('admin/admin_game_model');
		$game_data = $this->admin_game_model->get_games($type);
		
		$this->load->library('table_creator');
		$this->table_creator
			->text('id', array('title' => 'Номер'))
			->text('name', array('title' => 'Имя'))
			->text('phone', array('title' => 'Контактный телефон'))
			->text('gamers_number', array('title' => 'Количество игроков'))
			->text('balls_number', array('title' => 'Количество шаров'))
			->date('time', array('title' => 'Дата события'))
			->text('status', array('title' => 'Статус', 'extra' => $this->STATUS, 'func' => function($row, $params = false){
				return '<span class="label label-'.$params['extra'][$row['status']]['class'].'">'.$params['extra'][$row['status']]['name'].'</span>';
			}))
			->edit(array('link' => $this->MAIN_SEGMENT.'edit/%d'))
			->delete(array('link' => $this->MAIN_SEGMENT.'delete/%d', 'modal' => 1));
			if ($type == 'past') {
				$this->table_creator->text('clear_total', array('title' => 'Чистая прибыль'));
			}
			$this->data['center_block'] = $this->table_creator->create($game_data);
		
		$this->load->view($this->VIEW_URL.'header', $this->data);
		$this->load->view($this->VIEW_URL.'s_page', $this->data);
		$this->load->view($this->VIEW_URL.'footer', $this->data);
	}
	
	function calendar(){
		$this->data['header'] = 'Календарь';
		$this->data['header_descr'] = 'Запланированные события-игры';
		$this->data['title'] = $this->data['header'];
		
		$this->load->model('admin/admin_game_model');
		
		$this->load->view($this->VIEW_URL.'header', $this->data);
		$this->load->view($this->VIEW_URL.'calendar', $this->data);
		$this->load->view($this->VIEW_URL.'footer', $this->data);
	}
	
	
	function json_events(){
		$this->load->model('admin/admin_game_model');
		$game_data = $this->admin_game_model->get_games();
		if(empty($game_data)){
			return false;
		}
		foreach($game_data as $key => $item){
			$output[$key] = array(
				'id'    => $item['id'],
				'title' => $item['name'],
				'start' => date('Y-m-d H:i', $item['time']),
				//'end' => date('Y-m-d H:i', $item['time'] + 1260),
				'allDay' => false,
				//'status'=> $item['status']
			);
			if($item['status'] == 1){
				$output[$key]['className'] = 'alert-success';
			}elseif($item['time'] > time()){
				$output[$key]['className'] = 'alert-info';
			}else{
				$output[$key]['className'] = 'alert-danger';
			}
			
		}
		echo json_encode($output);
	}

}
