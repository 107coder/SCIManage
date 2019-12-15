layui.use(['form','layer','table','laytpl','laypage','upload'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        laypage = layui.laypage,
        $ = layui.jquery,
        laytpl = layui.laytpl,
        table = layui.table,
        upload = layui.upload;
 
    //用户列表
    var tableIns = table.render({ 
        elem: '#teacherList',
        url : rootUrl+'/user/getAllTeacherApi', 
        cellMinWidth : 95,
        page : true,
        height : "full-105", 
        limit : 30,
        limits : [10,20,30,40,50],
        loading : true,
        id : "teacherListTable",
        cols : [[
            {type: "checkbox", fixed:"left", width:50},
            {field: 'job_number', title: '工号', align:'center',minWidth:150},
            {field: 'name', title: '姓名', minWidth:100, align:"center"},
            {field: 'gender', title: '用户性别', align:'center'},
            {field: 'academy', title: '学院', minWidth:100, align:"center"},
            {field: 'identity', title: '身份', templet:'#identity',minWidth:100, align:"center"},
            {title: '操作', minWidth:175, templet:'#teacherListBar',fixed:"right",align:"center"}
        ]]
    });
    //用户查询
    var $ = layui.$;
    var demoReload = $('#demoReload');   
      $('.search_btn').on('click', function(){
          //执行重载
          table.reload('teacherListTable', {
            page: {
              curr: 1 //重新从第 1 页开始
            }
            ,where: {
                key: demoReload.val()
            }
          });
      });
      $(document).on('keydown', function (event) {  //按enter键搜索
            if (event.keyCode == 13) {
                $(".search_btn").click();
                return false
            }
      });
    //修改用户时的数值的显示
    function editTeacher(data){      
        var index = layui.layer.open({
            title : "修改用户",
            type : 2,
            content : "teacherEdit.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                body.find(".job_number").val(data.job_number);  
                body.find(".name").val(data.name);
                body.find(".gender input[value="+data.gender+"]").next(".layui-form-radio").find('i').click();  //找到目标单选框的临近i标签,然后触发它的click事件
                body.find(".academy").val(data.academy);  
                body.find(".layui-anim-upbit dd[lay-value="+data.identity+"]").click();     //找到目标下拉框的临近i标签,然后触发它的click事件
                var iden = data.identity==2?'校级管理员':(data.undefined==1?'院级管理员':'普通用户');
                body.find(".identity-input").val(iden);
                $.ajax({
                    url : rootUrl+"/user/checkTeacher",
                    dataType:"JSON",
                    type : "post",
                    data : {job_number : data.job_number},
                    async : false,
                    success : function(res)
                    {
                        body.find(".birthday").val(res['data'].birthday);
                        body.find(".edu_background").val(res['data'].edu_background);
                        body.find(".degree").val(res['data'].degree); 
                        body.find(".full_spell").val(res['data'].full_spell);  
                        body.find(".job_title").val(res['data'].job_title);
                        body.find(".job_title_rank").val(res['data'].job_title_rank);
                        body.find(".job_title_series").val(res['data'].job_title_series);
                    }
                });    
                form.render('radio');
            }
        })
        layui.layer.full(index);
        window.sessionStorage.setItem("index",index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize",function(){
            layui.layer.full(window.sessionStorage.getItem("index"));
        })
    }
    //添加用户
    function addTeacher(edit){
        var index = layui.layer.open({
            title : "添加用户",
            type : 2,
            content : "teacherAdd.html",
        })
        layui.layer.full(index);
        window.sessionStorage.setItem("index",index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize",function(){
            layui.layer.full(window.sessionStorage.getItem("index"));
        })
    }
    $(".addNews_btn").click(function(){
        addTeacher();
    })

    //批量删除
    $(".delAll_btn").click(function(){
        var checkStatus = table.checkStatus('teacherListTable'),
            data = checkStatus.data,
            job_number = [];
        if(data.length > 0)
        {
            for (var i in data)
                job_number.push(data[i].job_number);
            layer.confirm('确定删除选中的用户？', {icon: 3, title: '提示信息'}, function (index) {
                $.ajax({
                    url : rootUrl+"/user/delTeacher",
                    type : "post",
                    data : {job_number : job_number},
                    async : false,
                    success : function(res,status)
                    {
                        if(status=="success")
                            top.layer.msg("删除成功！");    
                        else
                            top.layer.msg("删除失败！");
                        tableIns.reload();
                        layer.close(index);
                    }
                });
            })
        }else{
            layer.msg("请选择需要删除的用户");
        }
    })

    //列表操作
    table.on('tool(teacherList)', function(obj){
        var layEvent = obj.event,
            data = obj.data;

        if(layEvent === 'edit'){ //编辑
            editTeacher(data);
        }else 
        if(layEvent === 'del'){ //删除
            layer.confirm('确定删除此用户？',{icon:3, title:'提示信息'},function(index){
                $.ajax({
                    url : rootUrl+"/user/delTeacher",
                    type : "post",
                    data : {job_number : data.job_number},
                    async : false,
                    success : function(res,status)
                    {
                        if(status=="success")
                            top.layer.msg("删除成功！");    
                        else
                            top.layer.msg("删除失败！");
                        tableIns.reload();
                        layer.close(index);
                    }
                });
            });
        }
    });

    // 批量添加用户 / 通过Excel表格导入用户
    var uploadInst = upload.render({
        elem: '#import_data' //绑定元素
        ,url: rootUrl+'/ExcelAction/uploadTeacherFileApi' //上传接口
        ,accept: 'file'
        ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
            layer.load(); //上传loading
        }
        ,done: function(res){
            layer.closeAll('loading'); //关闭loading
            console.log(res);
            if(res.code == 0)
            {
                layer.msg(res.msg);
            }else{
                layer.msg(res.msg);
            }
            table.reload('teacherListTable', { //刷新页面
                page: {
                  curr: 1 
                }
            });
        }
        ,error: function(){
            console.log('error');
            layer.closeAll('loading'); //关闭loading
            layer.msg("导入错误");
        //请求异常回调
        }
    });

    // 通过不同身份验证，判断不同身份的用户显示的按钮不同
    $.ajax({
        url:rootUrl+'/Login/isLogin',
        type:'post',
        dataType:'json',
        success:function (res) {
            if(res.code == 4)
            {
                location.href = webRoot + '/page/login/login.html';
                layer.msg("请先登录");
                return ;
            }else if(res.code == 0){
                if(res.data['identity']==2){
                    $('#import_data').removeClass('layui-hide');
                }else {
                    $('#import_data').remove();
                }
            }
        },error:function()
        {
            console.log('error');
        }
    });
})
