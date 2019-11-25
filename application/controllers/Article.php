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
        
        if(empty($key)){
            $data = $this->article->getArticle($page,$limit);
            $count = $this->db->count_all('article');
        }else{
            $data = $this->article->searchArticle($page,$limit,$key);
            $count = $this->article->searchArticleCount($key);
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
     * 获取当前登录着的姓名全拼，填写的搜索框中
     *
     * @return void
     */
    public function getFullSpell(){

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
            
            $address = $value['address'];
            $author = $value['author'];
            p("通讯作者：".$address);
            p("所有作者：".$author);
            $claim_author = $this->searchFullSpell($address,$author);

            // if(!in_array($first_author,$claim_author)){
            //     array_push($claim_author,$first_author);
            // }
            // $claim_author = implode(';',$claim_author);
            p("认领作者：".$claim_author);
            echo '---</br>';
            // $data_arr = array('claim_author'=>$claim_author);
            // $where = array('accession_number'=>$value['accession_number']);
            // $this->article->updateArticle($data_arr,$where);
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
        // $accession_number = 'WOS:000454836700027';
        // $tableData = '[["崔少峰","Li, Rui","","","计算机与信息工程学院","本校教师","男","17101211","否","127"],["刘瑞欣","Huang, Xiaowei","","","文学院","本校研究生","女","104752100022","否","128"],["刘瑞欣","Ma, Xiaoyu","","","文学院","本校研究生","女","104752100022","否","129"],["刘瑞欣","Zhu, Zhili","","","文学院","本校研究生","女","104752100022","否","130"],["刘瑞欣","Li, Chong","","","文学院","本校研究生","女","104752100022","否","131"],["马征","Xia, Congxin","博士研究生","副教授","黄河文明与可持续发展研究中心","本校教师","女","10010003","否","132"],["马征","Zeng, Zaiping","博士研究生","副教授","黄河文明与可持续发展研究中心","本校教师","女","10010003","否","133"],["马征","Jia, Yu","博士研究生","副教授","黄河文明与可持续发展研究中心","本校教师","女","10010003","否","134"]]';
        // 从article表中获取相关信息，检查是否能够认领并且是否用认领的权限
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
                $data_arr[$key]['aIsCliam'] = $value[7]==$this->session->job_number?1:0;
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
            'articleStatus' => 0
        ];
        // 重置论文的认领信息
        $where = ['accession_number'=>$accession_number];
        $status = $this->article->backArticle($data,$where);
        
        // 删除作者的信息
        $where = ['aArticleNumber'=>$accession_number];
        $this->load->model('Author_model','author');
        // 不删除作者的信息
        // $status2 = $this->author->deleteArticle($where);

        if($status)
        {
            echo JsonEcho('0','文章退回成功');
        }else{
            exit(JsonEcho('1','文章退回失败'));
        }
    }


    /**
     * 根据通讯作者中的简写，在所有作者中查找出来所有通讯作者的全拼
     *
     * @param [type] $address
     * @param [type] $author
     * @return void
     */    
    function searchFullSpell($address,$author){
        
        //把作者中的姓名全拼分为数组   这里涉及到两种格式，判断姓名的分类中是否有 ','分割，
        if(strpos($author,',') == false){
            $author=str_replace('-', '', $author);
            $authorArray=explode('; ', $author);
            foreach($authorArray as &$author){
                $author=str_replace(', ', ',', $author);
                $author=str_replace(' ', ',', $author);
            }
        }else{
            $author=str_replace(' ', '', $author);
            $author=str_replace('-', '', $author);
            $authorArray=explode(';', $author);
        }

       
       

        //把地址中的姓名简写分为数组  先根据';'分成数组，然后判断是否含有(reprintauthor)如果有截取前面的字符
        $address=str_replace(' ', '', $address);
        $addressArray=explode(';', $address);
        $addressArrayLen=count($addressArray);
        for ($i=0; $i < $addressArrayLen; $i++) 
        { 
            if(strstr($addressArray[$i], "(reprintauthor)")!=false)
            {
                $pos=strpos($addressArray[$i],"(reprintauthor)");
                $addressArray[$i]=substr($addressArray[$i], 0,$pos);
            }
                
        }
        // $fullSpellArray  = [];
        //对每个简写查找它的全拼以数组形式返回
        foreach ($addressArray as $value)
        {
            $short=$value;
            $short=strtolower($short);
            $shortLowerArray=explode(',', $short);
            foreach ($authorArray as $author)
            {
                $fullSpell=strtolower($author);
                $authorLowerArray=explode(',', $fullSpell);
                $bool=true;
                //判断二者逗号前是否相同
                if(strstr($authorLowerArray[0], $shortLowerArray[0])==false)
                    $bool=false;
                //判断缩写逗号后的字母是否都在全拼逗号后的字符里
                if(strpos($fullSpell,',') != false)
                {
                    $length=strlen($shortLowerArray[1]);
                    for ($i=0; $i <$length ; $i++)
                    { 
                        if(strstr($authorLowerArray[1], $shortLowerArray[1][$i])==false)
                            $bool=false;
                    }
                }
                if($bool)
                {
                    $fullSpellArray[]=$author;
                }
            }
        }
        if(isset($fullSpellArray))
            $claim_author = $fullSpellArray;
        else
            $claim_author = array();
        
        if(!in_array($first_author,$claim_author)){
            array_push($claim_author,$first_author);
        }
        $claim_author = implode(';',$claim_author);
        return $claim_author; return $fullSpellArray;

        
    }


    public function test(){
        $author = 'Xu, Dang-Dang; Zheng, Bei; Song, Chong-Yang; Lin, Yi; Pang, Dai-Wen; Tang, Hong-Wu';
        $author=str_replace('-', '', $author);
        $authorArray=explode('; ', $author);
        foreach($authorArray as &$author){
            $author=str_replace(', ', ',', $author);
            $author=str_replace(' ', ',', $author);
            p($author);
        }

        p($authorArray);
    }
}
