<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ExcelAction extends MY_Controller {

    /**
     * sci论文表格文件的导入
     *
     * @return void
     */
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

    /**
     * 添加sci论文的操作
     *
     * @param string $file
     * @return void
     */
    public function readArticleExcel($file = "")
    {
        $file = './file/上传文件模板.xlsx';
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
                if($col == 'K'){
                    $time = PHPExcel_Shared_Date::ExcelToPHP(intval($sheet->getCell($address)->getValue()));
                    $data[$col] = date("Y-m-d",$time);
                }else{
                    $data[$col] = $sheet->getCell($address)->getValue();
                }
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
            p($data);

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
                'author'           => $redis->get('article:'.$wosCur.':C'),
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
                'page'             => $redis->get('article:'.$wosCur.':O').'--'.$redis->get('article:'.$wosCur.':P'),
                'is_first_inst'    => $redis->get('article:'.$wosCur.':Q'),
                'impact_factor'    => $redis->get('article:'.$wosCur.':R'),
                'subject'          => $redis->get('article:'.$wosCur.':S'),
                'zk_type'          => $redis->get('article:'.$wosCur.':T'),
                'is_top'           => $redis->get('article:'.$wosCur.':U'),
                'is_cover'         => $redis->get('article:'.$wosCur.':V')==NULL?'不是':'是',
                'sci_type'         => $redis->get('article:'.$wosCur.':W'),
                'reward_point'     => $redis->get('article:'.$wosCur.':X'),
                'other_info'       => $redis->get('article:'.$wosCur.':Y'),
                'add_method'       => 0,
                'claim_author'     => $this->searchFullSpell($redis->get('article:'.$wosCur.':G'),$redis->get('article:'.$wosCur.':C'))
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

   
    /**
     * /学科分类的导入
     *
     * @return void
     */
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
    //
    /**
     *执行分类的入库操作
     *
     * @param string $file
     * @return void
     */
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
    /**
     * 教师名单表格文件的上传
     *
     * @return void
     */
    public function uploadTeacherFileApi()
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
            $this->readTeacherExcel($data['full_path']);

        }
    }
    /**
     * 学生表格文件的上传
     *
     * @return void
     */
    public function uploadStudentFileApi()
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
            $this->readStudentExcel($data['full_path']);

        }
    }
    //执行用户的入库操作
    /**
     * 教师数据的处理，并进行入库的操作
     *
     * @param string $file
     * @return void
     */
    public function readTeacherExcel($file='')
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
            if($this->user->TeacherExist(array('job_number'=>$wosNumber)) == 0)
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
                'full_spell'         => "",
                'identity'         => 0,
                'password'         =>md5('a'.$redis->get('user:'.$wosCur.':A'))
            );
            array_push($data_all,$data_one);
            // 每300条数据，插入数据库一次
            if(count($data_all) >= 300 || $i==$userLen-1)
            {
                $status  = 0;
                $status = $this->user->teacherInsert($data_all);
                $data_all = [];
                if($status <= 0)
                {
                    exit(JsonEcho('1','数据插入错误！'));
                }
            }
        }
        exit(JsonEcho('0','数据导入成功！'));
    }
    /**
     * 学生数据的处理及写入数据库
     *
     * @param string $file
     * @return void
     */
    public function readStudentExcel($file='')
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
            if($this->user->studentExist(array('sno'=>$wosNumber)) == 0)
            {
                $redis->lPush('studentLink',$wosNumber);
            }

            // 在这里可以对某些数据进行处理然后在进行存储，入库
            for($col='A'; $col<=$allColumn; $col++)
            {
                $redis->set('student:'.$wosNumber.':'.$col,$data[$col]);
                $redis->pExpire('student:'.$wosNumber.':'.$col,86400000);
            }

        }
        $studentLen = $redis->lLen('studentLink');

        $data_all = [];
        for($i=0; $i<$studentLen; $i++)
        {
            $wosCur = $redis->rPop('studentLink');
            $data_one = array(
                'sno' => $redis->get('student:'.$wosCur.':A'),
                'name'            => $redis->get('student:'.$wosCur.':B'),
                //取gender的第一个汉字
                'gender'           => $redis->getRange('student:'.$wosCur.':C', 0, 3),
                'academy'           => $redis->get('student:'.$wosCur.':D'),
                'profession'           => $redis->get('student:'.$wosCur.':E'),
            );
            array_push($data_all,$data_one);
            // 每300条数据，插入数据库一次
            if(count($data_all) >= 300 || $i==$studentLen-1)
            {
                $status  = 0;
                $status = $this->user->studentInsert($data_all);
                $data_all = [];
                if($status <= 0)
                {
                    exit(JsonEcho('1','数据插入错误！'));
                }
            }
        }
        exit(JsonEcho('0','数据导入成功！'));
    }


    /**
     * 他引文章文件的上传
     *
     * @return void
     */
    public function uploadCitationApi()
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
            $this->readCitationExcel($data['full_path']);
        }
        
    }
    /**
     * 他引文章数据的处理与写入数据库操作
     *
     * @param string $file
     * @return void
     */
    public function readCitationExcel($file = "")
    {
        // $file = './file/论文他引.xlsx';
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
        for($row = 3; $row<=$allRow; $row++)
        {
            for($col='A'; $col<= $allColumn; $col++)
            {
                $address = $col.$row;
                if($col == 'L'){
                    $time = PHPExcel_Shared_Date::ExcelToPHP(intval($sheet->getCell($address)->getValue()));
                    $data[$col] = date("Y-m-d",$time);
                }else{
                    $data[$col] = $sheet->getCell($address)->getValue();
                }
            }
            
            $wosNumber = $data['A'];
            if($wosNumber == '') continue;  // 如果判断检测到某行的wos号码为空，直接跳过
           if($this->file->citationExist(['citation_number'=>$wosNumber])== 0)
           {
               $redis->lPush('articleLink',$wosNumber);
           }

            // 在这里可以对某些数据进行处理然后在进行存储，入库
            for($col1='A'; $col1<=$allColumn; $col1++)
            {
                $redis->set('article:'.$wosNumber.':'.$col1,$data[$col1]);
                $redis->pExpire('article:'.$wosNumber.':'.$col1,86400000);
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
                'citation_number'  => $redis->get('article:'.$wosCur.':A'),
                'author'           => $redis->get('article:'.$wosCur.':B'),
                'title'            => $redis->get('article:'.$wosCur.':C'),
                'source'           => $redis->get('article:'.$wosCur.':D'),
                'type'             => $redis->get('article:'.$wosCur.':E'),
                'organization'     => $redis->get('article:'.$wosCur.':F'),
                'reprint_author'   => $redis->get('article:'.$wosCur.':G'),
                'email'            => $redis->get('article:'.$wosCur.':H'),
                'quote_time'       => $redis->get('article:'.$wosCur.':I'),
                'publication_number' => $redis->get('article:'.$wosCur.':J'),
                'source_shorthand' => $redis->get('article:'.$wosCur.':K'),
                'date'             => $redis->get('article:'.$wosCur.':L'),
                'year'             => $redis->get('article:'.$wosCur.':M'),
                'roll'             => $redis->get('article:'.$wosCur.':N'),
                'period'           => $redis->get('article:'.$wosCur.':O'),
                'page'             => $redis->get('article:'.$wosCur.':P').'--'.$redis->get('article:'.$wosCur.':Q'),
                'is_first_inst'    => $redis->get('article:'.$wosCur.':R'),
                'impact_factor'    => $redis->get('article:'.$wosCur.':S'),
                'subject'          => $redis->get('article:'.$wosCur.':T'),
                'zk_type'          => $redis->get('article:'.$wosCur.':U'),
                'is_top'           => $redis->get('article:'.$wosCur.':V'),
                'citation_time'         => '2018:'.$redis->get('article:'.$wosCur.':W').'-2019:'.$redis->get('article:'.$wosCur.':X').'-',
                'other_info'       => $redis->get('article:'.$wosCur.':Y'),
                'add_method'       => 0,
                'claim_author'     => $this->searchFullSpell($redis->get('article:'.$wosCur.':G'),$redis->get('article:'.$wosCur.':B'))
            );
            
            array_push($data_all,$data_one);
            // 每300条数据，插入数据库一次
           if(count($data_all) >= 300 || $i==$articleLen-1)
           {
               $status  = 0;
               $status = $this->file->insertCitation($data_all);

               $data_all = [];
               if($status <= 0)
               {
                   exit(JsonEcho('1','数据插入错误！'));
               }
           }
        }
        
        exit(JsonEcho('0','数据导入成功！'));
    }


    /**
     * 根据通讯作者中的简写，在所有作者中查找出来所有通讯作者的全拼
     *
     * @param [type] $address
     * @param [type] $author
     * @return void
     */    
    function searchFullSpell($address,$author){
        
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
       

        //把地址中的姓名简写分为数组  先根据';'分成数组，然后判断是否含有(reprintauthor)如果有截取前面的字符
        $address=str_replace(' ', '', $address);
        $addressArray=explode(';', $address);
        $addressArrayLen=count($addressArray);
        for ($i=0; $i < $addressArrayLen; $i++) 
        { 
            if(strstr($addressArray[$i], "(reprintauthor)")!=false)
            {
                $pos=strpos($addressArray[$i],"(reprintauthor)");
                $addressArray[$i]=substr($addressArray[$i], 0,$pos);
            }
                
        }
        // $fullSpellArray  = [];
        //对每个简写查找它的全拼以数组形式返回
        foreach ($addressArray as $value)
        {
            $short=$value;
            $short=strtolower($short);
            $shortLowerArray=explode(',', $short);
            foreach ($authorArray as $author)
            {
                $fullSpell=strtolower($author);
                $authorLowerArray=explode(',', $fullSpell);
                $bool=true;
                //判断二者逗号前是否相同
                if(strstr($authorLowerArray[0], $shortLowerArray[0])==false)
                    $bool=false;
                //判断缩写逗号后的字母是否都在全拼逗号后的字符里
                $length=strlen($shortLowerArray[1]);
                if(strpos($fullSpell,',') != false)
                {
                    $length=strlen($shortLowerArray[1]);
                    for ($i=0; $i <$length ; $i++)
                    { 
                        if(strstr($authorLowerArray[1], $shortLowerArray[1][$i])==false)
                            $bool=false;
                    }
                }
                if($bool)
                {
                    $fullSpellArray[]=$author;
                }
            }
        }
        if(isset($fullSpellArray))
            $claim_author = $fullSpellArray;
         else
            $claim_author = array();

        
        if(!in_array($first_author,$claim_author)){
            array_unshift($claim_author,$first_author);
        }
        $claim_author = implode(';',$claim_author);
        return $claim_author;
    }



    // ========================== 数据导出 =============================

    /**
     * sci数据导出
     *
     * @param string $fileName
     * @return json
     */
    public function sciExport(){
        $this->load->model('file_model','file');    //载入数据库文件插入的model


        $basePath = $_SERVER['DOCUMENT_ROOT'];
        $basePath = substr($basePath,0,strlen($basePath)-5).'file/download/';
        $dir = 'SCI论文认领结果_'.date('Ymdim',time());
        $filePath = $basePath.$dir;
//        $path = $this->tranEncoding($path);
//        $dir = $this->tranEncoding($dir);
        if(isset($dir)){
            mkdir($filePath,'0777');
            chmod($filePath,0777);
        }
        if(!is_dir($filePath)){
            exit(JsonEcho(2,'找不到目录，无法导出'));
        }
        // 获取到所有的sci论文数据
        $data = $this->file->getAllSciArticleToExport();
        $articleData = $data['data'];
        foreach ($articleData as $key => $val){
//            $key = $this->tranEncoding($key);
           $this->sciExportAction($val,$key,$dir);
        }
        // 使用自定义的文件压缩类
        $this->load->library('ZipFolder');
        $ZipFolder = new ZipFolder();
        $zipFile = $filePath.'.zip';//生成压缩文件的路径
        $path = $filePath;//被压缩文件夹的路径
        $result = $ZipFolder->zip($zipFile,$path);


        if($result){
            $fileName = $dir.'.zip';
            exit(JsonEcho(0,'数据导出正常',['filename'=>$fileName]));
        }else{
            exit(JsonEcho(1,'数据导出异常'));
        }
    }


    public function sciExportAction($data,$fileName,$dir=null){
        $this->load->library("PHPExcel");
        // 准备表格中的要用的一些数据
        $title = array('入藏号','论文名称','中文作者全拼','来源期刊','文章类型','单位','通讯作者','电子邮箱','引用次数',
            '来源期刊简写','月日','年','卷','期','开始页码','结束页码','是否第一机构','影响因子','所属大类','中科院大类分区',
            '是否TOP期刊','是否封面论文','奖励文件论文分类','奖励分值','备注','论文状态','第一作者','第一作者工号','通讯作者','其他作者',
            '认领人','认领人所属单位');
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF');
        $articleStatus = ['未认领','学院审核中','学院不通过','学校未审核','学校不通过','审核通过'];// 论文的状态

        $obj = new PHPExcel();
        $obj->getActiveSheet(0)->setTitle('sheet');   //设置sheet名称
        $_row = 1;   //设置纵向单元格标识

        // 为表格设置标题
        if($title){
            $i = 0;
            foreach($title AS $v){   //设置列标题
                $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);
                $i++;
            }
            $_row++;
        }

        //设置表格宽度
        $obj->getActiveSheet()->getColumnDimension('A')->setWidth(21);
        $obj->getActiveSheet()->getColumnDimension('B')->setWidth(21);
        $obj->getActiveSheet()->getColumnDimension('C')->setWidth(21);
        $obj->getActiveSheet()->getColumnDimension('D')->setWidth(21);
        $obj->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $obj->getActiveSheet()->getColumnDimension('F')->setWidth(21);
        $obj->getActiveSheet()->getColumnDimension('G')->setWidth(21);
        $obj->getActiveSheet()->getColumnDimension('H')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('I')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('J')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('K')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('L')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('M')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('N')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('O')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('P')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('Q')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('R')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('S')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('T')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('U')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('V')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('W')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('X')->setWidth(9);
        $obj->getActiveSheet()->getColumnDimension('Y')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('AA')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('AC')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('AD')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('AE')->setWidth(15);
        $obj->getActiveSheet()->getColumnDimension('AF')->setWidth(15);

        // 如果数据不为空，则向所有的单元格中写入数据
        if($data){
            $i = 0;
            foreach($data AS $v){
                $obj->getActiveSheet(0)->setCellValue($cellName[0].($i+$_row),$v['accession_number']);
                $obj->getActiveSheet(0)->setCellValue($cellName[1].($i+$_row),$v['title']);
                $obj->getActiveSheet(0)->setCellValue($cellName[2].($i+$_row),$v['author']);
                $obj->getActiveSheet(0)->setCellValue($cellName[3].($i+$_row),$v['source']);
                $obj->getActiveSheet(0)->setCellValue($cellName[4].($i+$_row),$v['article_type']);
                $obj->getActiveSheet(0)->setCellValue($cellName[5].($i+$_row),$v['organization']);
                $obj->getActiveSheet(0)->setCellValue($cellName[6].($i+$_row),$v['address']);
                $obj->getActiveSheet(0)->setCellValue($cellName[7].($i+$_row),$v['email']);
                $obj->getActiveSheet(0)->setCellValue($cellName[8].($i+$_row),$v['quite_time']);
                $obj->getActiveSheet(0)->setCellValue($cellName[9].($i+$_row),$v['source_shorthand']);
                $obj->getActiveSheet(0)->setCellValue($cellName[10].($i+$_row),$v['date']);
                $obj->getActiveSheet(0)->setCellValue($cellName[11].($i+$_row),$v['year']);
                $obj->getActiveSheet(0)->setCellValue($cellName[12].($i+$_row),$v['roll']);
                $obj->getActiveSheet(0)->setCellValue($cellName[13].($i+$_row),$v['period']);
                $obj->getActiveSheet(0)->setCellValue($cellName[14].($i+$_row),$v['startPage']);
                $obj->getActiveSheet(0)->setCellValue($cellName[15].($i+$_row),$v['endPage']);
                $obj->getActiveSheet(0)->setCellValue($cellName[16].($i+$_row),$v['is_first_inst']);
                $obj->getActiveSheet(0)->setCellValue($cellName[17].($i+$_row),$v['impact_factor']);
                $obj->getActiveSheet(0)->setCellValue($cellName[18].($i+$_row),$v['subject']);
                $obj->getActiveSheet(0)->setCellValue($cellName[19].($i+$_row),$v['zk_type']);
                $obj->getActiveSheet(0)->setCellValue($cellName[20].($i+$_row),$v['is_top']);
                $obj->getActiveSheet(0)->setCellValue($cellName[21].($i+$_row),$v['is_cover']);
                $obj->getActiveSheet(0)->setCellValue($cellName[22].($i+$_row),$v['sci_type']);
                $obj->getActiveSheet(0)->setCellValue($cellName[23].($i+$_row),$v['reward_point']);
                $obj->getActiveSheet(0)->setCellValue($cellName[24].($i+$_row),$v['other_info']);
                $obj->getActiveSheet(0)->setCellValue($cellName[25].($i+$_row),$articleStatus[$v['articleStatus']]);
                $obj->getActiveSheet(0)->setCellValue($cellName[26].($i+$_row),$v['first_author']);
                $obj->getActiveSheet(0)->setCellValue($cellName[27].($i+$_row),$v['first_author_number']);
                $obj->getActiveSheet(0)->setCellValue($cellName[28].($i+$_row),$v['reprint_author']);
                $obj->getActiveSheet(0)->setCellValue($cellName[29].($i+$_row),$v['other_author']);
                $obj->getActiveSheet(0)->setCellValue($cellName[30].($i+$_row),$v['owner_name']);
                $obj->getActiveSheet(0)->setCellValue($cellName[31].($i+$_row),$v['claimer_unit']);

                $i++;
            }
        }


        // 设置文件名称   保存文件
        if(!$fileName){
            $fileName = date('Ymdims',time()).'.xls';
        }else{
            $fileName = $fileName.'.xls';
        }
        if($dir==null){
            $filePath = '/var/www/SCIManage/file/download/'.$fileName;
        }else{
            $filePath = '/var/www/SCIManage/file/download/'.$dir.'/'.$fileName;
        }
        $objWrite = PHPExcel_IOFactory::createWriter($obj, 'Excel5');

        if(!empty($objWrite)){
            $objWrite->save($filePath);
//            exit(JsonEcho(0,'数据导出正常',['filename'=>$fileName]));
        }else{
//            exit(JsonEcho(1,'数据导出异常'));
        }
    }

    public function downloadFile(){
        $filename = $this->input->get('filename');
        $this->load->helper('download');
        // 根据项目路径指定下载文件的文件夹
        $basePath = $_SERVER['DOCUMENT_ROOT'];
        $basePath = substr($basePath,0,strlen($basePath)-5).'file/download/';
        $filePath = $basePath.$filename;

        force_download($filePath,NULL);
    }
    public function citationExport(){


    }

}
