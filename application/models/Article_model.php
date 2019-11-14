<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Article_model extends CI_Model{

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
    public function getArticle($page,$limit)
    {
        return $this->db
                    ->limit($limit,$page)
                    // ->select('accession_number,title,author,source,address,quite_time,is_top,roll,period,date,year,page,is_first_inst,impact_factor,subject,sci_type,other_info,articleStatus')
                    ->from('article')->get()->result_array();
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


}