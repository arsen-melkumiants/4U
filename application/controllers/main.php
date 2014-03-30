<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
    }

	public function index()
	{
		$this->data['title'] = 'Main_page';
		
		$this->load->view('header');
		$this->load->view('footer');
	}
}
