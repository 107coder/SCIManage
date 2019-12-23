<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage extends MY_Controller {

    private $where = [];
    public function __construct(){
        parent::__construct();
        $this->load->model('Article_model','article');
        $this->load->model('Citation_model','citation');

        // 管理员权限控制
        if($this->session->identity != 2){
            $this->where = ['claimer_unit'=>$this->session->academy];
        }
    }
    // ================================   sci论文管理     ==============================
     /**
     * 添加sci文章
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
     * 管理员 执行审核论文通过操作
     *
     * @return void
     */
    public function passArticle(){
        $accession_number = $this->input->post('accession_number');
        $where = $this->where;
        $where['accession_number']=$accession_number;
        if($this->session->identity==2){
            $data_arr = ['articleStatus'=>5];
        }else if($this->session->identity == 1){
            $data_arr = ['articleStatus'=>3];
        }else{
            exit(JsonEcho(1,'没有操作权限'));
        }
        $res = $this->article->updateArticle($data_arr,$where);
        if(!$res){
            exit(JsonEcho(1,'审核通过失败，请稍后重试'));
        }else{
            exit(JsonEcho(0,'审核通过成功'));
        }
    }

    public function multiPassArticle(){
        $accession_number = $this->input->post('accession_number');
        $data_arr = [];
        if($this->session->identity==2){
            foreach ($accession_number as $value){
                $data_one[0] = array(
                    'accession_number' => $value,
                    'articleStatus' => 6    // 用于批量审核，增加一个状态 6
                );
                $data_arr = array_merge($data_arr,$data_one);
            }
        }else if($this->session->identity == 1){
//            $data_arr = ['articleStatus'=>3];
            exit(JsonEcho(1,'权限不够，不能执行操作'));
        }else{
            exit(JsonEcho(1,'没有操作权限'));
        }
//        exit(JsonEcho(0,'审核成功',$data_arr));
        $res = $this->article->multiUpdateArticle($data_arr,'accession_number');
        if(!$res){
            exit(JsonEcho(1,'审核通过失败，请稍后重试'));
        }else{
            exit(JsonEcho(0,'批量审核通过成功'));
        }
    }
    /**
     * 管理员执行论文不通过操作
     *
     * @return void
     */
    public function backArticle(){
        $accession_number = $this->input->post('accession_number');
        $where = $this->where;
        $where['accession_number']=$accession_number;
        if($this->session->identity==2){
            $data_arr = ['articleStatus'=>4];
        }else if($this->session->identity == 1){
            $data_arr = ['articleStatus'=>2];
        }else{
            exit(JsonEcho(1,'没有操作权限'));
        }
        $res = $this->article->updateArticle($data_arr,$where);
        if(!$res){
            exit(JsonEcho(1,'审核操作失败，请稍后重试'));
        }else{
            exit(JsonEcho(0,'论文处理成功'));
        }
    }
    /**
     * 管理员执行论文不通过操作
     *
     * @return void
     */
    public function cancalArticle(){
        $accession_number = $this->input->post('accession_number');
        $where = $this->where;
        $where['accession_number']=$accession_number;
        if($this->session->identity==2){
            $data_arr = ['articleStatus'=>3];
        }else if($this->session->identity == 1){
            $data_arr = ['articleStatus'=>1];
        }else{
            exit(JsonEcho(1,'没有操作权限'));
        }
        $res = $this->article->updateArticle($data_arr,$where);
        if(!$res){
            exit(JsonEcho(1,'审核操作失败，请稍后重试'));
        }else{
            exit(JsonEcho(0,'论文取消通过成功'));
        }
    }

    // ================================   论文他引管理     ==============================
    
    public function deleteCitation(){
        $citation_number = $this->input->post('citation_number');
        $where = $this->where;
        $where['citation_number'] = $citation_number;
        
        $status = $this->citation->deleteCitation($where);
        if($status === false){
            exit(JsonEcho(1,'删除失败'));
        }else{
            exit(JsonEcho(0,'删除成功'));
        }
    }

    public function resetCitation(){
        $citation_number = $this->input->post('citation_number');
        $where = $this->where;
        $where['citation_number'] = $citation_number;

        $cols = 'claim_author,status,claim_time';
        // 获取改文章的一些验证数据
        $citationData = $this->citation->getCitationByColName($where,$cols);
        // 检测文章是否已经被认领
        if($citationData[0]['status']==0){
            exit(JsonEcho(1,'文章未被认领，无需重置'));
        }
        $data_arr = array(
            'claimer_name' => NULL,
            'claimer_number' => NULL,
            'claimer_unit' => NULL,
            'status'       => 0,
            'claim_time' => time()
        );
        $status = $this->citation->updateCitation($where,$data_arr);
        if($status === false){
            exit(JsonEcho(1,'重置失败'));
        }else{
            exit(JsonEcho(0,'重置成功'));
        }
    }
}