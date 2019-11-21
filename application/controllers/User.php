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

    public function getAllTeacherApi()
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
        $data = $this->user->getAllTeacher($limit,$offset,$key,$identity);
        $nums= $this->user->getTeacherNums($key,$identity);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'count' =>  $nums,
            'data'  =>  $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }

    public function getAllStudentApi()
    {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');    //每页多少条
        $offset=(($page-1)*$limit);         //从第几条开始
        $key = $this->input->get('key');
        $data = $this->user->getAllStudent($limit,$offset,$key);
        $nums= $this->user->getStudentNums($key);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'count' =>  $nums,
            'data'  =>  $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }

    //添加用户
    public function addTeacher()
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
            'identity' => $this->input->post('identity'),
            'password' => md5('a'.$this->input->post('job_number'))
        );
        $this->user->addTeacher($data);   
    }
    public function addStudent()
    {
        $data=array
        (
            'sno' => $this->input->post('sno'),
            'name' => $this->input->post('name'),
            'gender' => $this->input->post('gender'),
            'academy' => $this->input->post('academy'),
            'profession' => $this->input->post('profession'),
        );
        $this->user->addStudent($data);   
    }

    //查询对应用户
    public function checkTeacher(){
        $job_number=$this->input->post('job_number');
        $data=$this->user->checkTeacher($job_number);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'data'  =>  $data[0]
        ); 
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }
    public function checkStudent(){
        $sno=$this->input->post('sno');
        $data=$this->user->checkStudent($sno);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'data'  =>  $data[0]
        ); 
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 通过id获取一个用户的信息
     *
     * @return void
     */
    public function getOneTeacher()
    {
        $job_number=$this->input->post('job_number');
        $data=$this->user->checkTeacher($job_number);
        if(empty($data)){
          exit(JsonEcho('1','没有该用户'));
        }else{
            echo JsonEcho('0','数据请求正常',$data[0]);
        }
    }
    //管理员编辑用户
    public function editTeacher()
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
        $this->user->editTeacher($job_number,$data);   
    }
    //用户修改资料
    public function changeTeacher()
    {
        $job_number=$this->input->post('job_number');
        $data=array
        (
            'birthday' => $this->input->post('birthday'),
            'full_spell' => $this->input->post('full_spell'),
            /*'name' => $this->input->post('name'),
            'gender' => $this->input->post('gender'),
            'academy' => $this->input->post('academy'),           
            'edu_background' => $this->input->post('edu_background'),
            'degree' => $this->input->post('degree'),
            'job_title' => $this->input->post('job_title'),
            'job_title_rank' => $this->input->post('job_title_rank'),
            'job_title_series'=> $this->input->post('job_title_series'),             
            'identity' => $this->input->post('identity') */  
        );
        $this->user->editTeacher($job_number,$data);   
    }
    public function editStudent()
    {
        $sno=$this->input->post('sno');
        $data=array
        (
            'name' => $this->input->post('name'),
            'gender' => $this->input->post('gender'),
            'academy' => $this->input->post('academy'),
            'profession' => $this->input->post('profession'),  
        );
        $this->user->editStudent($sno,$data);   
    }
    //删除用户
    public function delTeacher()
    {
        $job_number=$this->input->post('job_number');
        $this->user->delTeacher($job_number);
    }
    public function delStudent()
    {
        $sno=$this->input->post('sno');
        $this->user->delStudent($sno);
    }
    //第一次登陆时添加用户姓名全拼
    public function addFullSpell()
    {
        $job_number=$this->session->job_number;
        $data=array
        (
            'full_spell' => $this->input->post('full_spell')  
        );
        $this->user->editTeacher($job_number,$data); 
    }
    //修改密码
    public function teacherInfo(){
        $job_number=$this->session->job_number;
        $data=$this->user->checkTeacher($job_number);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'data'  =>  $data[0]
        ); 
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }
    public function checkPwd()
    {
        $job_number=$this->session->job_number;
        $oldPwd=md5($this->input->post('oldPwd'));
        $data=$this->user->checkTeacher($job_number);
        if($oldPwd!=$data[0]['password'])
            echo "密码错误！";
           
    }
    public function changePwd()
    {
        $job_number=$this->session->job_number;
        $data=array
        (
            'password' => md5($this->input->post('newPwd'))  
        );
        $this->user->editTeacher($job_number,$data);    
    }      

}