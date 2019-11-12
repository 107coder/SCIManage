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
    public function getAllUser($perPage,$offest,$key,$identity)
    {
        return $this->db
                    ->limit($perPage,$offest)
                    ->select('job_number,name,gender,academy,identity')
                    ->from('user')
                    ->like('job_number' ,$key)
                    ->or_like('name' ,$key)
                    ->or_like('academy' ,$key)
                    ->or_like('gender' ,$key)
                    ->or_like('identity' ,$identity)
                    ->order_by('identity desc')
                    ->order_by('job_number')
                    ->get()->result_array();
    }
    //返回查询的总数量
    public function getUserNums($key,$identity)
    {
        return $this->db
                    ->select('job_number,name,gender,academy,identity')
                    ->from('user')
                    ->like('job_number' ,$key)
                    ->or_like('name' ,$key)
                    ->or_like('academy' ,$key)
                    ->or_like('gender' ,$key)
                    ->or_like('identity' ,$identity)
                    ->count_all_results();
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
    // 返回数据库中的条数，判断数据是否存在
    public function userExist($where)
    {
        $this->db->where($where);
        $this->db->from('user');
        return $this->db->count_all_results();
    }
    //Excel导入用户
     public function userInsert($data_arr)
    {
        return $this->db->insert_batch('user',$data_arr);
    }
}