<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {


    /**
     * Login constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model','user');
    }

    public function checkLogin()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        if(!empty($username) && !empty($password))
        {
            $where = array(
                'job_number'=>$username,
            );
            $userInfo = $this->user->checkLogin($where);
//            print_r($userInfo);
            if(!empty($userInfo) && $password==('a'.$username))
            {
                $this->session->set_userdata($userInfo[0]);
                echo JsonEcho('0','登录成功',['username'=>$username,'password'=>$password]);
            }
            else
            {
                echo JsonEcho('1','用户名或密码错误！');
            }

        }
        else
        {
            echo JsonEcho('1','用户名和密码不能为空');
        }

    }


    // 判断是否已经登录
    public function isLogin()
    {
        $userId = $this->session->job_number;
        if(empty($userId))
        {
            exit(JsonEcho(4,'请先登录！'));
        }
    }
    // 判断是否首次登陆已经填写姓名全拼
     public function checkAddFullSpell()
    {
        $job_number=$this->session->job_number;
        $data=$this->user->checkUser($job_number);
        echo json_encode($data);
    }

}