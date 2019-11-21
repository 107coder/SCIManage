<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Author_model extends CI_Model {
    
    /**
     * 插入作者数据的方法
     *
     * @param [type] $data_arr 所有用户信息的列表
     * @return 插入成功的条数
     */
    public function insertAuthor($data_arr)
    {
        return $this->db->insert_batch('author',$data_arr);
    }

    public function getAuthorClaimArticle($where)
    {
        return $this->db->get_where('author',$where)->result_array();
    }

    /**
     * 退回文章的时候删除文章对应的作者
     *
     * @return void
     */
    public function deleteArticle($where){
        return $this->db->delete('author',$where);
    }

    public function getPostgraduate($where){
        return $this->db->where($where)->get("student")->result_array();
    }
}