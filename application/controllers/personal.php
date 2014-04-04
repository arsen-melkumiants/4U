<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Personal extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array(
			'ion_auth',
			'form',
			'form_validation',
		));
		$this->load->helper('url');

		// Load MongoDB library instead of native db driver if required
		$this->config->item('use_mongodb', 'ion_auth') ?
			$this->load->library('mongo_db') :
			$this->load->database();

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
		$this->load->helper('language');

		$this->load->model(array(
			'menu_model',
			'shop_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		$this->data['left_block'] = $this->shop_model->get_categories();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		return false;
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login', 'refresh');
		} elseif (!$this->ion_auth->is_admin())	{
			return show_error('You must be an administrator to view this page.');
		} else {
			redirect('', 'refresh');
		}
	}

	//log the user in
	function login() {
		if ($this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('', 'refresh');
		}

		$this->data['title'] = $this->data['header'] = "Login";

		$this->form
			->text('identity', array(
				'label' => 'Логин или Email:',
				'valid_rules' => 'required|xss'
			))
			->password('password', array(
				'label' => 'Пароль:',
				'valid_rules' => 'required|xss'
			))
			->btn(array(
				'value' => $this->lang->line('login_submit_btn'),
			));

		if ($this->form_validation->run() == true) {
			$remember = (bool) $this->input->post('remember');
			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				if ($this->input->is_ajax_request()) {
					echo 'refresh';exit;
				}
				redirect('', 'refresh');
			} else {
				//$this->session->set_flashdata('danger', $this->ion_auth->errors());
				$this->form->form_data[0]['params']['error'] = $this->ion_auth->errors();
				$this->data['center_block'] = $this->form->create(array('action' => current_url(), 'error_inline' => 'true'));
				load_views();	
			}
		} else {
			$this->data['center_block'] = $this->form->create(array('action' => current_url(), 'error_inline' => 'true'));
			load_views();	
		}
	}

	//log the user out
	function logout()
	{
		$this->data['title'] = "Logout";
		$logout = $this->ion_auth->logout();
		$this->session->set_flashdata('success', $this->ion_auth->messages());
		redirect('', 'refresh');
	}

	//activate the user
	function activate($id, $code=false)
	{
		if ($code !== false) {
			$activation = $this->ion_auth->activate($id, $code);
		} else if ($this->ion_auth->is_admin())	{
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation) {
			//redirect them to the auth page
			$this->session->set_flashdata('success', $this->ion_auth->messages());
			redirect("", 'refresh');
		} else {
			//redirect them to the forgot password page
			$this->session->set_flashdata('danger', $this->ion_auth->errors());
			redirect('personal/forgot_password', 'refresh');
		}
	}

	function registration()	{
		$this->data['title'] = $this->data['header'] = "Create User";

		if ($this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('', 'refresh');
		}

		$this->data['center_block'] = $this->form
			->text('username', array('valid_rules' => 'required|trim|xss_clean|max_length[150]',  'label' => $this->lang->line('create_user_validation_fname_label')))
			->text('email', array('valid_rules' => 'required|trim|xss_clean|max_length[150]|is_unique[users.email]',  'label' => $this->lang->line('create_user_validation_email_label')))
			->password('password', array('valid_rules' => 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']',  'label' => $this->lang->line('create_user_validation_password_label')))
			->password('password_confirm', array('valid_rules' => 'required|matches[password]',  'label' => $this->lang->line('create_user_validation_password_confirm_label')))
			->text('company', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'Company'))
			->text('address', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'Address'))
			->text('city', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'City'))
			->text('state', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'State'))
			->text('country', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'Country'))
			->text('zip', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'Zip'))
			->text('phone', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => 'Phone'))
			->btn(array('value' => 'Регистрироваться'))
			->create(array('action' => current_url(), 'error_inline' => 'true'));

		if ($this->form_validation->run() == true) {
			$username = $this->input->post('username');
			$email    = strtolower($this->input->post('email'));
			$password = $this->input->post('password');

			$additional_data = $this->input->post();
			unset(
				$additional_data['username'],
				$additional_data['email'],
				$additional_data['password'],
				$additional_data['password_confirm']
			);
		}

		if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("", 'refresh');
		} else {
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			load_views();	
		}
	}

	function forgot_password() {
		$this->data['header'] = $this->data['title'] = lang('forgot_password_heading');
		
		if ($this->config->item('identity', 'ion_auth') == 'username') { 
			$label = 'forgot_password_username_identity_label';
			$email_rule = '';
		} else {
			$label = 'forgot_password_email_identity_label';
			$email_rule = '|valid_email';
		}
		$label = $this->lang->line($label);
		$this->form
			->text('email', array(
				'valid_rules' => 'required|trim|xss_clean'.$email_rule, 
				'label' => $label
			))
			->btn(array('value' => lang('forgot_password_submit_btn')));
		
		if ($this->form_validation->run() == false) {
			$this->form->form_data[0]['params']['error'] = $this->session->flashdata('message');
			$this->data['center_block'] = $this->form
				->create(array('action' => current_url(), 'error_inline' => 'true'));
			load_views();	
		} else {
			$identity = $this->ion_auth->where('email', strtolower($this->input->post('email')))->users()->row();
			if(empty($identity)) {
				$this->ion_auth->set_message('forgot_password_email_not_found');
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect('personal/forgot_password', 'refresh');
			}

			//run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

			if ($forgotten) {
				$this->session->set_flashdata('success', $this->ion_auth->messages());
				redirect('', 'refresh'); //we should display a confirmation page here instead of the login page
			} else {
				$this->session->set_flashdata('danger', $this->ion_auth->errors());
				redirect('personal/forgot_password', 'refresh');
			}
		}
	}

	function _get_csrf_nonce() {
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}

	function _valid_csrf_nonce() {
		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
			$this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function _render_page($view, $data = null, $render = false)	{
		$this->viewdata = (empty($data)) ? $this->data: $data;
		$view_html = $this->load->view($view, $this->viewdata, $render);
		if (!$render) return $view_html;
	}

}