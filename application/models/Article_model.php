<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Article_model extends CI_Model{

    // =========================   SCI论文部分   ==============================
    // 获取所有的论文的分类
    public function getType($page=null,$limit=null)
    {
        if($page==null && $limit==null)
        {
            return $this->db
                ->from('subject')->get()->result_array();
        }
        else
        {
            return $this->db
                ->limit($limit,$page)
                ->from('subject')->get()->result_array();
        }


    }

    // 获取所有的论文信息
    public function getArticle($page=null,$limit=null)
    {
        if($page==null && $limit==null){
            return $this->db
//                    ->limit($limit,$page)
                // ->select('accession_number,title,author,source,address,quite_time,is_top,roll,period,date,year,page,is_first_inst,impact_factor,subject,sci_type,other_info,articleStatus')
                ->from('article')->get()->result_array();
        }else{
            return $this->db
                    ->limit($limit,$page)
                // ->select('accession_number,title,author,source,address,quite_time,is_top,roll,period,date,year,page,is_first_inst,impact_factor,subject,sci_type,other_info,articleStatus')
                ->from('article')->get()->result_array();
        }

    }

    /**
     * 文章搜索
     *
     * @param [type] $page
     * @param [type] $limit
     * @param [type] $key
     * @return array
     */
    public function searchArticle($page,$limit,$key)
    {
        return $this->db
                    ->from('article')
                    ->like('accession_number',$key)
                    ->or_like('title' ,$key)
                    ->or_like('author' ,$key)
                    ->or_like('claim_author' ,$key)
                    ->order_by('accession_number')
                    ->limit($limit,$page)
                    ->get()->result_array();
    }
    public function searchArticleCount($key){
        return $this->db
            ->from('article')
            ->like('accession_number',$key)
            ->or_like('title' ,$key)
            ->or_like('author' ,$key)
            ->or_like('claim_author' ,$key)
            ->order_by('accession_number')
            ->count_all_results();
    }

    /**
     * 根据状态搜索文章，管理员操作
     *
     * @param [type] $page
     * @param [type] $limit
     * @param [type] $key
     * @return array
     */
    public function selectStatus($page,$limit,$key){
        if($key != '-1')
        {
            $where=array('articleStatus'=>$key);
        }
        else{
            $where=array();
        }
        return $this->db
                    ->from('article')
                    ->where($where)
                    ->order_by('accession_number')
                    ->limit($limit,$page)
                    ->get()->result_array();
    }
    public function selectStatusCount($key){
        if($key != '-1')
        {
            $where=['articleStatus'=>$key];
        }
        else{
            $where=array();
        }
        return $this->db
            ->from('article')
            ->where($where)
            ->count_all_results();
    }
    /**
     * 判断所添加的文章是否存在 存在返回false 不存在返回true
     *
     * @param [string] $accession_number
     * @return bool
     */
    public function checkArticleExist($accession_number)
    {
        $res = $this->db->where(array('accession_number'=>$accession_number))->from('article')->count_all_results();
        $data = $res==0?true:false;
        return $data;
    }
    /**
     * 手动添加一篇文章
     *
     * @param [array] $data_arr
     * @return 添加的条数
     */
    public function insertArticle($data_arr)
    {
        return $this->db->insert('article',$data_arr);
    }

    /**
     * 更新文章
     *
     * @param $data_arr 传入要修改的数据
     * @param $where    修改的条件
     * @return bool
     */
    public function updateArticle($data_arr,$where)
    {
        return $this->db->update('article',$data_arr,$where);
    }

    /**
     * 批量更新sci文章，主要是用于批量审核通过
     * @param $data_arr
     * @param $where
     * @return mixed
     */
    public function multiUpdateArticle($data_arr,$where){
        return $this->db->update_batch('article',$data_arr,$where);
    }

    // 检测文章是否可以认领
    public function checkArticle($where){
        return $this->db->select('claim_author,owner,claim_time,articleStatus,owner_name,claimer_unit')->where($where)->from('article')->get()->result_array();
    }

    /**
     * 获取文章表中符合条件的某个或者某些属性
     *
     * @param [array] $where
     * @param string $cols
     * @return array
     */
    public function getAnyArticle($where,$cols='')
    {
        return $this->db->select($cols)->get_where('article',$where)->result_array();
    }

    /**
     * 文章退回时初始化信息
     *
     * @param [type] $data
     * @param [type] $where
     * @return array
     */
    public function backArticle($data,$where)
    {
        return $this->db->update('article',$data,$where);
    }


    /**
     * 管理员认领功能的 文章加载
     *
     * @param [type] $limit
     * @param [type] $page
     * @param [type] $where
     * @param array $like
     * @return array
     */
    public function getArticleByAcademy($limit,$page,$where,$like=[]){
         $this->db->from('article');
         $this->db->where($where);
        if(isset($where['articleStatus']) && $where['articleStatus'] == 5)
            $this->db->or_where('articleStatus = ',6);
         $this->db->like($like)->limit($limit,$page)->order_by("accession_number");

        $res['data'] = $this->db->get()->result_array();

        $this->db->from('article')->where($where);
        if(isset($where['articleStatus']) && $where['articleStatus'] == 5)
            $this->db->or_where('articleStatus = ',6);
        $res['count'] = $this->db->like($like)->count_all_results();
        return $res;
    }

    
}