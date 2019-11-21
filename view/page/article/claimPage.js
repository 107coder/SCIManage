var authorCount = 0;
layui.use(['form','layer','layedit','laydate','upload','element','table'],function(){
    var form = layui.form
        layer = parent.layer === undefined ? layui.layer : top.layer,
        laypage = layui.laypage,
        upload = layui.upload,
        layedit = layui.layedit,
        laydate = layui.laydate,
        table = layui.table, //表格
        element = layui.element,
        $ = layui.jquery;
        
    //用于同步编辑器内容到textarea
    layedit.sync(editIndex);

    //上传缩略图
    upload.render({
        elem: '.thumbBox',
        url: '../../json/userface.json',
        method : "get",  //此处是为了演示之用，实际使用中请将此删除，默认用post方式提交
        done: function(res, index, upload){
            var num = parseInt(4*Math.random());  //生成0-4的随机数，随机显示一个头像信息
            $('.thumbImg').attr('src',res.data[num].src);
            $('.thumbBox').css("background","#fff");
        }
    });

    //格式化时间
    function filterTime(val){
        if(val < 10){
            return "0" + val;
        }else{
            return val;
        }
    }
    //定时发布
    var time = new Date();
    var submitTime = time.getFullYear()+'-'+filterTime(time.getMonth()+1)+'-'+filterTime(time.getDate())+' '+filterTime(time.getHours())+':'+filterTime(time.getMinutes())+':'+filterTime(time.getSeconds());
    laydate.render({
        elem: '#release',
        type: 'datetime',
        trigger : "click",
        done : function(value, date, endDate){
            submitTime = value;
        }
    });
    form.on("radio(release)",function(data){
        if(data.elem.title == "定时发布"){
            $(".releaseDate").removeClass("layui-hide");
            $(".releaseDate #release").attr("lay-verify","required");
        }else{
            $(".releaseDate").addClass("layui-hide");
            $(".releaseDate #release").removeAttr("lay-verify");
            submitTime = time.getFullYear()+'-'+(time.getMonth()+1)+'-'+time.getDate()+' '+time.getHours()+':'+time.getMinutes()+':'+time.getSeconds();
        }
    });

    form.verify({
        newsName : function(val){
            if(val == ''){
                return "文章标题不能为空";
            }
        },
        content : function(val){
            if(val == ''){
                return "文章内容不能为空";
            }
        }
    })



    form.on("submit(articleAdd)",function(data){
        layer.msg('test');
        //截取文章内容中的一部分文字放入文章摘要
        // var abstract = layedit.getText(editIndex).substring(0,50);
        //弹出loading
        var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
        var dataJson = JSON.stringify(data.field);
        // layer.msg(dataJson);
        // 实际使用时的提交信息
        $.post(rootUrl+"/Article/insertArticleApi",{
            data:dataJson
          
        },function(res){
            if(res.code == 0)
            {
                setTimeout(function(){
                    top.layer.close(index);
                    top.layer.msg("文章添加成功！");
                    layer.closeAll("iframe");
                    //刷新父页面
                    parent.location.reload();
                },500);
            }else if(res.code == 1){
                console.log(res);
                top.layer.close(index);
                top.layer.msg(res.msg);
                return false;
            }else{
                top.layer.msg(res.msg);
                return false;
            }
        },'json')
        return false;
    })

    //预览
    form.on("submit(look)",function(){
        layer.alert("此功能需要前台展示，实际开发中传入对应的必要参数进行文章内容页面访问");
        return false;
    })

    //创建一个编辑器
    var editIndex = layedit.build('news_content',{
        height : 535,
        uploadImage : {
            url : "../../json/newsImg.json"
        }
    });


    // 加载下面添加人员信息的部分

        //监听地址选择操作
        form.on('select(selectDemo)', function (obj) {
            layer.tips(obj.elem.getAttribute('name') + '：'+obj.value + ' ' + obj.elem.getAttribute('dataId') , obj.othis);
        });
        //监听性别操作
        form.on('switch(sexDemo)', function (obj) {
            layer.tips(this.value + ' ' + this.name + '：' + obj.elem.checked, obj.othis);
        });
        var accession_number = $('#accession_number').text();
        //执行一个 table 实例
        var userTable = table.render({
            elem: '#tableDemo'
            , height: 350
            , url: rootUrl+'/Author/userInfo' //数据接口
            , type:'post'
            , where: {accession_number:accession_number}
            // , page: true //开启分页
            , cols: [[ //表头
                {field: 'full_spell', title: '姓名全拼', width: 120, fixed: 'left'}
                , {field: 'authorType', title: '作者类型', width: 160, templet: '#selectType', unresize: true}
                , {field: 'number', title: '作者职工号', edit: 'text',width: 150}
                , {field: 'name', title: '作者姓名', edit:'text', width: 150}
                , {field: 'sex', title: '性别', width: 85, templet: '#selectSex', unresize: true }
                , {field: 'xueli', title: '学历', edit:"text", width: 150}
                , {field: 'title', title: '职称', edit:"text", width: 100}
                , {field: 'tongxun', title: '是否为通讯作者', width: 150, templet:"#switchTpl"}
                , {field: 'unit', title: '工作单位', edit:"text"}
            ]]
            ,done:function(res){
                authorCount = res.count;
                $("[data-index=0]").find("[data-field='name']").attr('data-edit','t');
                // $("[data-index="+0+"]").find("[data-field='name']").attr('data-edit','text');
            }
        });

        // 根据工号和行索引向服务器请求数据  
        function getOneAuthor(index,job_number,authorType){
            $.ajax({
                url:rootUrl+"/User/getOneUser"
                ,type:'post'
                ,data:{
                    job_number:job_number,
                    authorType:authorType
                }
                ,dataType:'json'
                ,success:function(res){
                    console.log(res.data);
                    if(res.code==0)
                    {
                        updateAuthor(index,res.data);
                    }else
                    {
                        data = new Array();
                        data['name'] = '';
                        data['edu_background'] = '';
                        data['job_title'] = '';
                        data['academy'] = '';
                        data['gender'] = '男';
                        updateAuthor(index,data);
                    }
                },error:function()
                {
                    layer.msg('服务器错误，请联系系统管理员');
                }
            });
        }

         // 当填入工号 学号时自动填充信息的方法
         function updateAuthor(index,data)
         {
             $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
             // $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
             $("[data-index="+index+"]").find("[data-field='xueli']").find('div').text(data['edu_background']);
             $("[data-index="+index+"]").find("[data-field='title']").find('div').text(data['job_title']);
             $("[data-index="+index+"]").find("[data-field='unit']").find('div').text(data['academy']);
             // $("[data-index="+index+"]").find("[data-field='sex']").find('find').val(data['gender']);
             $("[data-index="+index+"]").find("dd[lay-value="+data['gender']+"]").click();
             // $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
             // $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
         }   



        //监听单元格编辑
        table.on('edit(tableDemo)', function(obj){
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,tr = obj.tr  // 获取tr的dom对象
                ,field = obj.field; //得到字段
                // 控制如果不是修改的工号，就不会给整行填写数据
                
                if(field != 'number'){
                    return false;
                }
                // console.log(data);
                // 获取对应的单元格的 索引 index
                var index = data.LAY_TABLE_INDEX;
                // 获取到作者的类型
                var authorType = $("[data-index="+index+"]").find("[data-field='authorType']").find('input').val();
                // console.log(authorType);
                if(authorType=='本校教师' || authorType=='本校研究生'){
                    getOneAuthor(index,data.number,authorType);
                }else{
                    data = new Array();
                    data['name'] = '';
                    data['edu_background'] = '';
                    data['job_title'] = '';
                    data['academy'] = '';
                    data['gender'] = '男';
                    updateAuthor(index,data);
                }
                
        });

       

        // 选择教师类型，相应对应的事件
        form.on("select(selectType)",function(data){
            // console.log(data.elem); //得到select原始DOM对象
            console.log(data.value); //得到被选中的值
            
            // 获取对应的教师类型的值
            var authorType = data.value;
            // 获取对应的行的索引index
            var index = $(data.elem).parent().parent().parent().attr('data-index');
            // 获取此时的工号
            var job_number = $("[data-index="+index+"]").find("[data-field='number']").find('div').text();
            // 请求数据，填充数据
            if(authorType=='本校教师' || authorType=='本校研究生'){
                getOneAuthor(index,job_number,authorType);
            }else{
                data = new Array();
                data['name'] = '';
                data['edu_background'] = '';
                data['job_title'] = '';
                data['academy'] = '';
                data['gender'] = '男';
                updateAuthor(index,data);

                $("[data-index="+index+"]").find("[data-field='name']").attr('data-edit','text');
            }
            
          });

})

/**
 * 使用jquery获取页面中对应序号的作者的信息
 * @param {author 的序号} index 
 */
function getAuthor(index)
{
    var full_spell = $("[data-index="+index+"]").find("[data-field='full_spell']").find('div').html();
    var name = $("[data-index="+index+"]").find("[data-field='name']").find('div').text();
    var xueli =  $("[data-index="+index+"]").find("[data-field='xueli']").find('div').text();
    var title = $("[data-index="+index+"]").find("[data-field='title']").find('div').text();
    var unit = $("[data-index="+index+"]").find("[data-field='unit']").find('div').text();
    var authorType = $("[data-index="+index+"]").find("[data-field='authorType']").find('input').val();
    var sex = $("[data-index="+index+"]").find("[data-field='sex']").find('input').val();
    var number = $("[data-index="+index+"]").find("[data-field='number']").find('div').text();
    var tongxun = $("[data-index="+index+"]").find("[data-field='tongxun']").find('em').text();
    var arr = new Array(name,full_spell,xueli,title,unit,authorType,sex,number,tongxun);
    // 姓名 全拼 学历 职称 单位 作者类型 性别 工号 是否为通讯作者
    return arr;
}
function getAllUser()
{
    var table = $("#tableDemo").html();
    table = getAuthor(authorCount-1);
    console.log(table);
}

function claimArticle() {
    var accession_number = $('#accession_number').text();
    
    // 获取所有表格的数据 
    var tableData = new Array();    
    for(var i=0; i<authorCount; i++)
    {
        tableData.push(getAuthor(i));
    }
    
    var flag = true;
    tableData.forEach(element => {
        if(element[4]=='' || element[7]==''){
            layer.msg('作者的中文姓名和学院信息必须填写，请将信息填写完整！');
            flag = false;
        }
    });
    if(!flag){return false;}
    $.ajax({
        url:rootUrl+"/Article/claimArticle",
        type:'post',
        data:{
            accession_number:accession_number,
            tableData:JSON.stringify(tableData)
        },
        dataType: 'json',
        success:function(res)
        {
            layer.msg("填写信息已完整");
            if(res.code == 0)
            {
                layer.msg(res.msg);
                layer.closeAll("iframe");
                //刷新父页面
                parent.location.reload();
            }
            else
            {
                layer.msg(res.msg);
                return false;
            }

            console.log(res);
        },error:function (res) {
            layer.msg("服务器出现错误，请联系系统管理员！");
        }
    });
    
}
