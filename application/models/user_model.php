<?php
/*
 *
 *
		CREATE TABLE `user` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `username` varchar(50) NOT NULL DEFAULT '',
		  `password` varchar(50) NOT NULL DEFAULT '',
		  `name` varchar(50) NOT NULL DEFAULT '',
		  `email` varchar(100) NOT NULL DEFAULT '',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `username` (`username`),
		  UNIQUE KEY `email` (`email`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 */
class User_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function get_user($username = FALSE)
	{
		if ($username === FALSE)
		{
			$query = $this->db->get('user');
			return $query->result_array();
		}
		
		$query = $this->db->get_where('user', array('username' => $username));
		return $query->row_array();
	}

	public function get_user_by_fbid($facebook_id)
	{
		$query = $this->db->get_where('user', array('facebook_id' => $facebook_id));
		return $query->row_array();
	}

	public function set_user(){
		$data = array(
			'username' => $this->input->post('username'),
			'password' => md5($this->input->post('password')),
			'name' => $this->input->post('name'),
			'email' => $this->input->post('email'),
		);
		return $this->db->insert('user', $data);
	}

	public function set_facebook_user($info){
		$data = array(
			'username' => $info['username'],
			'facebook_id' => $info['id'],
			'name' => $info['name'],
			'email' => $info['email'],
		);
		return $this->db->insert('user', $data);
	}

	public function login($username, $password){
		$this -> db -> select(array('username','name'));
		$this -> db -> from('user');
		$this -> db -> where('username = ' . "'" . $username . "'"); 
		$this -> db -> where('password = ' . "'" . MD5($password) . "'"); 
		$this -> db -> limit(1);
		
		$query = $this -> db -> get();

		if($query -> num_rows() == 1) return $query->result();
		else return false;
	}
}

?>