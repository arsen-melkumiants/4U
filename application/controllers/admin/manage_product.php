<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_product extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'                     => array(
			'header'                => 'Все продукты',
			'header_descr'          => 'Список продуктов',
		),
		'moderate'                  => array(
			'header'                => 'Продукты на модерацию',
			'header_descr'          => 'Список продуктов',
		),
		'activated'                 => array(
			'header'                => 'Активные продукты',
			'header_descr'          => 'Список продуктов',
		),
		'rejected'                  => array(
			'header'                => 'Продукты непрошедшие модерацию',
			'header_descr'          => 'Список продуктов',
		),
		'add'                       => array(
			'header'                => 'Добавления продукта',
			'header_descr'          => 'Информация о продукте',
		),
		'edit'                      => array(
			'header'                => 'Редактирование "%name"',
			'header_descr'          => 'Редактирование информации о продукте',
		),
		'delete'                    => array(
			'header'                => 'Удаление продукта "%name"',
			'header_descr'          => false,
		),
		'orders'                    => array(
			'header'                => 'Все заказы',
			'header_descr'          => 'Список заказов',
		),
		'order_view'                => array(
			'header'                => 'Заказ №"%order_id"',
			'header_descr'          => '',
		),
		'withdrawal_requests'       => array(
			'header'                => 'Вывод денег покупателей',
			'header_descr'          => 'Заявки на вывод денег покупателей',
		),
		'withdrawal_sellers'        => array(
			'header'                => 'Вывод денег продавцов',
			'header_descr'          => 'Заявки на вывод денег продавцов',
		),
		'withdrawal_seller_accept'  => array(
			'header'                => 'Вывод денег продавца %login(%email)',
			'header_descr'          => '',
		),
		'fill_up_requests'       => array(
			'header'                => 'Пополнение счета',
			'header_descr'          => 'Заявки на пополнение пользовательского счета',
		),
		'delete_request' => array(
			'header'                => 'Удаление запроса №%id',
			'header_descr'          => '',
		),
		'accept_request' => array(
			'header'                => 'Подтверждение запроса №%id',
			'header_descr'          => '',
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

	public function index($status = false) {
		$product_categories = $this->admin_product_model->get_product_categories();

		$this->data['status'] = $status;

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Название',
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
			->text('login', array(
				'title' => 'Владелец',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('4U/manage_user/edit/'.$row['author_id']).'">'.$row['login'].'</a>';
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
			//			->active(array('link' => $this->MAIN_URL.'active/%d'))
			/*->btn(array(
				'link'   => $this->MAIN_URL.'add',
				'name'   => 'Добавить',
				'header' => true,
			))*/
			->create(function($CI) {
				return $CI->admin_product_model->get_all_products($CI->data['status']);
			});

		load_admin_views();
	}

	public function moderate() {
		$this->index(0);
	}

	public function activated() {
		$this->index(1);
	}

	public function rejected() {
		$this->index(2);
	}

	public function add() {
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE);
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info )) {
			custom_404();
		}
		set_header_info($product_info);
		$this->load->model('shop_model');
		$this->data['center_block'] = $this->edit_form($product_info);
		$this->data['center_block'] .= $this->load->view(ADM_FOLDER.'product_edit_js', $this->data, true);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			if (isset($_POST['status']) && $_POST['status'] != $product_info['status']) {
				if ($_POST['status'] == 1) {
					$this->shop_model->send_mail($product_info['email'], 'mail_product_moderation', 'product_moderated', $product_info);	
				}
				elseif ($_POST['status'] == 2) {
					$this->shop_model->send_mail($product_info['email'], 'mail_product_no_moderation', 'product_no_moderated', $product_info);
				}
			}
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($product_info = false) {
		$product_categories = $this->admin_product_model->get_product_categories();
		array_unshift($product_categories, array('id' => 0, 'name' => 'Без категории'));
		$currencies = $this->admin_product_model->get_currencies();
		array_unshift($currencies, array('id' => '', 'name' => 'Список валюты'));
		if (isset($_POST['commission'])) {
			$_POST['commission'] = abs(round($_POST['commission'], 2));
		}
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|numeric',
				'symbol'	  => '$',
				'label'       => 'Цена',
			))
			->radio('unlimited', array(
				'value'       => $product_info['unlimited'] ?: false,
				'valid_rules' => 'trim|xss_clean|is_natural',
				'label'       => 'Бесконечный',
				'inputs'      => array('Нет', 'Да'),
			))
			->text('amount', array(
				'group_class' => 'amount_field',
				'value'       => $product_info['amount'] ?: false,
				'valid_rules' => 'trim|xss_clean|is_natural',
				'label'       => 'Количество',
			))
			/*->select('currency', array(
				'value'       => $product_info['currency'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Валюта',
				'options'     => $currencies,
				'search'      => true,
			))*/
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Категория',
				'options'     => $product_categories,
				'search'      => true,
			))
/*			->radio('recommended', array(
				'value'       => $product_info['recommended'] ?: false,
				'valid_rules' => 'trim|xss_clean|is_natural',
				'label'       => 'Рекомендуемый',
				'inputs'      => array('Нет', 'Да'),
			))*/
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Текст',
			))
			->text('keywords', array(
				'value'       => $product_info['keywords'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Ключевые слова',
			))
			->text('title', array(
				'value'       => $product_info['title'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Заголовок страницы',
			))
			->text('description', array(
				'value'       => $product_info['description'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Описание',
			))
			->radio('status', array(
				'value'       => $product_info['status'] ?: false,
				'valid_rules' => 'trim|xss_clean|is_natural',
				'label'       => '<span class="text-danger">Модерация</span>',
				'inputs'      => array('Ожидание', 'Подтверждено', 'Отказано'),
			))
			->select('type_commission', array(
				'value'       => $product_info['type_commission'] ?: '',
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Тип комиссии по продаже товара',
				'options'     => array('' => 'Глобальная', 'fixed' => 'Фиксированная', 'percent' => 'Процент'),
			))
			->text('commission', array(
				'value'       => $product_info['commission'] ?: '',
				'valid_rules' => 'trim|xss_clean|numeric',
				'label'       => 'Комиссия по продаже товара',
				'width'       => '2',
			))
			->btn(array('value' => empty($product_info) ? 'Добавить' : 'Изменить'))
			->link(array('name' => 'Галерея', 'href' => site_url($this->MAIN_URL.'gallery/'.$product_info['id'])))
			->link(array('name' => 'Медиа контент', 'href' => site_url($this->MAIN_URL.'media_files/'.$product_info['id'])))
			->create(array('action' => current_url()));
	}

	public function delete($id = false, $type = false) {
		if (empty($id)) {
			custom_404();
		}

		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info)) {
			custom_404();
		}
		set_header_info($product_info);

		if ($this->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->db->where('id', $id)->update($this->DB_TABLE, array('status' => 3));
				$this->session->set_flashdata('danger', 'Удаление успешно выполено');
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 4));
				echo $this->load->view(ADM_FOLDER.'ajax', '', true);
			}
		} else {
			$this->db->where('id', $id)->update($this->DB_TABLE, array('status' => 3));
			$this->session->set_flashdata('danger', 'Удаление успешно выполено');
			redirect($this->MAIN_URL, 'refresh');
		}

	}

	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info)) {
			custom_404();
		}
		set_header_info($product_info);

		admin_method('active', $this->DB_TABLE, $product_info);
	}

	function gallery($id = false) {
		$this->media_files($id, 'image');
	}

	function upload_gallery($id = false) {
		$this->upload_media_files($id, 'image');
	}

	function media_files($id = false, $type = false) {
		$id = $this->data['id'] = intval($id);
		$this->data['type'] = $type;
		$product_info = $this->admin_product_model->get_product_info($id);
		if (empty($product_info)) {
			redirect($this->MAIN_URL, 'refresh');
		}

		if ($type == 'image') {
			$this->data['title'] = $this->data['name'] = 'Галерея';
			$this->data['upload_url'] = base_url($this->MAIN_URL.'upload_gallery/'.$id);
		} else {
			$this->data['title'] = $this->data['name'] = 'Медиа контент';
			$this->data['upload_url'] = base_url($this->MAIN_URL.'upload_media_files/'.$id);
		}

		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'upload', $this->data, true);
		load_admin_views();
	}

	function upload_media_files($id = false, $type = 'file') {
		$id = intval($id);
		$product_info = $this->admin_product_model->get_product_info($id);
		if (empty($product_info)) {
			redirect($this->MAIN_URL, 'refresh');
		}
		$folder = $product_info['id'];
		$this->load->model('shop_model');

		if ($type == 'image') {
			$upload_path_url = base_url('uploads/gallery/'.$folder).'/';
		} else {
			$upload_path_url = base_url('media_files').'/';
		}

		$files = array();

		$product_files = $type == 'image' ? $this->shop_model->get_product_images($id) : $this->shop_model->get_product_files($id);
		foreach ($product_files as $item) {
			$thumbnail = '';
			if (preg_match('/\.(jpg|jpeg|png|gif)/iu', $item['file_name'])) {
				if ($type == 'image') {
					$thumbnail = $upload_path_url.'small_thumb/'.$item['file_name'];
				} else {
					$thumbnail = $upload_path_url.$item['id'];
				}
			}
			$files[] = array(
				'name'         => $item['file_name'],
				'url'          => $type == 'image' ? $upload_path_url.$item['file_name'] : $upload_path_url.$item['id'],
				'thumbnailUrl' => $thumbnail,
				'error'        => null,
				'sold'         => !empty($item['status'])
			);
		}

		if ($this->input->is_ajax_request()) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(array('files' => $files)));
		}	
	}

	public function orders() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('id', array(
				'title' => 'Номер',
				'width' => '20%'
			))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->date('login', array(
				'title' => 'Покупатель',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('4U/manage_user/edit/'.$row['user_id']).'">'.$row['login'].'</a>';
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
			->btn(array(
				'func' => function($row, $params, $html, $that, $CI) {
					if (!$row['status']) {
						$params['title'] = 'Неоплачен';
						$params['icon'] = 'ban-circle';
					} else {
						$params['title'] = 'Оплачен';
						$params['icon'] = 'ok';
					}
					return '<a href="'.site_url($CI->MAIN_URL.'order_pay/'.$row['id']).'" title="'.$params['title'].'"><i class="icon-'.$params['icon'].'"></i> </a>';
				}
		))
			->create(function($CI) {
				return $CI->admin_product_model->get_orders();
			});

		load_admin_views();
	}

	public function order_pay($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$order_info = $this->db->where('id', $id)->get('shop_orders')->row_array();

		if (empty($order_info)) {
			custom_404();
		}
		set_header_info(array('order_id' => $id));

		$this->load->model('shop_model');

		if (!$order_info['status']) {
			$this->shop_model->pay_order($id, $order_info, true);
		} else {
			$this->shop_model->rollback_order($id);
		}

		redirect(ADM_URL.strtolower(__CLASS__).'/orders', 'refresh');
	}

	public function order_view($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$order_info = $this->admin_product_model->get_order_info($id);

		if (empty($order_info)) {
			custom_404();
		}
		$this->data['id'] = $id;
		set_header_info(array('order_id' => $id));
		$product_categories = $this->admin_product_model->get_product_categories();
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title' => 'Название',
				'width' => '40%',
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
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->create(function($CI) {
				return $CI->admin_product_model->get_order_info($CI->data['id']);
			});

		load_admin_views();
	}

	public function withdrawal_requests() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('id', array('tityyle' => 'Номер заявки'))
			->date('login', array(
				'title' => 'Пользователь',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('4U/manage_user/edit/'.$row['user_id']).'">'.$row['login'].'</a>';
				}
		))
			->text('name', array('title' => 'Название'))
			->text('number', array('title' => 'Номер'))
			->text('amount', array(
				'title' => 'Количество',
				'func'  => function($row, $params) {
					return floatval($row['amount']).' '.$row['symbol'];
				}
		))
			->text('commission', array(
				'title' => lang('commission'),
				'func'  => function($row, $params) {
					return floatval($row['commission']).' '.$row['symbol'];
				}
		))
			->text('amount', array(
				'title' => 'Всего на снятие',
				'func'  => function($row, $params) {
					return floatval($row['amount'] + $row['commission']).' '.$row['symbol'];
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->delete(array('link' => $this->MAIN_URL.'delete_request/%d', 'modal' => 1))
			->btn(array('link' => $this->MAIN_URL.'accept_request/%d', 'modal' => 1, 'icon' => 'ok'))
			->create(function($CI) {
				return $CI->db
					->select('r.*, c.symbol, c.code, u.username, u.login')
					->from('shop_user_payment_requests as r')
					->join('shop_currencies as c', 'r.currency = c.id')
					->join('users as u', 'r.user_id = u.id')
					->where('r.type', 'withdraw')
					->where('r.status', 0)
					->order_by('r.id', 'desc')
					->get();
			});

		load_admin_views();
	}

	public function fill_up_requests() {
		$all_users = $this->db->select('id, CONCAT(login, \' - \', email) as name', false)->get('users')->result_array();
		array_unshift($all_users, array('id' => '', 'name' => 'Список пользователей'));

		$this->load->library('form');
		$this->data['center_block'] = $this->form
			->select('user_id', array(
				'value'       => false,
				'valid_rules' => 'trim|xss_clean|required',
				'label'       => 'Пользователь',
				'options'     => $all_users,
				'search'      => true,
			))
			->text('amount', array(
				'valid_rules' => 'required|trim|xss_clean|price',
				'symbol'      => '$',
				'label'       => lang('product_amount'),
			))
			->btn(array('value' => lang('finance_fill_up')))
			->create();

		if ($this->form_validation->run() != FALSE) {
			$this->load->model('shop_model');
			$this->shop_model->log_payment($this->input->post('user_id'), 'fill_up', 0, floatval($this->input->post('amount')));
			$this->session->set_flashdata('success', 'Счет успешно пополнен');
			redirect(current_url(), 'refresh');
		}

		load_admin_views();
	}

	public function delete_request($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$request_info = $this->db->where(array('id' => $id,'status'  => 0))->get('shop_user_payment_requests')->row_array();
		if (empty($request_info)) {
			custom_404();
		}
		set_header_info($request_info);

		if (isset($_POST['delete'])) {
			$this->db->where('id', $id)->update('shop_user_payment_requests', array('status' => 2));
			$this->session->set_flashdata('danger', 'Удаление успешно выполено');
			echo 'refresh';
		} else {
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			echo $this->load->view(ADM_FOLDER.'ajax', '', true);
		}
	}

	public function accept_request($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$request_info = $this->db->where(array('id' => $id,'status'  => 0))->get('shop_user_payment_requests')->row_array();
		if (empty($request_info)) {
			custom_404();
		}
		set_header_info($request_info);

		$this->load->model('shop_model');

		$request_info['total'] = $this->input->post('total') ?: $request_info['amount'] + $request_info['commission'];

		if (isset($_POST['accept'])) {

			$user_balance = $this->shop_model->get_user_balance($request_info['user_id']);
			if ($request_info['type'] == 'withdraw' && $user_balance[0]['amount'] < $request_info['total']) {
				$this->session->set_flashdata('danger', 'Недостаточно средств на счету пользователя('.$user_balance[0]['amount'].' $)');
				echo 'refresh';
				exit;
			}

			$this->db->trans_begin();
			$this->load->model('shop_model');

			$this->db->where('id', $id)->update('shop_user_payment_requests', array('status' => 1));
			if ($request_info['type'] == 'fill_up') {
				$this->shop_model->log_payment($request_info['user_id'], 'fill_up', $request_info['id'], $request_info['amount']);
			} elseif ($request_info['type'] == 'withdraw') {
				$this->shop_model->log_payment($request_info['user_id'], 'draw_out', $request_info['id'], -($request_info['total']));
				if ($this->input->post('total')) {
					$update_array['commission'] = $request_info['total'] / (100 + WITHDRAWAL_COMMISSION) * WITHDRAWAL_COMMISSION;
					$update_array['amount'] = $request_info['total'] - $update_array['commission'];
					$this->db->where('id', $id)->update('shop_user_payment_requests', $update_array);
				}
			}
			$this->session->set_flashdata('success', 'Запрос успешно обработан');

			$this->db->trans_commit();
			echo 'refresh';
			exit;
		} else {
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->text('payment_amount', array(
					'class'       => 'withdraw_amount',
					'value'       => $request_info['amount'] ?: false,
					'valid_rules' => 'required|trim|xss_clean|numeric',
					'symbol'      => '$',
					'label'       => 'Количество',
				))
				->text('total', array(
					'group_class' => 'withdraw_total',
					'value'       => $request_info['total'] ?: false,
					'valid_rules' => 'required|trim|xss_clean|numeric',
					'symbol'	  => '$',
					'label'       => 'Итого + комиссия <span class="commis_value">'.$request_info['commission'].'</span> $',
					'readonly'    => 1,
				))
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'accept', 'value' => $request_info['type'] == 'withdraw' ? 'Вывести' : 'Пополнить', 'class' => 'btn-success'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			$this->data['center_block'] .= $this->load->view(ADM_FOLDER.'withdraw_js', $this->data, true);
			echo $this->load->view(ADM_FOLDER.'ajax', '', true);
		}
	}

	public function withdrawal_sellers() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->date('login', array(
				'title' => 'Пользователь',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('4U/manage_user/edit/'.$row['id']).'">'.$row['login'].'</a>';
				}
		))
			->text('payment_name', array('title' => 'Название'))
			->text('number', array('title' => 'Номер'))
			->text('balance', array(
				'title' => 'На счету продавца',
				'func'  => function($row, $params) {
					return floatval($row['balance']).' '.$row['symbol'];
				}
		))
			->text('payment_amount', array(
				'title' => 'Минимальное количество на вывод',
				'func'  => function($row, $params) {
					return floatval($row['payment_amount']).' '.$row['symbol'];
				}
		))
			->text('commission', array(
				'title' => lang('commission'),
				'func'  => function($row, $params) {
					return -floatval(round($row['commission'], 2)).' '.$row['symbol'];
				}
		))
			->text('total', array(
				'title' => 'На перевод пользователю',
				'func'  => function($row, $params) {
					return floatval(round($row['total'], 2)).' '.$row['symbol'];
				}
		))
			->date('payment_last_date', array(
				'title' => 'Последнее снятие'
			))
			->btn(array('link' => $this->MAIN_URL.'withdrawal_seller_accept/%d', 'modal' => 1, 'icon' => 'ok', 'title' => 'Снять со счета'))
			->create(function($CI) {
				return $CI->db
					->select('SUM(l.amount) as balance, (SUM(l.amount) / 100 * '.WITHDRAWAL_COMMISSION.') as commission, (SUM(l.amount) / 100 * (100 - '.WITHDRAWAL_COMMISSION.')) as total, l.currency, c.symbol, c.code, u.*')
					->from('shop_user_payment_logs as l')
					->join('shop_currencies as c', 'l.currency = c.id')
					->join('users as u', 'u.id = l.user_id')
					->where(array(
						'l.currency'          => 1,
						'u.payment_amount >'  => 0,
						'u.payment_number !=' => 0,
						'u.is_seller'         => 1,
					))
					->where('u.payment_last_date + 86400 <', time())
					->group_by('u.id')
					->having('balance >= total')
					->get();
			});

		load_admin_views();
	}

	public function withdrawal_seller_accept($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$this->data['seller'] = 1;

		$seller_info = $this->db
			->select('SUM(l.amount) as balance, (SUM(l.amount) / 100 * '.WITHDRAWAL_COMMISSION.') as commission, (SUM(l.amount) / 100 * (100 - '.WITHDRAWAL_COMMISSION.')) as total, l.currency, c.symbol, c.code, u.*')
			->from('shop_user_payment_logs as l')
			->join('shop_currencies as c', 'l.currency = c.id')
			->join('users as u', 'u.id = l.user_id')
			->where(array(
				'l.currency'          => 1,
				'u.payment_amount >'  => 0,
				'u.payment_number !=' => 0,
				'u.is_seller'         => 1,
			))
			->group_by('u.id')
			->having('balance >= total')
			->get()
			->row_array();
		if (empty($seller_info)) {
			custom_404();
		}
		$seller_info['total']          = round($seller_info['total'], 2);
		$seller_info['commission']     = round($seller_info['commission'], 2);
		$seller_info['payment_amount'] = $seller_info['total'] + $seller_info['commission'];
		set_header_info($seller_info);

		if (isset($_POST['accept'])) {
			$this->load->model('shop_model');

			$seller_info['total'] = $this->input->post('total') ?: $seller_info['total'];
			$seller_info['payment_amount'] = $this->input->post('payment_amount') ?: $seller_info['payment_amount'];

			$this->db->trans_begin();
			$this->load->model('shop_model');
			$this->db->where('id', $seller_info['id'])->update('users', array('payment_last_date' => time()));
			$this->shop_model->log_payment($seller_info['id'], 'draw_out', 0, -$seller_info['payment_amount']);
			$this->session->set_flashdata('success', 'Запрос успешно обработан');

			$this->db->trans_commit();
			echo 'refresh';
			exit;
		} else {
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->text('payment_amount', array(
					'class'       => 'withdraw_amount',
					'value'       => $seller_info['payment_amount'] ?: false,
					'valid_rules' => 'required|trim|xss_clean|numeric',
					'symbol'      => '$',
					'label'       => 'На снятие с внутреннего счета',
				))
				->text('total', array(
					'group_class' => 'withdraw_total',
					'value'       => $seller_info['total'] ?: false,
					'valid_rules' => 'required|trim|xss_clean|numeric',
					'symbol'	  => '$',
					'label'       => 'На перевод пользователю, комиссия -<span class="commis_value">'.$seller_info['commission'].'</span> $',
					'readonly'    => 1,
				))
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'accept', 'value' => 'Вывести', 'class' => 'btn-success'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			$this->data['center_block'] .= $this->load->view(ADM_FOLDER.'withdraw_js', $this->data, true);
			echo $this->load->view(ADM_FOLDER.'ajax', '', true);
		}
	}
}
