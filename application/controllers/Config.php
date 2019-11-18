<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/14
 * Time: 20:17
 */

class Config extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function userInfo(){
        // 获取数据   table中的where是通过get方式获取的
        $accession_number = $this->input->get('accession_number');
        $this->load->model('article_model','article');

        $cols = 'author';
        $where = ['accession_number'=>$accession_number];
        $data = $this->article->getAnyArticle($where,$cols);
        $author = explode('; ',$data[0]['author']);
        $author_arr = ["code"=>0,"msg"=>'',"count"=>count($author),"data"=>[]];
        foreach($author as $key => $val)
        {
            $data = [
                "full_spell"=> $val,
                "name"=> "",
                "sex"=> "",
                "number"=> "",
                "xueli"=> "",
                "title"=> "",
                "tongxun"=> "",
                "unit"=> ""
            ];
            array_push($author_arr['data'],$data);
        }
      
        echo json_encode($author_arr,JSON_UNESCAPED_UNICODE);
    }

    public function getSession()
    {
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }
  
}