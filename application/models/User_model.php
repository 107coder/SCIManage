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
    public function getAllTeacher($perPage,$offest,$key,$identity)
    {
        if(isset($this->session->identity) && $this->session->identity != 2){
            $where = ['academy'=>$this->session->academy];
        }else{
            $where = [];
        }
        return $this->db
                    ->where($where)
                    ->limit($perPage,$offest)
                    ->select('job_number,name,gender,academy,identity')
                    ->from('user')
                    ->group_start()
                        ->like('job_number' ,$key)
                        ->or_like('name' ,$key)
                        ->or_like('academy' ,$key)
                        ->or_like('gender' ,$key)
                        ->or_like('identity' ,$identity)
                    ->group_end()
                    ->order_by('identity desc')
                    ->order_by('job_number')
                    ->get()->result_array();
    }
    public function getAllStudent($perPage,$offest,$key)
    {

        return $this->db
                    ->limit($perPage,$offest)
                    ->select('sno,name,gender,academy,profession')
                    ->from('student')
                    ->like('sno' ,$key)
                    ->or_like('name' ,$key)
                    ->or_like('academy' ,$key)
                    ->or_like('gender' ,$key)
                    ->or_like('profession' ,$key)
                    ->order_by('sno')
                    ->get()->result_array();
    }
    //返回查询的总数量
    public function getTeacherNums($key,$identity)
    {
        if(isset($this->session->identity) && $this->session->identity!=2){
            $where = ['academy'=>$this->session->academy];
        }else{
            $where = [];
        }
        return $this->db
                    ->select('job_number,name,gender,academy,identity')
                    ->where($where)
                    ->from('user')
                    ->group_start()  // 同时使用where条件和or_like去查询，需要在查询的条件上使用group_start()加上括号
                        ->like('job_number' ,$key)
                        ->or_like('name' ,$key)
                        ->or_like('academy' ,$key)
                        ->or_like('gender' ,$key)
                        ->or_like('identity' ,$identity)
                    ->group_end()
                    ->count_all_results();
    }
    public function getStudentNums($key)
    {
        return $this->db
                    ->select('sno,name,gender,academy,profession')
                    ->from('student')
                    ->like('sno' ,$key)
                    ->or_like('name' ,$key)
                    ->or_like('academy' ,$key)
                    ->or_like('gender' ,$key)
                    ->or_like('profession' ,$key)
                    ->count_all_results();
    }
    //添加用户
    public function addTeacher($data){
        $this->db->insert('user',$data);
    }
    public function addStudent($data){
        $this->db->insert('student',$data);
    } 
 
    //删除用户
    public function delTeacher($job_number){
        $this->db->where_in('job_number',$job_number)->delete('user');    
    }
    public function delStudent($sno){
        $this->db->where_in('sno',$sno)->delete('student');    
    }
    //查询对应的用户
    public function checkTeacher($job_number){
        $data=$this->db->where(array('job_number'=>$job_number))->get('user')->result_array();
        return $data;
    }
    public function checkStudent($sno){
        $data=$this->db->where(array('sno'=>$sno))->get('student')->result_array();
        return $data;
    }
    //修改用户
    public function editTeacher($job_number,$data){
       return $this->db->update('user',$data,array('job_number'=>$job_number));
    }
    public function editStudent($sno,$data){
       $this->db->update('student',$data,array('sno'=>$sno));
    }
    // 返回数据库中的条数，判断数据是否存在
    public function TeacherExist($where)
    {
        $this->db->where($where);
        $this->db->from('user');
        return $this->db->count_all_results();
    }
    public function studentExist($where)
    {
        $this->db->where($where);
        $this->db->from('student');
        return $this->db->count_all_results();
    }
    //Excel导入用户
     public function teacherInsert($data_arr)
    {
        return $this->db->insert_batch('user',$data_arr);
    }
     public function studentInsert($data_arr)
    {
        return $this->db->insert_batch('student',$data_arr);
    }

    public function checkLogin($where)
    {
        return $this->db->select('job_number,full_spell,name,gender,academy,identity')->where($where)->from('user')->get()->result_array();
    }
}