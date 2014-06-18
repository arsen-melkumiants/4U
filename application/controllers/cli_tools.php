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
			$archive_name = $item['id'].'-'.url_title(translitIt($item['name']), 'underscore', TRUE).'.zip';
			foreach (glob($product_folder.$item['id'].'-*.zip') as $exist_file) {
				if(basename($exist_file) != $archive_name) {
					unlink($exist_file);
				}
			}
			$run = 'cd '.$product_folder.' && /usr/local/bin/zip -FSr '.$archive_name.' ./ -x "'.$archive_name.'" > /dev/null &';
			shell_exec($run);
			$this->db->where('id', $item['id'])->update('shop_products', array('zip_date' => time()));
		}
	}
}
