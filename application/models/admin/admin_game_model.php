<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_game_model extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function get_games($type = false){
		$today = strtotime(date("Y-m-d", time()));
		$tomorrow = $today + 86400;
		
		if($type == 'future'){
			$this->db->where(array('time >=' => $tomorrow));
		}elseif($type == 'past'){
			$this->db->where(array('time <=' => $today));
		}elseif($type == 'notice'){
			$this->db->where(array('status' => 0, 'time <' => $tomorrow));
		}
		return $this->db->get('games')->result_array();
	}
	
	function update_store($balls_number = false){
		if(empty($balls_number)){
			return true;
		}
		$this->db->trans_start();
		$store_info = $this->db->where('id', 1)->get('balls_store')->row_array();
		
		$data['number'] = $store_info['number'] + $balls_number;
		if($data['number'] < 0){
			$this->session->set_flashdata('danger', 'Недостаточно шаров (в размере '.abs($balls_number).'шт) на складе');
			return false;
		}
		$data['box'] = $data['number'] / BALLS_IN_BOX;
		$this->db->where('id', 1)->update('balls_store', $data);
		$this->session->set_flashdata('success', 'Данные склада успешно обновлены');
		$this->db->trans_complete();
		return true;
	}
}