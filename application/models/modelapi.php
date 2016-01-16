<?php
Class ModelApi extends CI_Model
{

public function savepost($posting)
		{
			$namalengkap = $this->input->post('namalengkap');
			$username  = $this->input->post('username');
			$password  = md5($this->input->post('password'));
			$roleaccess = $this->input->post('roleaccess');
			if($this->input->post('status') == 1)
			{
				$status = '1';
			}
			else
			{
				$status = '2';
			}


			$data = array(
				'namalengkap' => $namalengkap,
				'username' => $username,
				'password' => $password,
				'roleaccess' => $roleaccess,
				'status' => $status
				);

			$qry = $this->db->insert('tweets',$posting);
						
						if($this->db->affected_rows())
						{
							$json = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'REGISTER  SUCCESS');
						}
						else
						{
							$json = array('STATUS' => 'ERROR', 'MESSAGE' => 'REGISTER  FAILED');
						}

			return json_encode($json);
		}
}