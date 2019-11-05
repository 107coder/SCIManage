<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Article_model extends CI_Model{

    // 获取所有的论文的分类
    public function getType($page,$limit)
    {
        return $this->db
            ->limit($limit,$page)
            ->from('subject')->get()->result_array();
    }
    // 获取所有的论文信息
    public function getArticle($page,$limit)
    {
        return $this->db
                    ->limit($limit,$page)
                    ->select('title,author,source,address,quite_time,is_top,roll,period,date,year,page,is_first_inst,impact_factor,subject,sci_type,other_info,articleStatus')
                    ->from('article')->get()->result_array();
    }


}