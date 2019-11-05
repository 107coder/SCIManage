<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/26
 * Time: 12:03
 */ 

class User_model extends CI_Model
{
    public function getAllUser($perPage,$offest)
    {
        return $this->db
                    ->limit($perPage,$offest)
                    ->select('job_number,name,gender,academy,identity')
                    ->from('user')
                    ->order_by('identity desc','job_number')
                    ->get()->result_array();
    }
    //添加用户
	public function addUser($data){
		$this->db->insert('user',$data);
	} 
 
    //删除用户
    public function delUser($job_number){
        $this->db->where_in('job_number',$job_number)->delete('user');    
    }
    //查询对应的用户
    public function checkUser($job_number){
        $data=$this->db->where(array('job_number'=>$job_number))->get('user')->result_array();
        return $data;
    }
    //修改用户
    public function editUser($job_number,$data){
       $this->db->update('user',$data,array('job_number'=>$job_number));
    }
    //搜索用户
    public function searchUser($perPage,$offest,$key){
        return $this->db
                    ->limit($perPage,$offest)
                    ->select('job_number,name,gender,academy,identity')
                    ->from('user')
                    ->like('job_number' ,$key,)
                    ->or_like('name' ,$key,)
                    ->or_like('academy' ,$key,)
                    ->order_by('identity desc','job_number')
                    ->get()->result_array();
       /*$sql = "select * from user where job_number like '%$key%' or name like '%$key%' or gender like '%$key%' or academy like '%$key%' or identity like '%$key%' order by 'identity desc','job_number'";
       return $this->db->query($sql)->result_array();*/
    }

}