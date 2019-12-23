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

    public function index(){
        if(empty($this->session->job_number)){
            redirect(base_url('/view/page/login/login.html'));
            exit();
        }else{
            redirect(base_url('/view/index.html'));
        }
    }

    public function ids(){
        header("Content-Type: text/html; charset=utf-8");
        $this->load->library('phpCAS');
        phpCAS::client(CAS_VERSION_2_0,'ids.henu.edu.cn',80,'authserver',false);
       
        phpCAS::setNoCasServerValidation();
        phpCAS::handleLogoutRequests();
        phpCAS::forceAuthentication();

        echo "<pre>";
        echo "user:".phpCAS::getUser();
        echo "<br/>";
        echo "*******************";
        echo "<br/>";
        echo "attributes:";
        echo "<br/>";
        var_dump(phpCAS::getAttributes());
    }

    public function idsLoginOut(){
        $param = array('service'=>'http://ssodemo.test.com/cas/index.php');
        phpCAS::logout($param);
        exit;
    }
    public function checkLogin()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        if(!empty($username) && !empty($password))
        {
            // 当系统中一个用户的都没有的额时候，使用默认的账号密码登录
            if($this->noUser()){
                if(md5($username)=='f10a0199d44639f8ceb6b310a4f0e8ea' && md5($password)=='38118ffccfa14989b8e60187267f9919'){
                    $userInfo = array(
                        'job_number' => '100000001',
                        'name'       => 'root',
                        'full_spell' => 'root',
                        'gender'     => '男',
                        'academy'    => '科学技术研究院',
                        'identity'   =>  2
                    );
                    $this->session->set_userdata($userInfo);
                    exit(JsonEcho(0,'登录成功'));
                }
                else{
                    exit(JsonEcho(1,'密码错误'));
                }
            }
            $where = array(
                'job_number'=>$username,
                'password' => md5($password)
            );
            $userInfo = $this->user->checkLogin($where);
//            print_r($userInfo);
            if(!empty($userInfo))
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

    private function noUser(){
        $userCount = $this->db->count_all('user');
        if($userCount == 0){
            return true;
        }else{
            return false;
        }
    }


    // 判断是否已经登录
    public function isLogin()
    {
        $userId = $this->session->job_number;
        if(empty($userId))
        {
            exit(JsonEcho(4,'请先登录！'));
        }else{
            $data = array(
                'job_number' => $this->session->job_number,
                'full_spell' => $this->session->full_spell,
                'name' => $this->session->name,
                'gender' => $this->session->gender,
                'academy' => $this->session->academy,
                'identity' => $this->session->identity
            );
            exit(JsonEcho(0,'已登录',$data));
        }
    }
    // 判断是否首次登陆已经填写姓名全拼
     public function checkAddFullSpell()
    {
        $job_number=$this->session->job_number;
        $data=$this->user->checkTeacher($job_number);
        $resdata = array(
            'code'  => '0',
            'msg'   => '数据请求正常',
            'data'  =>  $data[0]
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }


    public function signOut(){
        $status = session_destroy();
        if($status){
            exit(JsonEcho(0,'退出成功！'));
        }else{
            exit(JsonEcho(1,"退出失败！"));
        }
    }
}