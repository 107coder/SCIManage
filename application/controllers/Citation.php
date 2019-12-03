<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Citation extends MY_Controller {

    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Citation_model','citation');
    }

    public function getCitation(){
        $limit = $this->input->get('limit');
        $page = $this->input->get('page')-1;
        $key = $this->input->get('key');
        $type = $this->input->get('selectType');

        if(empty($type)){
            $data = $this->citation->getCitation($limit,$page,$key);
            $count = $this->citation->getCitationCount($key);
        }else{
            $data = $this->citation->selectStatus($page,$limit,$key);
            $count = $this->citation->selectStatusCount($key);
        }
        // 年份处理
        $data = $this->deal_citation_time($data,_year());
        $data = $this->deal_citation_time($data,'2018');
        $data_json =array(
            'code' => 0,
            'msg'  => '数据请求成功',
            'count' => $count,
            'data' => $data
        );
        // p($data);
        echo json_encode($data_json,256);
    }

    /**
     * 认领文章
     *
     * @return void
     */
    public function claimCitation(){
        $citation_number = $this->input->post('citation_number');
        if(empty($citation_number)){
           exit(JsonEcho(2,'参数错误')); 
        }
        $where = ['citation_number'=>$citation_number];
        $cols = 'claim_author,status,claim_time';
        // 获取改文章的一些验证数据
        $citationData = $this->citation->getCitationByColName($where,$cols);
        // 检测文章是否已经被认领
        if($citationData[0]['status']!=0){
            exit(JsonEcho(1,'文章已被认领'));
        }
        // 检测认领者是否有权限认领
        if(!$this->isAllowClaim($citationData)){
            exit(JsonEcho(2,'您没有权限认领'));
        }
        
        $data_arr = array(
            'claimer_name' => $this->session->name,
            'claimer_number' => $this->session->job_number,
            'claimer_unit' => $this->session->academy,
            'status'       => 1,
            'claim_time' => time()
        );
        $status = $this->citation->updateCitation($where,$data_arr);
        if($status === false){
            exit(JsonEcho(1,'认领失败，请稍后重试'));
        }else{
            echo JsonEcho(0,'认领成功',['a'=>$status]);
        }
    }
    /**
     * 根据传入的数据，判断是否允许该作者认领
     *
     * @param [type] $articleData
     * @param [type] $full_name
     * @return boolean
     */
    public function isAllowClaim($articleData,$full_spell=null){
        if($full_spell==null){
            $full_spell = $this->session->full_spell;
        }
        $yourName = strtolower($full_spell);
        $claim_author = explode(';',strtolower($articleData[0]['claim_author']));
        return in_array($yourName,$claim_author);
    }

    /**
     * 获取当前用户认领的所有的文章
     *
     * @return void
     */
    public function myCitation(){
        $limit = $this->input->get('limit');
        $page = $this->input->get('page')-1;
        
        $where = ['claimer_number'=>$this->session->job_number];
        $data = $this->citation->getCitationByColName($where);
        
        // 年份处理
        $data = $this->deal_citation_time($data,_year());
        $data = $this->deal_citation_time($data,'2018');

        $data_json =array(
            'code' => 0,
            'msg'  => '数据请求成功',
            'count' => $this->db->where($where)->from('citation')->count_all_results(),
            'data' => $data
        );
        
        echo json_encode($data_json,256);
    }

    /**
     * 他引文章退回
     *
     * @return void
     */
    public function backCitation(){
        $citation_number = $this->input->post('citation_number');
        if(empty($citation_number))
            exit(JsonEcho(1,'参数错误'));
        $where = ['citation_number'=>$citation_number];
        $data_arr = array(
            'claimer_name' => NULL,
            'claimer_number' => NULL,
            'claimer_unit' => NULL,
            'status'       => 0,
            'claim_time' => time()
        );

        $status = $this->citation->updateCitation($where,$data_arr);
        if($status===false){
            exit(JsonEcho(1,'退回失败，请稍后重试'));
        }else{
            echo JsonEcho(0,'退回成功');
        }
    }




    public function deal_citation_time($article,$update_time)//处理citation_time字段
    {
        foreach ($article as &$v)      //循环处理每条记录里的citation_time
        {
            $increase_claim=0;
            $claim_time=explode("-",$v['citation_time']);//将citation_time字段分解为数组
            foreach ($claim_time as $c) {     //对数组进行查找

                if(substr($c,0,strpos($c, ':'))==$update_time)// 截取数组中每个元素 “:”前的字符串，如果与查找条件$begin_time相等
                {                                                              //则截取“:”后的字符串赋给$begin_claim
                    $increase_claim=substr($c,strpos($c, ':')+1);             // 处理字符串 $c 从冒号后面的位置开始，到最后 
                }
            }
            $v[$update_time.'_'.'time']=$increase_claim;
        }
        return $article;
    }
}