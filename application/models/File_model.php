<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File_model extends CI_Model
{
    // 返回数据库中的条数，判断数据是否存在
    public function articleExist($where)
    {

        $this->db->where($where);
        $this->db->from('article');
        return $this->db->count_all_results();
    }
    // 插入文章
    public function articleInsert($data_arr)
    {
        return $this->db->insert_batch('article',$data_arr);
    }
    // 更新文章
    public function articleUpdate($where,$data_arr)
    {
        return $this->db->update('article',$data_arr,$where);
    }

    //插入文章分类
    public function typeInsert($data_arr)
    {
        return $this->db->insert_batch('subject',$data_arr);
    }

}