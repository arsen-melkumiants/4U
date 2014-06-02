<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_statistic extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Общая статитстика',
			'header_descr' => 'Статистика по основным операциям',
		),
		'daily'            => array(
			'header'       => 'Дневная статитстика',
			'header_descr' => 'Статистика по основным операциям',
		),
		'period'           => array(
			'header'       => 'Статитстика за период',
			'header_descr' => 'Статистика по основным операциям',
		),
		'paid_products'    => array(
			'header'       => 'Статистика по продажам',
			'header_descr' => 'Список проданных продуктов',
		),
		'user_incomes'     => array(
			'header'       => 'Статистика по выплатам',
			'header_descr' => 'Доходы пользователей от продажи продуктов',
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
		$this->load->model('shop_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	function index($period = 'all') {
		$this->data['period'] = $period;
		$this->data['types']  = array(
			'all'    => 'Все время',
			'daily'  => 'Текущий день',
			'period' => 'Указанный период'
		);

		$this->data['dd_list'] = array(
			'Количеcтво проданных товаров' => $this->admin_product_model->get_paid_product_amount($period),
			'Доход сайта от продаж'       => $this->admin_product_model->get_total_income_amount($period),
			'Доход продавцов от продаж'   => $this->admin_product_model->get_sellers_income_amount($period),
		);
		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'dd_page', $this->data, true);
		load_admin_views();
	}

	function daily() {
		$this->index('daily');
	}
	
	function period() {
		$period = array(
			'from' => !empty($_GET['from']) ? strtotime($_GET['from']) : 0,
			'to'   => !empty($_GET['to']) ? strtotime($_GET['to']) : time(),
		);
		$this->index($period);
	}

	function paid_products() {
		$product_categories = $this->admin_product_model->get_product_categories();
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title' => 'Название',
				'width' => '30%',
				'func'  => function($row, $params) {
					return anchor(product_url($row['product_id'], $row['name']), $row['name']);
				}
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
			->text('qty', array(
				'title'   => 'Количество',
				'p_width' => 10
			))
			->text('price', array(
				'title' => 'Цена',
				'func'  => function($row, $params) {
					return $row['price'].' '.$row['symbol'];
				}
		))
			->text('commission', array(
				'title' => 'Коммиссия',
				'func'  => function($row, $params, $that, $CI) {
					return -$CI->shop_model->product_commission($row).' '.$row['symbol'];
				}
		))
			->date('paid_date', array(
				'title' => 'Дата покупки'
			))
			->create(function($CI) {
				return $CI->db
					->select('op.*, o.paid_date, c.symbol')
					->from('shop_order_products as op')
					->join('shop_orders as o', 'op.order_id = o.id')
					->join('shop_currencies as c', 'c.id = o.currency')
					->where('o.status = 1')
					->order_by('o.paid_date', 'desc')
					->order_by('o.id', 'desc')
					->get()
					;
			});

		load_admin_views();
	}

	function user_incomes() {
		$this->load->library('table');
		$this->table
			->text('type_id', array(
				'title' => 'Продукт',
				'func'  => function($row, $params) {
					return anchor(product_url($row['type_id'], $row['prodname']), $row['prodname']);
				}
		))
			->date('login', array(
				'title' => 'Пользователь',
				'func'  => function($row, $params) {
					return anchor(site_url('4U/manage_user/edit/'.$row['user_id']), $row['login']);
				}
		))
			->text('amount', array(
				'title' => 'Доход',
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
					->select('l.*, c.symbol, c.code, u.login, p.name as prodname')
					->from('shop_user_payment_logs as l')
					->join('shop_products as p', 'l.type_id = p.id')
					->join('shop_currencies as c', 'l.currency = c.id')
					->join('users as u', 'l.user_id = u.id')
					->where('l.type_name', 'income_product')
					->order_by('l.id', 'desc')
					->get();
			});

		load_admin_views();
	}
}
