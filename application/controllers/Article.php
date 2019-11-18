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
        $accession_number = 'WOS:000454084300011';
        $tableData = '[{"full_spell":"Qin, Peige","name":"1","sex":"","Number":"17101211","Xueli":"1","Title":"1","Tongxun":"","Unit":"1","LAY_TABLE_INDEX":0},{"full_spell":"Yang, Yixin","name":"1","sex":"","Number":"1","Xueli":"1","Title":"1","Tongxun":"","Unit":"1","LAY_TABLE_INDEX":1},{"full_spell":"Li, Wenqi","name":"1","sex":"","Number":"1","Xueli":"1","Title":"1","Tongxun":"","Unit":"1","LAY_TABLE_INDEX":2},{"full_spell":"Zhang, Jing","name":"1","sex":"","Number":"1","Xueli":"1","Title":"1","Tongxun":"","Unit":"1","LAY_TABLE_INDEX":3},{"full_spell":"Zhou, Qian","name":"11","sex":"","Number":"1","Xueli":"1","Title":"11","Tongxun":"","Unit":"1","LAY_TABLE_INDEX":4},{"full_spell":"Lu, Minghua","name":"1","sex":"","Number":"1","Xueli":"1","Title":"1","Tongxun":"","Unit":"1","LAY_TABLE_INDEX":5}]';
        $where = ['accession_number'=>$accession_number];
        $data = $this->article->checkArticle($where);
        $yourName = $this->session->full_spell;
        $first_author = $data[0]['first_author'];
        if(strnatcasecmp($yourName,$first_author) == 0)
        {
            $data = json_decode($tableData,true);
            $data_arr = [];
            foreach($data as $key => $value){
                $data_arr[$key]['aName'] = $value['name'];
                $data_arr[$key]['aJobNumber'] = $value['Number'];
                $data_arr[$key]['sSex'] = $value['sex'];
                $data_arr[$key]['aEduBackground'] = $value['Xueli'];
                $data_arr[$key]['aJobTitle'] = $value['Title'];
                $data_arr[$key]['aisAddress'] = $value['Tongxun'];
                $data_arr[$key]['aUnit'] = $value['Unit'];
                $data_arr[$key]['aArticleNumber'] = $accession_number;
                $data_arr[$key]['aIsCliam'] = $value['Number']==$this->session->job_number?1:0;
            }
            $this->load->model('Author_model','author');
            $status = $this->author->insertAuthor($data_arr);
            var_dump($status);
            if($status==0)
            {
                exit(JsonEcho('2','插入失败'));
            }else{
                echo JsonEcho('0','插入成功',$data_arr);
            }
        } else{
            exit(JsonEcho('1','抱歉，不能认领属于自己的文章'));
        }
            
    }

    public function doClaimArticle()
    {

    }
}
