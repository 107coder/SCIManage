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
}