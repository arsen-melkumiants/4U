<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_history extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Все операции',
			'header_descr' => 'Список всех платежных операций',
		),
		'refillng'         => array(
			'header'       => 'Пополнения',
			'header_descr' => 'Список операций пополнения счета',
		),
		'withdrawing'      => array(
			'header'       => 'Снятия',
			'header_descr' => 'Список операций снятия денег со счета',
		),
		'facilities'       => array(
			'header'       => 'Услуги',
			'header_descr' => 'Оплаты дополнительных услуг',
		),
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_product_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	function index($types = false) {
		$this->data['types'] = $types;
		$this->load->library('table');
		$this->table
			->text('type_name', array(
				'title' => 'Тип операции',
				'func'  => function($row, $params, $that, $CI) {
					return lang('finance_'.$row['type_name']);
				}
		))
			->text('type_id', array(
				'title' => 'Номер объекта операции<br />(заказ, продукт и т.д.)',
				'width' => '20%',
			))
			->date('username', array(
				'title' => 'Пользователь',
				'func'  => function($row, $params) {
					return anchor(site_url('4U/manage_user/edit/'.$row['user_id']), $row['username']);
				}
		))
			->text('amount', array(
				'title' => 'Количество',
				'func'  => function($row, $params) {
					return '<div class="price"><i class="c_icon_label"></i>'.floatval($row['amount']).' '.$row['symbol'].'</div>';
				}
		))
			->date('date', array(
				'title' => lang('date'),
			));
		$this->data['center_block'] = $this->table
			->create(function($CI) {
				if (!empty($CI->data['types']) && is_array($CI->data['types'])) {
					$CI->db->where_in('type_name', $CI->data['types']);
				}
				return $CI->db
					->select('l.*, c.symbol, c.code, u.username')
					->from('shop_user_payment_logs as l')
					->join('shop_currencies as c', 'l.currency = c.id')
					->join('users as u', 'l.user_id = u.id')
					->order_by('l.id', 'desc')
					->get();
			});

		load_admin_views();
	}

	public function refillng() {
		$this->index(array('fill_up'));
	}

	public function withdrawing() {
		$this->index(array('draw_out'));
	}

	public function facilities() {
		$this->index(array('lift_up', 'mark', 'make_vip'));
	}
}
