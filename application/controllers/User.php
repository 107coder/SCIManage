<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/26
 * Time: 12:01
 */

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model','user');
    }

    public function getAllUserApi()
    {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');    //每页多少条
        $offset=(($page-1)*$limit);         //从第几条开始
        $key = $this->input->get('key');
        if($key=="校级管理员")    //判断用户身份
            $identity=2;
        else if($key=="院级管理员")
            $identity=1;
        else if($key=="普通用户")
            $identity=0;
        else
            $identity=-1;
        $data = $this->user->getAllUser($limit,$offset,$key,$identity);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'count' =>  $this->db->count_all('user'),
            'data'  =>  $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }

    //添加用户
    public function addUser()
    {
        $data=array
        (
            'job_number' => $this->input->post('job_number'),
            'name' => $this->input->post('name'),
            'gender' => $this->input->post('gender'),
            'academy' => $this->input->post('academy'),
            'birthday' => $this->input->post('birthday'),
            'edu_background' => $this->input->post('edu_background'),
            'degree' => $this->input->post('degree'),
            'job_title' => $this->input->post('job_title'),
            'job_title_rank' => $this->input->post('job_title_rank'),
            'job_title_series'=> $this->input->post('job_title_series'),
            'full_spell' => $this->input->post('full_spell'),  
            'identity' => $this->input->post('identity')  
        );
        $this->user->addUser($data);   
    }

    //查询对应用户
    public function checkUser(){
        $job_number=$this->input->post('job_number');
        $data=$this->user->checkUser($job_number);
        echo json_encode($data);
    }

    //编辑用户
    public function editUser()
    {
        $job_number=$this->input->post('job_number');
        $data=array
        (
            'name' => $this->input->post('name'),
            'gender' => $this->input->post('gender'),
            'academy' => $this->input->post('academy'),
            'birthday' => $this->input->post('birthday'),
            'edu_background' => $this->input->post('edu_background'),
            'degree' => $this->input->post('degree'),
            'job_title' => $this->input->post('job_title'),
            'job_title_rank' => $this->input->post('job_title_rank'),
            'job_title_series'=> $this->input->post('job_title_series'),
            'full_spell' => $this->input->post('full_spell'),  
            'identity' => $this->input->post('identity')   
        );
        $this->user->editUser($job_number,$data);   
    }

    //删除用户
    public function delUser()
    {
        $job_number=$this->input->post('job_number');
        $this->user->delUser($job_number);
    }
    
        

}