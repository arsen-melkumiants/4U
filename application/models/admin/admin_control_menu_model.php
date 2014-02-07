<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_control_menu_model extends CI_Model
{
	var $menus = array();
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		
		$this->menus = array(
			'top' => array(
				'Сайт'					=> array(
					'global_settings'		=> 'Глобальные настройки',
					'change_access'		    => 'Смена доступа в админ-панель',
					'logout'				=> 'Выйти',
				),
				'Группы'				=> array(
					'manage_game/add'			=> 'Новая группа',
					'manage_game/all/future'		=> 'Будущие группы',
					'manage_game/all/past'			=> 'Прошлые группы',
					'manage_game/all/notice'		=> 'Напоминания',
					'manage_game/calendar'		=> 'Календарь',
				),
				'Склад'				=> array(
					'manage_store/all_equip'		=> 'Список снаряжения',
					'manage_store/add_equip'		=> 'Добавить снаряжение',
					'0'                             => '',
					'manage_store/balls_store'		=> 'Склад шаров',
				),
				/*'Меню'				=> array(
					'manage_menu/upper'				=> 'Верхнее меню',
                    'manage_menu/low'				=> 'Нижнее меню',
				),
				'Контент'				=> array(
					'manage_content/page/all'		=> 'Статические страницы',
					'manage_content/page/add'		=> 'Добавить страницу',
					'1' 							=> '',
                    'manage_partner/all'                => 'Список партнёров',
					/*'manage_content/news/all'		=> 'Новости',
					'manage_content/news/add'		=> 'Добавить новость',
					'manage_content/news_cat/all'	=> 'Категории новостей',*/
				/*),
                'Магазин'                => array(
                    'manage_category/all'               => 'Список категорий',
                    '1'                             => '',
                    'manage_product/all'                => 'Список товаров',
                    'manage_product/special'            => 'Специальные товары',
                    '2'                             => '',
                    'manage_order/all'                  => 'Список заказов',
                    'manage_order/settings'             => 'Настройки заказов',
                    '3'                             => '',
                    'manage_callback/all'              => 'Обратные звонки'
                ),
                'manage_users/user/all'				=> 'Пользователи',
				
				/*
				'extra'					=> 'Дополнительно',
				'developers'			=> 'О разработчиках',*/
			),
		);
		
	}
	
	function get_control_menu($name = false){
		if($name){
			$current_menu = isset($this->menus[$name]) ? $this->menus[$name] : '';
            if(empty($current_menu)){
				return false;
			}
			
            if($this->ion_auth->is_admin()){
				return $this->generate_html_menu($current_menu);
			}
		
            
		}
	}
	
	
	function generate_html_menu($menu = false){
		if(empty($menu)){
			return false;
		}
		
		$html = '<header class="navbar navbar-default navbar-fixed-top">
				<div class="container">
					<div class="navbar-header">
					  <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".general_menu">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					  </button>
					  <a href="../" class="navbar-brand">4U</a>
					</div>
					<nav class="collapse navbar-collapse general_menu" role="navigation">
					  <ul class="nav navbar-nav">';
		foreach($menu as $name => $items){
			if(empty($items)){
				continue;
			}
			if(is_array($items)){
				$html .= '<li class="dropdown">'."\n".
				'<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$name.' <b class="caret"></b></a>'."\n".
				'<ul class="dropdown-menu">';
				foreach($items as $link => $subname){
					if(!empty($subname)){
						$html .= '<li><a href="/'.$link.'">'.$subname.'</a></li>';
					}else{
						$html .= '<li class="divider"></li>';
					}
				}
				$html .= '</ul></li>';
			}
		}
				$html .= '</ul>
			</nav>
		  </div>
		</header>';
		
		return $html;
	}
}
