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

    public function getAllSciArticleToExport($where=array()){
        $unit = $this->db
                     ->select('academy')
                     ->distinct()
                     ->from('user')
                     ->get()
                     ->result_array();

        $resultData = [];
        foreach ($unit as $val){
            $where['claimer_unit'] = $val['academy'];
            $data = $this->db
                ->where($where)
                ->order_by('articleStatus','DESC')
                ->from('article')
                ->get()
                ->result_array();
//            var_dump($where);
//            exit();
            if(empty($data)){
                continue;
            }
//            $redis = new Redis();
//            $redis->connect('127.0.0.1','6379') or exit(JsonEcho(1,'服务器异常，请联系技术人员！'));
            foreach($data as $key => &$value){
                /*             $curNumber = $value['accession_number'];
                            $redis->Lpush('sciExportLink',$curNumber);
                            $redis->set('sciExport:'.$curNumber.':title',$value['title']);
                            $redis->set('sciExport:'.$curNumber.':author',$value['author']);
                            $redis->set('sciExport:'.$curNumber.':source',$value['source']);
                            $redis->set('sciExport:'.$curNumber.':article_type',$value['article_type']);
                            $redis->set('sciExport:'.$curNumber.':address',$value['address']);
                            $redis->set('sciExport:'.$curNumber.':email',$value['email']);
                            $redis->set('sciExport:'.$curNumber.':organization',$value['organization']);
                            $redis->set('sciExport:'.$curNumber.':quite_time',$value['quite_time']);
                            $redis->set('sciExport:'.$curNumber.':source_shorthand',$value['source_shorthand']);
                            $redis->set('sciExport:'.$curNumber.':is_top',$value['is_top']);
                            $redis->set('sciExport:'.$curNumber.':roll',$value['roll']);
                            $redis->set('sciExport:'.$curNumber.':period',$value['period']);
                            $redis->set('sciExport:'.$curNumber.':date',$value['date']);
                            $redis->set('sciExport:'.$curNumber.':year',$value['year']);
                            // 这里的页码需要处理
                            $page = explode($value['page'],'--');
                            $redis->set('sciExport:'.$curNumber.':startPage',$page[0]);
                            $redis->set('sciExport:'.$curNumber.':endPage',$page[1]);
                            $redis->set('sciExport:'.$curNumber.':is_first_inst',$value['is_first_inst']);
                            $redis->set('sciExport:'.$curNumber.':impact_factor',$value['impact_factor']);
                            $redis->set('sciExport:'.$curNumber.':subject',$value['subject']);
                            $redis->set('sciExport:'.$curNumber.':zk_type',$value['zk_type']);
                            $redis->set('sciExport:'.$curNumber.':is_cover',$value['is_cover']==1?'是':'否');
                            $redis->set('sciExport:'.$curNumber.':sci_type',$value['sci_type']);
                            $redis->set('sciExport:'.$curNumber.':reward_point',$value['reward_point']);
                            $redis->set('sciExport:'.$curNumber.':other_info',$value['other_info']);
                            $redis->set('sciExport:'.$curNumber.':owner',$value['owner']);
                            $redis->set('sciExport:'.$curNumber.':owner_name',$value['owner_name']);
                            $redis->set('sciExport:'.$curNumber.':claimer_unit',$value['claimer_unit']);
                            p($value);
                            exit();
                            */

                $page = explode('--',$value['page']);
                $value['startPage'] = $page[0];
                $value['endPage'] = $page[1];

                $author = $this->db->select('aName,aJobNumber,aisAddress,aUnit,aIsClaim')
                    ->where(['aArticleNumber'=>$value['accession_number']])
                    ->from('author')
                    ->get()->result_array();
                $first_author = [];
                $first_author_number = [];
                $reprint_author = [];
                $other_author = [];
                foreach($author as $k => $v){
                    if($k == 0){
                        array_push($first_author,$v['aName']);
                        array_push($first_author_number,$v['aJobNumber']);
                    }else if($v['aisAddress']=='是'){
                        array_push($reprint_author,$v['aName']);
                    }else{
                        array_push($other_author,$v['aName']);
                    }
                }
                $value['first_author'] = implode(',',$first_author);
                $value['first_author_number'] = implode(',',$first_author_number);
                $value['reprint_author'] = implode(',',$reprint_author);
                $value['other_author'] = implode(',',$other_author);


            }
            $resultData[$val['academy']]=$data;
//            p($data);
        }
        // echo $redis->rPop();
        // 对于为认领的论文，做格式化操作
        $data  = $this->db
                    ->where(['claimer_unit'=>NULL])
                    ->order_by('articleStatus','DESC')
                    ->from('article')
                    ->get()
                    ->result_array();
        foreach($data as $key => &$value){
            /*             $curNumber = $value['accession_number'];
                        $redis->Lpush('sciExportLink',$curNumber);
                        $redis->set('sciExport:'.$curNumber.':title',$value['title']);
                        $redis->set('sciExport:'.$curNumber.':author',$value['author']);
                        $redis->set('sciExport:'.$curNumber.':source',$value['source']);
                        $redis->set('sciExport:'.$curNumber.':article_type',$value['article_type']);
                        $redis->set('sciExport:'.$curNumber.':address',$value['address']);
                        $redis->set('sciExport:'.$curNumber.':email',$value['email']);
                        $redis->set('sciExport:'.$curNumber.':organization',$value['organization']);
                        $redis->set('sciExport:'.$curNumber.':quite_time',$value['quite_time']);
                        $redis->set('sciExport:'.$curNumber.':source_shorthand',$value['source_shorthand']);
                        $redis->set('sciExport:'.$curNumber.':is_top',$value['is_top']);
                        $redis->set('sciExport:'.$curNumber.':roll',$value['roll']);
                        $redis->set('sciExport:'.$curNumber.':period',$value['period']);
                        $redis->set('sciExport:'.$curNumber.':date',$value['date']);
                        $redis->set('sciExport:'.$curNumber.':year',$value['year']);
                        // 这里的页码需要处理
                        $page = explode($value['page'],'--');
                        $redis->set('sciExport:'.$curNumber.':startPage',$page[0]);
                        $redis->set('sciExport:'.$curNumber.':endPage',$page[1]);
                        $redis->set('sciExport:'.$curNumber.':is_first_inst',$value['is_first_inst']);
                        $redis->set('sciExport:'.$curNumber.':impact_factor',$value['impact_factor']);
                        $redis->set('sciExport:'.$curNumber.':subject',$value['subject']);
                        $redis->set('sciExport:'.$curNumber.':zk_type',$value['zk_type']);
                        $redis->set('sciExport:'.$curNumber.':is_cover',$value['is_cover']==1?'是':'否');
                        $redis->set('sciExport:'.$curNumber.':sci_type',$value['sci_type']);
                        $redis->set('sciExport:'.$curNumber.':reward_point',$value['reward_point']);
                        $redis->set('sciExport:'.$curNumber.':other_info',$value['other_info']);
                        $redis->set('sciExport:'.$curNumber.':owner',$value['owner']);
                        $redis->set('sciExport:'.$curNumber.':owner_name',$value['owner_name']);
                        $redis->set('sciExport:'.$curNumber.':claimer_unit',$value['claimer_unit']);
                        p($value);
                        exit();
                        */

            $page = explode('--',$value['page']);
            $value['startPage'] = $page[0];
            $value['endPage'] = $page[1];

            $author = $this->db->select('aName,aJobNumber,aisAddress,aUnit,aIsClaim')
                ->where(['aArticleNumber'=>$value['accession_number']])
                ->from('author')
                ->get()->result_array();
            $first_author = [];
            $first_author_number = [];
            $reprint_author = [];
            $other_author = [];
            foreach($author as $k => $v){
                if($k == 0){
                    array_push($first_author,$v['aName']);
                    array_push($first_author_number,$v['aJobNumber']);
                }else if($v['aisAddress']=='是'){
                    array_push($reprint_author,$v['aName']);
                }else{
                    array_push($other_author,$v['aName']);
                }
            }
            $value['first_author'] = implode(',',$first_author);
            $value['first_author_number'] = implode(',',$first_author_number);
            $value['reprint_author'] = implode(',',$reprint_author);
            $value['other_author'] = implode(',',$other_author);
        }

        $resultData["未认领"] = $data;
//        p($resultData);
//        exit();
        return ['data'=>$resultData,'unit'=>$unit];
    }


    public function formatAuthor($author){
        //把作者中的姓名全拼分为数组   这里涉及到两种格式，判断姓名的分类中是否有 ','分割，
        if(strpos($author,',') == false){
            $author=str_replace('-', '', $author);
            $authorArray=explode('; ', $author);
            foreach($authorArray as &$author){
                $author=str_replace(', ', ',', $author);
                $author=str_replace(' ', ',', $author);
            }
        }else{
            $author=str_replace(' ', '', $author);
            $author=str_replace('-', '', $author);
            $authorArray=explode(';', $author);
        }
        $first_author = $authorArray[0];
    }
    // ======================= 他引文章的数据库操作====================================

    /**
     * 数据库导入文件整个表
     *
     * @param [type] $data_arr
     * @return void
     */
    public function insertCitation($data_arr){
        return $this->db->insert_batch('citation',$data_arr);
    }

    /**
     * 判断某一篇文章是否存在
     *
     * @param [type] $where
     * @return void
     */
    public function citationExist($where)
    {
        $this->db->where($where);
        $this->db->from('citation');
        return $this->db->count_all_results();
    }

}