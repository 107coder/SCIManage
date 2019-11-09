<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ExcelAction extends CI_Controller {

    public function uploadFileApi()
    {
        $config['upload_path']      = './file/';
        $config['allowed_types']    = 'xls|xlsx|txt';
        $config['max_size']     = 2048;

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('file'))
        {
            $error = array('error' => $this->upload->display_errors());
            exit(JsonEcho('1','上传失败！',$error));
        }
        else
        {
            $data = $this->upload->data();
            $this->readArticleExcel($data['full_path']);
        }

        /*
         * 返回数据格式
         *
         *  client_name: "一级学科分类.xlsx"
            file_ext: ".xlsx"
            file_name: "一级学科分类.xlsx"
            file_path: "E:/www/107/SCIManage/file/"
            file_size: 9.23
            file_type: "application/octet-stream"
            full_path: "E:/www/107/SCIManage/file/一级学科分类.xlsx"
            image_height: null
            image_size_str: ""
            image_type: ""
            image_width: null
            is_image: false
            orig_name: "一级学科分类.xlsx"
            raw_name: "一级学科分类"
         */
    }
    public function getSession()
    {
//        session_destroy();
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
//        $this->load->model('file_model','file');
//        $where = array('accession_number'=>'WOS:0004542483000113');
//        var_dump($this->file->articleExist($where));

    }
    public function readArticleExcel($file = "")
    {
        $this->load->model('file_model','file');    //载入数据库文件插入的model
        $this->load->library("PHPExcel");
        header('Content-Type:text/html;charset=utf-8');

        $PHPReader = new PHPExcel_Reader_Excel5();
        if(!file_exists($file) || !$PHPReader->canRead($file)){
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if(!file_exists($file) || !$PHPReader->canRead($file))
            {
                exit(JsonEcho('1','file no exist!'));
            }
        }
        $objPHPExcel = $PHPReader->load($file);

        $sheet = $objPHPExcel->setActiveSheetIndex(0); // 设置只读取第一个sheet
        $allColumn = $sheet->getHighestColumn();
        $allRow = $sheet->getHighestRow();

        $redis = new redis();
        $redis->connect('127.0.0.1','6379') or exit(JsonEcho('1','服务器出现错误，请联系技术人员！'));
        $redis->select(2);
        $redis->flushDB();

        // 循环 遍历，读取表格数据
        for($row = 2; $row<=$allRow; $row++)
        {
            for($col='A'; $col<= $allColumn; $col++)
            {
                $address = $col.$row;
                $data[$col] = $sheet->getCell($address)->getValue();
            }
            $wosNumber = $data['A'];
            if($wosNumber == '') continue;  // 如果判断检测到某行的wos号码为空，直接跳过
            if($this->file->articleExist(array('accession_number'=>$wosNumber)) == 0)
            {
                $redis->lPush('articleLink',$wosNumber);
            }

            // 在这里可以对某些数据进行处理然后在进行存储，入库
            for($col='A'; $col<=$allColumn; $col++)
            {
                $redis->set('article:'.$wosNumber.':'.$col,$data[$col]);
                $redis->pExpire('article:'.$wosNumber.':'.$col,86400000);
            }

        }
        $articleLen = $redis->lLen('articleLink');

        $data_all = [];
        for($i=0; $i<$articleLen; $i++)
        {
            $wosCur = $redis->rPop('articleLink');

            $accession_number = $redis->get('article:'.$wosCur.':A');
            $title = $redis->get('article:'.$wosCur.':B');
            $data_one = array(
                'accession_number' => $redis->get('article:'.$wosCur.':A'),
                'title'            => $redis->get('article:'.$wosCur.':B'),
                'gender'           => $redis->get('article:'.$wosCur.':C'),
                'source'           => $redis->get('article:'.$wosCur.':D'),
                'article_type'     => $redis->get('article:'.$wosCur.':E'),
                'organization'     => $redis->get('article:'.$wosCur.':F'),
                'address'          => $redis->get('article:'.$wosCur.':G'),
                'email'            => $redis->get('article:'.$wosCur.':H'),
                'quite_time'       => $redis->get('article:'.$wosCur.':I'),
                'source_shorthand' => $redis->get('article:'.$wosCur.':J'),
                'date'             => $redis->get('article:'.$wosCur.':K'),
                'year'             => $redis->get('article:'.$wosCur.':L'),
                'roll'             => $redis->get('article:'.$wosCur.':M'),
                'period'           => $redis->get('article:'.$wosCur.':N'),
                'page'             => $redis->get('article:'.$wosCur.':O').'-'.$redis->get('article:'.$wosCur.':P'),
                'is_first_inst'    => $redis->get('article:'.$wosCur.':Q'),
                'impact_factor'    => $redis->get('article:'.$wosCur.':R'),
                'subject'          => $redis->get('article:'.$wosCur.':S'),
                'zk_type'          => $redis->get('article:'.$wosCur.':T'),
                'is_top'           => $redis->get('article:'.$wosCur.':U'),
                'is_cover'         => $redis->get('article:'.$wosCur.':V'),
                'sci_type'         => $redis->get('article:'.$wosCur.':W'),
                'reward_point'     => $redis->get('article:'.$wosCur.':X'),
                'other_info'       => $redis->get('article:'.$wosCur.':Y'),
                'add_method'       => 0
            );

            array_push($data_all,$data_one);
            // 每300条数据，插入数据库一次
            if(count($data_all) >= 300 || $i==$articleLen-1)
            {
                $status  = 0;
                $status = $this->file->articleInsert($data_all);
                $data_all = [];
                if($status <= 0)
                {
                    exit(JsonEcho('1','数据插入错误！'));
                }
            }
        }
        exit(JsonEcho('0','数据导入成功！'));
    }

    //学科分类的导入
    public function uploadTypeFileApi()
    {
        $config['upload_path']      = './file/';
        $config['allowed_types']    = 'xls|xlsx|txt';
        $config['max_size']     = 2048;

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('file'))
        {
            $error = array('error' => $this->upload->display_errors());
            exit(JsonEcho('1','上传失败！',$error));
        }
        else
        {
            $data = $this->upload->data();
            $this->readTypeExcel($data['full_path']);
        }
    }
    //执行分类的入库操作
    public function readTypeExcel($file='')
    {
        $this->load->model('file_model','file');    //载入数据库文件插入的model
        $this->load->library("PHPExcel");
        header('Content-Type:text/html;charset=utf-8');

        $PHPReader = new PHPExcel_Reader_Excel5();
        if(!file_exists($file) || !$PHPReader->canRead($file)){
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if(!file_exists($file) || !$PHPReader->canRead($file))
            {
                exit(JsonEcho('1','file no exist!'));
            }
        }
        $objPHPExcel = $PHPReader->load($file);

        $sheet = $objPHPExcel->setActiveSheetIndex(0); // 设置只读取第一个sheet
        $allColumn = $sheet->getHighestColumn();
        $allRow = $sheet->getHighestRow();

        $data_all = [];
        for($row = 2; $row<=$allRow; $row++)
        {
            for($col='A'; $col<= $allColumn; $col++)
            {
                $address = $col.$row;
                $data['subject_name'] = $sheet->getCell($address)->getValue();
            }

            array_push($data_all,$data);
        }

        $status = $this->file->typeInsert($data_all);
        if($status <= 0)
        {
            exit(JsonEcho('1','数据插入错误！'));
        }else{
            exit(JsonEcho('0','数据导入成功！'));
        }

    }

    //用户的导入
    public function uploadUserFileApi()
    {
        $config['upload_path']      = './file/';
        $config['allowed_types']    = 'xls|xlsx|txt';
        $config['max_size']     = 2048;

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('file'))
        {
            $error = array('error' => $this->upload->display_errors());
            exit(JsonEcho('1','上传失败！',$error));
        }
        else
        {
            $data = $this->upload->data();
            $this->readUserExcel($data['full_path']);

        }
    }
    //执行用户的入库操作
    public function readUserExcel($file='')
    {   
        $this->load->model('user_model','user');    //载入数据库文件插入的model
        $this->load->library("PHPExcel");
        header('Content-Type:text/html;charset=utf-8');

        $PHPReader = new PHPExcel_Reader_Excel5();
        if(!file_exists($file) || !$PHPReader->canRead($file)){
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if(!file_exists($file) || !$PHPReader->canRead($file))
            {
                exit(JsonEcho('1','file no exist!'));
            }
        }
        $objPHPExcel = $PHPReader->load($file);

        $sheet = $objPHPExcel->setActiveSheetIndex(0); // 设置只读取第一个sheet
        $allColumn = $sheet->getHighestColumn();    //总列数
        $allRow = $sheet->getHighestRow();      //总行数

        $redis = new redis();
        $redis->connect('127.0.0.1','6379') or exit(JsonEcho('1','服务器出现错误，请联系技术人员！'));
        $redis->select(2);
        $redis->flushDB();

        // 循环 遍历，读取表格数据
        for($row = 2; $row<=$allRow; $row++)
        {
            for($col='A'; $col<= $allColumn; $col++)
            {
                $address = $col.$row;
                $data[$col] = $sheet->getCell($address)->getValue();
            }
            $wosNumber = $data['A'];
            if($wosNumber == '') continue;  // 如果判断检测到某行的wos号码为空，直接跳过
            if($this->user->userExist(array('job_number'=>$wosNumber)) == 0)
            {
                $redis->lPush('userLink',$wosNumber);
            }

            // 在这里可以对某些数据进行处理然后在进行存储，入库
            for($col='A'; $col<=$allColumn; $col++)
            {
                $redis->set('user:'.$wosNumber.':'.$col,$data[$col]);
                $redis->pExpire('user:'.$wosNumber.':'.$col,86400000);
            }

        }
        $userLen = $redis->lLen('userLink');

        $data_all = [];
        for($i=0; $i<$userLen; $i++)
        {
            $wosCur = $redis->rPop('userLink');
            $data_one = array(
                'job_number' => $redis->get('user:'.$wosCur.':A'),
                'name'            => $redis->get('user:'.$wosCur.':B'),
                //取gender的第一个汉字
                'gender'           => $redis->getRange('user:'.$wosCur.':C', 0, 3),
                'academy'           => $redis->get('user:'.$wosCur.':D'),
                'birthday'     => $redis->get('user:'.$wosCur.':E'),
                'edu_background'     => $redis->get('user:'.$wosCur.':F'),
                'degree'          => $redis->get('user:'.$wosCur.':G'),
                'job_title'            => $redis->get('user:'.$wosCur.':H'),
                'job_title_rank'       => $redis->get('user:'.$wosCur.':I'),
                'job_title_series' => $redis->get('user:'.$wosCur.':J'),    
                'full_spell'         => "暂未填写",
                'identity'         => 0,
            );
            array_push($data_all,$data_one);
            // 每300条数据，插入数据库一次
            if(count($data_all) >= 300 || $i==$userLen-1)
            {
                $status  = 0;
                $status = $this->user->userInsert($data_all);
                $data_all = [];
                if($status <= 0)
                {
                    exit(JsonEcho('1','数据插入错误！'));
                }
            }
        }
        exit(JsonEcho('0','数据导入成功！'));
    }


}
