layui.use(['form','layer','table','laytpl','laypage'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        laypage = layui.laypage;
        $ = layui.jquery,
        laytpl = layui.laytpl,
        table = layui.table;
 
    //用户列表
    var tableIns = table.render({ 
        elem: '#userList',
        url : rootUrl+'/user/getAllUserApi', 
        cellMinWidth : 95,
        page : true,
        height : "full-105", 
        limit : 10,
        limits : [10,20,30,40,50],
        loading : true,
        id : "userListTable",
        cols : [[
            {type: "checkbox", fixed:"left", width:50},
            {field: 'job_number', title: '工号', align:'center',minWidth:150},
            {field: 'name', title: '姓名', minWidth:100, align:"center"},
            {field: 'gender', title: '用户性别', align:'center'},
            {field: 'academy', title: '学院', minWidth:100, align:"center"},
            {field: 'identity', title: '身份', templet:'#identity',minWidth:100, align:"center"},
            {title: '操作', minWidth:175, templet:'#userListBar',fixed:"right",align:"center"}
        ]]
    });
    //用户查询
    var $ = layui.$;
    var demoReload = $('#demoReload');   
      $('.search_btn').on('click', function(){
          //执行重载
          table.reload('userListTable', {
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
    function editUser(data){ 
        
        var index = layui.layer.open({
            title : "修改用户",
            type : 2,
            content : "userEdit.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                body.find(".job_number").val(data.job_number);  
                body.find(".name").val(data.name); 
                body.find(".gender input[value="+data.gender+"]").next('.layui-unselect').addClass("layui-form-radioed");
                body.find(".layui-form-radioed").find('i').addClass("layui-anim-scaleSpring");              
                body.find(".academy").val(data.academy);  
                body.find(".identity option[value="+data.identity+"]").attr('selected',true);
                body.find(".layui-anim-upbit dd[lay-value='0']").removeClass("layui-this"); //取消默认身份颜色样式
                body.find(".layui-anim-upbit dd[lay-value="+data.identity+"]").addClass("layui-this");  //为相应身份添加颜色样式   
                body.find(".layui-unselect").val(returnIdentity(data.identity));    //改变编辑时的身份数值
                $.ajax({
                    url : rootUrl+"/user/checkUser",
                    dataType:"JSON",
                    type : "post",
                    data : {job_number : data.job_number},
                    async : false,
                    success : function(res)
                    {
                        body.find(".birthday").val(res[0].birthday);
                        body.find(".edu_background").val(res[0].edu_background);
                        body.find(".degree").val(res[0].degree); 
                        body.find(".full_spell").val(res[0].full_spell);  
                        body.find(".job_title").val(res[0].job_title);
                        body.find(".job_title_rank").val(res[0].job_title_rank);
                        body.find(".job_title_series").val(res[0].job_title_series);
                    }
                });    
                form.render();
            }
        })
        layui.layer.full(index);
        window.sessionStorage.setItem("index",index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize",function(){
            layui.layer.full(window.sessionStorage.getItem("index"));
        })
    }
    //根据identity的值返回相应文字说明
    function returnIdentity(data){ 
        if(data==0)
            return "普通用户";
        else if(data==1)
            return "院级管理员"; 
        else if(data==2)
            return "校级管理员";
        else
            return "身份错误";
    }

    //添加用户
    function addUser(edit){
        var index = layui.layer.open({
            title : "添加用户",
            type : 2,
            content : "userAdd.html",
        })
        layui.layer.full(index);
        window.sessionStorage.setItem("index",index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize",function(){
            layui.layer.full(window.sessionStorage.getItem("index"));
        })
    }
    $(".addNews_btn").click(function(){
        addUser();
    })

    //批量删除
    $(".delAll_btn").click(function(){
        var checkStatus = table.checkStatus('userListTable'),
            data = checkStatus.data,
            job_number = [];
        if(data.length > 0)
        {
            for (var i in data)
                job_number.push(data[i].job_number);
            layer.confirm('确定删除选中的用户？', {icon: 3, title: '提示信息'}, function (index) {
                $.ajax({
                    url : rootUrl+"/user/delUser",
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
    table.on('tool(userList)', function(obj){
        var layEvent = obj.event,
            data = obj.data;

        if(layEvent === 'edit'){ //编辑
            editUser(data);
        }else 
        if(layEvent === 'del'){ //删除
            layer.confirm('确定删除此用户？',{icon:3, title:'提示信息'},function(index){
                $.ajax({
                    url : rootUrl+"/user/delUser",
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
})
