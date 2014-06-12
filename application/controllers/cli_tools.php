<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cli_tools extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->input->is_cli_request()) {
			custom_404();
		}

		$this->load->database();
	}

	public function index() {
		return false;
	}

	public function make_zip_media() {
		$result = $this->db
			->select('p.*')
			->from('shop_products as p')
			->join('shop_product_media_files as f', 'p.id = f.product_id')
			->where('p.type', 'media')
			->where('p.zip_date', 0)
			->group_by('p.id')
			->get()
			->result_array();
		if (empty($result)) {
			return false;
		}

		$media_folder = FCPATH.'media_files/';
		foreach ($result as $item) {
			$product_folder = $media_folder.$item['id'].'/';
			shell_exec('cd '.$product_folder.' && zip -r '.$item['id'].'.zip ./ -x "'.$item['id'].'.zip'.'"  > /dev/null 2 > /dev/null &');
			$this->db->where('id', $item['id'])->update('shop_products', array('zip_date' => time()));
		}
	}
}
