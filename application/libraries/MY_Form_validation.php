<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

	// --------------------------------------------------------------------

	/**
	 * Valid Date (ISO format)
	 *
	 * @access    public
	 * @param    string
	 * @return    bool
	 */
	function valid_date($str) {
		if (preg_match("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})/", $str)) {
			$arr = split(".", $str);    // splitting the array
			$day = $arr[0];            // first element of the array is year
			$month = $arr[1];              // second element is month
			$year = $arr[2];              // third element is days
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Numeric equal or greater than 0
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function price($str) {
		return (bool)preg_match( '/^[\+]?[0-9]*\.?[0-9]+$/', $str);
	}


	/**
	 * Match one field to another without current row
	 *
	 * Created by Arsen
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	function is_unique_without($str, $field) {
		list($table, $field, $id)=explode('.', $field);
		$query = $this->CI->db->limit(1)->get_where($table, array($field => $str, 'id !=' => $id));

		return $query->num_rows() === 0;
	}

}
