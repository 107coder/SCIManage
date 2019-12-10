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
        // console.log(accession_number);
        //执行一个 table 实例
        var userTable = table.render({
            elem: '#tableDemo'
            , height: 400
            , url: rootUrl+'/Author/getAuthorClaimArticle' //数据接口
            , type:'post'
            , where: {accession_number:accession_number}
            // , page: true //开启分页
            , cols: [[ //表头

                {field: 'full_spell', title: '姓名全拼', width: 120, fixed: 'left'}
                ,{field: 'aId', title: '作者ID', width: 20}
                , {field: 'authorType', title: '作者类型', width: 150,unresize: true}
                , {field: 'number', title: '作者职工号',width: 150}
                , {field: 'name', title: '作者姓名', width: 150}
                , {field: 'sex', title: '性别', width: 85, unresize: true }
                , {field: 'xueli', title: '学历',  width: 150}
                , {field: 'title', title: '职称', width: 100}
                , {field: 'tongxun', title: '是否为通讯作者', width: 150}
                , {field: 'unit', title: '工作单位',}
            ]]
            ,done:function(res){
                authorCount = res.count;
                $("[data-field='aId']").css('display','none');
                $('.layui-table-body').css('overflow','visible');
                $('.layui-table-view').css('overflow','visible');
                $('.layui-table-box').css('overflow','visible');
                // layer.close(load);
            }
        });


    form.on('submit(backArticle)',function()
    {
        var accession_number = $('#accession_number').text();
        layer.confirm('您确定要退回这篇文章吗？', {
            btn: ['确定','取消'] //按钮
        }, function(){
            $.ajax({
                url:rootUrl+"/Article/backArticle",
                type:'post',
                data:{
                    accession_number:accession_number,
                },
                dataType: 'json',
                success:function(res)
                {
                    if(res.code == 0)
                    {
                        layer.msg(res.msg); 
                        setTimeout(() => {
                            layer.closeAll("iframe");
                            //刷新父页面
                            parent.location.reload();
                        }, 1000);
                    }
                    else
                    {
                        layer.msg(res.msg);
                        return false;
                    }
                    
                },error:function (res) {
                    layer.msg("服务器出现错误，请联系系统管理员！");
                }
            });
        }, function(){
            layer.msg('您已取消', {
            time: 1000, //1s后自动关闭
            });
        });

    });

    form.on("submit(pass)",function(){
        $.ajax({
            url:rootUrl+"/Manage/passArticle",
            data:{accession_number:accession_number},
            type:'post',
            dataType:'json',
            success:function(res){
                if(res.code == 0){
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        layer.closeAll("iframe");
                        //刷新父页面
                        parent.location.reload();
                    }, 1000);
                }else{
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        layer.closeAll("iframe");
                        //刷新父页面
                        parent.location.reload();
                    }, 1000);
                }
            },error:function(){
                layer.msg("服务器错误");
            }
        });
    });
    form.on("submit(back)",function(){
        $.ajax({
            url:rootUrl+"/Manage/backArticle",
            data:{accession_number:accession_number},
            type:'post',
            dataType:'json',
            success:function(res){
                if(res.code == 0){
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        layer.closeAll("iframe");
                        //刷新父页面
                        parent.location.reload();
                    }, 1000);
                }else{
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        layer.closeAll("iframe");
                        //刷新父页面
                        parent.location.reload();
                    }, 1000);
                }
            },error:function(){
                layer.msg("服务器错误");
            }
        });
    });
    form.on("submit(cancal)",function(){
        $.ajax({
            url:rootUrl+"/Manage/cancalArticle",
            data:{accession_number:accession_number},
            type:'post',
            dataType:'json',
            success:function(res){
                if(res.code == 0){
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        layer.closeAll("iframe");
                        //刷新父页面
                        parent.location.reload();
                    }, 1000);
                }else{
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        layer.closeAll("iframe");
                        //刷新父页面
                        parent.location.reload();
                    }, 1000);
                }
            },error:function(){
                layer.msg("服务器错误");
            }
        });
    });

})