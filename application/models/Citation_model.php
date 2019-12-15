<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Citation_model extends CI_Model{
     

    /**
     * 手动单独添加一条他引文章
     *
     * @param [type] $where
     * @param [type] $data_arr
     * @return void
     */
    public function insertCitation($where,$data_arr){
        $this->db->insert('citation',$data_arr,$where);
    }

    /**
     * 获得文章认领页面的文章列表
     *
     * @param [type] $limit
     * @param [type] $page
     * @param [type] $key
     * @return array
     */
    public function getCitation($limit,$page,$key=null){
        return $this->db
                    ->from('citation')
                    ->like('citation_number',$key)
                    ->or_like('title' ,$key)
                    ->or_like('author' ,$key)
                    ->or_like('claim_author' ,$key)
                    ->order_by('citation_number')
                    ->limit($limit,$page)
                    ->get()->result_array();
    }
    public function getCitationCount($key=null){
        return $this->db
                    ->from('citation')
                    ->like('citation_number',$key)
                    ->or_like('title' ,$key)
                    ->or_like('author' ,$key)
                    ->or_like('claim_author' ,$key)
                    ->count_all_results();
    }


    /**
     * 查询论文的的状态
     *
     * @param [type] $page
     * @param [type] $limit
     * @param [type] $key
     * @return array
     */
    public function selectStatus($page,$limit,$key){
        if($key != '-1')
        {
            $where=['status'=>$key];
        }
        else{
            $where=array();
        }
        return $this->db
                    ->from('citation')
                    ->where($where)
                    ->order_by('citation_number')
                    ->limit($limit,$page)
                    ->get()->result_array();
    }
    public function selectStatusCount($key){
        if($key != '-1')
        {
            $where=['status'=>$key];
        }
        else{
            $where=array();
        }
        return $this->db
                    ->from('citation')
                    ->like($where)
                    ->count_all_results();
    }

    /**
     * 通过条件获取对应的列
     *
     * @param [type] $where
     * @param string $cols
     * @return array
     */
    public function getCitationByColName($where,$cols=''){
        return $this->db->select($cols)->get_where('citation',$where)->result_array();
    }

    /**
     * 文章认领动作操作数据库，更新相关信息
     *
     * @param [type] $where
     * @param [type] $data_arr
     * @return array
     */
    public function updateCitation($where,$data_arr){
        return $this->db->update('citation',$data_arr,$where);
    }

    /**
     * 根据where条件删除论文
     *
     * @param [type] $where
     * @return int
     */
    public function deleteCitation($where){
        return $this->db->delete('citation',$where);
    }
}