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


    /**
     * 获取所有文章的内容
     *
     * @return void
     */
    public function getArticleApi()
    {
        $page = $this->input->get('page')-1;
        $limit = $this->input->get('limit');

        $key = $this->input->get('key');
        $type = $this->input->get('selectType');
      
        if(empty($key) && empty($type)){
            $data = $this->article->getArticle($page,$limit);
            $count = $this->db->count_all('article');
        }else if(empty($type)){
            $data = $this->article->searchArticle($page,$limit,$key);
            $count = $this->article->searchArticleCount($key);
        }else{
            $data = $this->article->selectStatus($page,$limit,$key);
            $count = $this->article->selectStatusCount($key);
        }
        $resdata = array(
            'code' => '0',
            'msg'  => '请求数据正常',
            'count'=> $count,
            'data' => $data
        );
        echo json_encode($resdata,JSON_UNESCAPED_UNICODE);
    }

     /**
     * 根据学院获取文章的内容
     *
     * @return void
     */
    public function getArticleByAcademyApi()
    {
        $page = $this->input->get('page')-1;
        $limit = $this->input->get('limit');

        $key = $this->input->get('key');
        $type = $this->input->get('selectType');
        if(!isset($this->session->identity)){
            exit(JsonEcho(4,'请先登录'));
        }
        if($this->session->identity == 2){
            $where = [];
        }else{
            $where = ['claimer_unit'=>$this->session->academy];
        }
        
        if($type == 'articleStatus'){
            if($key!=-1)
                $where['articleStatus']=$key;
            $like = [];
        }else{
            $like=array(
                'accession_number' => $key,
                'title'            => $key,
                'author'           => $key,
                'claim_author'     => $key
            );
        }
        $res = $this->article->getArticleByAcademy($limit,$page,$where,$like);
        $data = $res['data'];
        $count = $res['count'];

        $resdata = array(
            'code' => '0',
            'msg'  => '请求数据正常',
            'count'=> $count,
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
   
   
    

    
    public function verifyClaimAuthority()
    {
        // 获取文章wos号码
        $accession_number = $this->input->post('accession_number');
        // $accession_number = 'WOS:000454084300011';
        // 拼接出 where 查询条件
        $where = ['accession_number'=>$accession_number];
        $data = $this->article->checkArticle($where);

        // 将所有的名字都换成小写，判断是否可以认领
        $yourName = strtolower($this->session->full_spell);
        $claim_author = explode(';',strtolower($data[0]['claim_author']));
        $resData = ['articleStatus'=> $data[0]['articleStatus']];
        if(in_array($yourName,$claim_author))
        {
            echo JsonEcho('0','您可认领这篇文章',$resData);
        } else{
            exit(JsonEcho('1','该篇文章只允许第一作者和通讯作者认领，您不能认领，只能查看，如有疑问请联系系统管理员！',$resData));
        }
    }

    /**
     * 文章认领
     *
     * @return void
     */
    public function claimArticle()
    {
        // 从前端获取数据
        $accession_number = $this->input->post('accession_number');
        $tableData = $this->input->post('tableData');
        
        $where = ['accession_number'=>$accession_number];
        $articleData = $this->article->checkArticle($where);
        // 将所有的名字都换成小写，判断是否可以认领
        $yourName = strtolower($this->session->full_spell);
        $claim_author = explode(';',strtolower($articleData[0]['claim_author']));
        
        if(in_array($yourName,$claim_author))
        {
            if($articleData[0]['owner']!=null || $articleData[0]['articleStatus']!=0)
            {
                exit(JsonEcho('1','文章已被认领'));
            }

            // 首先更新 article 表中的数据
            $data_article = array(
                'owner' => $this->session->job_number,
                "owner_name"=> $this->session->name,
                'claimer_unit' => $this->session->academy,
                'articleStatus' => 1,
                'claim_time' => time()
            );
            $res = $this->article->updateArticle($data_article,$where);
            if(!$res)
            {
                exit(JsonEcho('1','文章认领失败，请稍后重试'));
            }
            $authorData = json_decode($tableData,true);
            // 需要插入的数据，在下面进行拼接
            $data_arr = [];
            foreach($authorData as $key => $value){
                $data_arr[$key]['aName'] = $value[0];
                $data_arr[$key]['aFull_spell'] = $value[1];
                $data_arr[$key]['aEduBackground'] = $value[2];
                $data_arr[$key]['aJobTitle'] = $value[3];
                $data_arr[$key]['aUnit'] = $value[4];
                $data_arr[$key]['atype'] = $value[5];
                $data_arr[$key]['sSex'] = $value[6];
                $data_arr[$key]['aJobNumber'] = $value[7];
                $data_arr[$key]['aisAddress'] = $value[8];
                $data_arr[$key]['aArticleNumber'] = $accession_number;
                $data_arr[$key]['aIsClaim'] = $value[7]==$this->session->job_number?1:0;
                if($value[9] != -1){
                    $data_arr[$key]['aId'] = $value[9];
                }
                
            }
            $this->load->model('Author_model','author');
            
            if($articleData[0]['claim_time']==NULL || $articleData[0]['claim_time']==''){
                $status = $this->author->insertAuthor($data_arr);
            }else{
                $where = ['aArticleNumber'=>$accession_number];
                $status = $this->author->updateAuthor($data_arr,$where);
                // var_dump($status);
            }
            
            if($status === false)
            {
                exit(JsonEcho('1','论文认领失败'));
            }else{
                echo JsonEcho('0','论文认领成功');
            }
        } else{
            exit(JsonEcho('1','抱歉，不能认领不属于自己的文章'));
        }
            
    }


    



    // 请求我的sci论文的列表
    public function mySciArticle()
    {
        $job_number = $this->session->job_number;

        $where = ['owner'=>$job_number];
        $data = $this->article->getAnyArticle($where);
        $count = $this->db->where($where)->from('article')->count_all_results();
        $data = array(
            'code' => '0',
            'msg'  => '请求数据正常',
            'count'=> $count,
            'data' => $data
        );
        echo json_encode($data,256);
    }

    // 文章退回
    public function backArticle()
    {
        $accession_number = $this->input->post('accession_number');

        $data = [
            'owner' => null,
            'owner_name' => null,
            'claimer_unit' => null,
            'articleStatus' => 0
        ];
        // 重置论文的认领信息
        $where = ['accession_number'=>$accession_number];
        $status = $this->article->backArticle($data,$where);
        
        // 删除作者的信息 引入对应model
        // $where = ['aArticleNumber'=>$accession_number];
        // $this->load->model('Author_model','author');
        // 不删除作者的信息
        // $status2 = $this->author->deleteArticle($where);

        if($status)
        {
            echo JsonEcho('0','文章退回成功');
        }else{
            exit(JsonEcho('1','文章退回失败'));
        }
    }


}
