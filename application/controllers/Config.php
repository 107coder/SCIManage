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
        $accession_number = $this->input->post('accession_number');
        $accession_number = 'WOS:000452969500009';
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
                "Number"=> "",
                "Xueli"=> "",
                "Title"=> "",
                "Tongxun"=> "",
                "Unit"=> ""
            ];
            array_push($author_arr['data'],$data);
        }
        // P($author_arr); 
      

        echo json_encode($author_arr,JSON_UNESCAPED_UNICODE);
    }

    function test()
    {
        $userData = ["code"=>0,"msg"=>'',"count"=>6,"data"=>
        [[
            "id"=> 1,
            "name"=> "",
            "sex"=> "",
            "Number"=> "",
            "Xueli"=> "",
            "Title"=> "",
            "Tongxun"=> "",
            "Unit"=> ""
        ]
        ,[
            "id"=> 2,
            "name"=> "",
            "sex"=> "",
            "Number"=> "",
            "Xueli"=> "",
            "Title"=> "",
            "Tongxun"=> "",
            "Unit"=> ""
        ],[
            "id"=> 3,
            "name"=> "",
            "sex"=> "",
            "Number"=> "",
            "Xueli"=> "",
            "Title"=> "",
            "Tongxun"=> "",
            "Unit"=> ""
        ],[
            "id"=> 4,
            "name"=> "",
            "sex"=> "",
            "Number"=> "",
            "Xueli"=> "",
            "Title"=> "",
            "Tongxun"=> "",
            "Unit"=> ""
        ],[
            "id"=> 5,
            "name"=> "",
            "sex"=> "",
            "Number"=> "",
            "Xueli"=> "",
            "Title"=> "",
            "Tongxun"=> "",
            "Unit"=> ""
        ],[
            "id"=> 6,
            "name"=> "",
            "sex"=> "",
            "Number"=> "",
            "Xueli"=> "",
            "Title"=> "",
            "Tongxun"=> "",
            "Unit"=> ""
        ]]];
        echo json_encode($userData,JSON_UNESCAPED_UNICODE);
//        json 数据格式
        $json = '
{
  "code": 0,
  "msg": "",
  "count": 6,
  "data": [
    {
      "id": 10000,
      "name": "user-0",
      "sex": "女",
      "Number": "",
      "Xueli": "",
      "Title": "",
      "Tongxun": "昆明",
      "Unit": "签名-0"
    }
  ]
}';

    }
}