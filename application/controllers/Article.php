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
        $page = $this->input->get('page')-1;
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
        // $author = 'Wang,Guan';
        // 获取所有文章的信息
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

    
    public function verifyClaimAuthority()
    {
        // 获取文章wos号码
        $accession_number = $this->input->post('accession_number');
        // 拼接出 where 查询条件
        $where = ['accession_number'=>$accession_number];
        $data = $this->article->checkArticle($where);

        $yourName = $this->session->full_spell;
        $first_author = $data[0]['first_author'];
        if(strnatcasecmp($yourName,$first_author) == 0)
        {
            echo JsonEcho('0','您可认领这篇文章');
        } else{
            exit(JsonEcho('1','这篇文章您不能认领，只能查看，如有疑问请联系系统系统管理员！'));
        }
    }

    public function claimArticle()
    {
        // 从前端获取数据
        $accession_number = $this->input->post('accession_number');
        $tableData = $this->input->post('tableData');
        // 从article表中获取相关信息，检查是否能够认领并且是否用认领的权限
        $where = ['accession_number'=>$accession_number];
        $data = $this->article->checkArticle($where);
        $yourName = $this->session->full_spell;
        $first_author = $data[0]['first_author'];
        if(strnatcasecmp($yourName,$first_author) == 0)
        {
            if($data[0]['owner']!=null || $data[0]['articleStatus']!=0)
            {
                exit(JsonEcho('1','文章已被认领'));
            }

            // 首先更新 article 表中的数据
            $data_article = array(
                'owner' => $this->session->job_number,
                'articleStatus' => 1,
                'claim_time' => time()
            );
            $res = $this->article->updateArticle($data_article,$where);
            if(!$res)
            {
                exit(JsonEcho('1','文章认领失败，请稍后重试'));
            }
            $data = json_decode($tableData,true);
            // 需要插入的数据，在下面进行拼接
            $data_arr = [];
            foreach($data as $key => $value){
                $data_arr[$key]['aName'] = $value[0];
                $data_arr[$key]['aEduBackground'] = $value[2];
                $data_arr[$key]['aJobTitle'] = $value[3];
                $data_arr[$key]['aUnit'] = $value[5];
                $data_arr[$key]['sSex'] = $value[6];
                $data_arr[$key]['aJobNumber'] = $value[7];
                $data_arr[$key]['aisAddress'] = $value[8];
                $data_arr[$key]['aArticleNumber'] = $accession_number;
                $data_arr[$key]['aIsCliam'] = $value[7]==$this->session->job_number?1:0;
            }
            $this->load->model('Author_model','author');
            $status = $this->author->insertAuthor($data_arr);
            
            if($status==0)
            {
                exit(JsonEcho('1','文章认领失败'));
            }else{
                echo JsonEcho('0','文章认领成功',$data_arr);
            }
        } else{
            exit(JsonEcho('1','抱歉，不能认领不属于自己的文章'));
        }
            
    }

    public function doClaimArticle()
    {

    }
}
