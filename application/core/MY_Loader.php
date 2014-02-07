<?php 
class MY_Loader extends CI_Loader {

    function __construct()
    {
        parent::__construct();
    }
	
	public function view($view, $vars = array(), $return = FALSE){
		init_system_alerts();
		//parent::view($view, $vars, $return);
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}
}