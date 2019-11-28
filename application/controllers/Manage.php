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