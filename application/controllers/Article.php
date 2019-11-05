<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Article extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('article_model','article');
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

    public function getArticleApi()
    {
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');
        $data = $this->article->getArticle($page,$limit);
        $resdata = array(
            'code' => '0',
            'msg'  => '请求数据正常',
            'count'=> $this->db->count_all('article'),
            'data' => $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }

    // 获取文章分类
    public function getTypeApi()
    {
        $page = $this->input->get('page')-1;
        $limit = $this->input->get('limit');
        $data = $this->article->getType($page,$limit);
        $resdata = array(
            'code' => '0',
            'msg'  => '请求数据正常',
            'count'=> $this->db->count_all('subject'),
            'data' => $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }
}