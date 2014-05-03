<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_control_panel extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login', 'refresh');
		}

		$this->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');

		$this->data['title'] = '4U :: ';
	}

	function index() {
		$this->data['title'] .= 'Админ-панель';

		$center_block = $this->last_users();
		$center_block .= $this->last_products(0);
		$center_block .= $this->last_products(1);
		$center_block .= $this->last_orders();

		$this->data['header'] = false;

		$this->data['center_block'] = $center_block;
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
			->text('SITE_EMAIL', array(
				'value'       => (defined('SITE_EMAIL') ? SITE_EMAIL : ''),
				'valid_rules' => 'required|trim|xss_clean|valid_email',
				'label'       => 'Почта сайта',
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

	private function last_users($limit = 5) {
		$this->data['header'] = 'Последние зарегистрированные пользователи';
		$this->data['limit']  = $limit;
		$this->MAIN_URL       = ADM_URL.'manage_user/';
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('username', array(
				'title'   => 'Имя',
				'p_width' => 30
			))
			->date('last_login', array(
				'title' => 'Дата последней авторизации',
			))
			->date('created_on', array(
				'title' => 'Дата регистрации'
			))
			->text('active', array(
				'title' => 'Статус',
				'func'  => function($row, $params, $that, $CI) {
					if ($row['active'] == 0) {
						return '<span class="label label-danger">Неактивированный</span>';
					} elseif ($row['active'] == 1) {
						return '<span class="label label-success">Активированный</span>';
					}
				}
		))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->btn(array(
				'func' => function($row, $params, $html, $that, $CI) {
					if (!$row['status']) {
						$params['title'] = 'Активировать';
						$params['icon'] = 'ok';
					} else {
						$params['title'] = 'Деактивировать';
						$params['icon'] = 'ban-circle';
					}
					return '<a href="'.site_url($CI->MAIN_URL.'active/'.$row['id']).'" title="'.$params['title'].'"><i class="icon-'.$params['icon'].'"></i> </a>';
				}
		))
			->create(function($CI) {
				return $CI->db->select('*, active as status')->limit($CI->data['limit'])->order_by('id', 'desc')->get('users');
			});

		return $this->load->view(ADM_FOLDER.'s_page', $this->data, true);
	}

	private function last_products($status = false, $limit = 5) {
		$this->load->model(ADM_FOLDER.'admin_product_model');
		$product_categories = $this->admin_product_model->get_product_categories();
		if ($status == 0) {
			$this->data['header'] = 'Последние товары на модерацию';
		} elseif ($status == 1) {
			$this->data['header'] = 'Последние активные товары';
		} else {
			$this->data['header'] = 'Последние товары';
		}
		$this->data['limit']  = $limit;
		$this->MAIN_URL       = ADM_URL.'manage_product/';
		$this->data['status'] = $status;

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('cat_id', array(
				'title' => 'Категория',
				'extra' => $product_categories ,
				'func'  => function($row, $params) {
					if (isset($params['extra'][$row['cat_id']]['name'])) {
						return '<span class="label label-info">'.$params['extra'][$row['cat_id']]['name'].'</span>';
					} else {
						return '<span class="label label-warning">Отсутствует</span>';
					}
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
			->create(function($CI) {
				$CI->db->limit($CI->data['limit']);
				return $CI->admin_product_model->get_all_products($CI->data['status']);
			});

		return $this->load->view(ADM_FOLDER.'s_page', $this->data, true);
	}

	public function last_orders($limit = 5) {
		$this->load->model(ADM_FOLDER.'admin_product_model');
		$this->data['limit']  = $limit;
		$this->data['header'] = 'Последние заказы';
		$this->MAIN_URL       = ADM_URL.'manage_product/';
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('id', array(
				'title' => 'Номер',
				'width' => '20%'
			))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->date('username', array(
				'title' => 'Покупатель',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('4U/manage_user/edit/'.$row['user_id']).'">'.$row['username'].'</a>';
				}
		))
			->text('total_amount', array(
				'title' => 'Количество товаров',
			))
			->text('total_price', array(
				'title' => 'Цена',
				'func'  => function($row, $params) {
					return $row['total_price'].' '.$row['symbol'];
				}
		))
			->text('status', array(
				'title' => 'Статус',
				'func'  => function($row, $params, $that, $CI) {
					if ($row['status'] == 0) {
						return '<span class="label label-warning">Неоплаченый</span>';
					} elseif ($row['status'] == 1) {
						return '<span class="label label-success">Оплаченый</span>';
					}
				}
		))
			->btn(array('link' => $this->MAIN_URL.'order_view/%d', 'icon' => 'list', 'title' => 'Детали заказа'))
			->create(function($CI) {
				$CI->db->limit($CI->data['limit']);
				return $CI->admin_product_model->get_orders();
			});

		return $this->load->view(ADM_FOLDER.'s_page', $this->data, true);
	}
}
