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

    public function getTypeForAdd(){
        $data = $this->article->getType();
        $resdata = array(
            'code' => '0',
            'msg'  => '请求数据正常',
            'count'=> $this->db->count_all('subject'),
            'data' => $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }
    /**
     * 添加文章
     *
     * @return json 添加成功或者失败
     */
    public function insertArticleApi()
    {   
        $data = $this->input->post('data');
        $dataArt = json_decode($data,true);
        $dataArt['page'] = $dataArt['startPage'].'--'.$dataArt['endPage'];
        unset($dataArt['startPage'],$dataArt['endPage']);
        $dataArt['is_first_inst'] = !empty($dataArt['is_first_inst'])?'是':'不是';
        $dataArt['is_cover'] = !empty($dataArt['is_cover'])?'是':'不是';
        $dataArt['is_top'] = !empty($dataArt['is_top'])?'是':'不是';
        $dataArt['add_method'] = 1;
        if($this->article->checkArticleExist($dataArt['accession_number']))
        {
            $status = $this->article->insertArticle($dataArt);
            if($status){
                echo JsonEcho(0,'添加成功');
            }
            else 
            {
                exit(JsonEcho(2,'添加失败'));
            }
        }
        else
        {
            exit(JsonEcho(1,'文章已存在，请检查wos号'));
        }
    }

    /**
     * 更新作者中的第一作者用户限制认领
     */
    public function updateAuthor()
    {
        $author = 'Wang,Guan';
//        获取所有文章的信息
        $data = $this->article->getArticle();
        foreach ($data as $value)
        {
            $first_author = explode('; ',$value['author'])[0]; // 截取出来第一个作者，作为认领人的限制条件
            $first_author = str_replace(' ','',$first_author);
            $first_author = str_replace('-','',$first_author);
            $data_arr = array('first_author'=>$first_author);
            $where = array('accession_number'=>$value['accession_number']);
            $this->article->updateArticle($data_arr,$where);
//            if(strnatcasecmp($first_author,$author) != 0) continue;
//            echo "<pre>";
//            print_r($where);
//            print_r($data);
//            echo "</pre>";
        }
//        echo "<pre>";
//        print_r(explode('; ',$data[0]['author']));
//        echo "</pre>";

    }

    public function claimArticle()
    {
        $accession_number = $this->input->post('accession_number');
        $where = ['accession_number'=>$accession_number];
        $data = $this->article->checkArticle($where);
        $yourName = $this->session->full_spell;
        $first_author = $data[0]['first_author'];
        if(strnatcasecmp($yourName,$first_author) == 0)
            echo JsonEcho('0','您可认领这篇文章',$data);
        else
            exit(JsonEcho('1','抱歉，不能认领属于自己的文章'));
    }
}
