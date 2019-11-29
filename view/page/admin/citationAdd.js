layui.use(['form','layer','layedit','laydate','upload'],function(){
    var form = layui.form
        layer = parent.layer === undefined ? layui.layer : top.layer,
        laypage = layui.laypage,
        upload = layui.upload,
        layedit = layui.layedit,
        laydate = layui.laydate,
        $ = layui.jquery;
        
    //用于同步编辑器内容到textarea
    layedit.sync(editIndex);

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


    $.ajax({
        url:rootUrl+'/Article/getTypeForAdd',
        type:'post',
        dataType:'json',
        success:function(res){
            var data = res.data;
            var html = '';
            if (data.length == 0){
                html += "<option value=''>请添加数据</option>";
                $("#subject").empty().append(html);
            }else{
                $.each(data,function (k,v) {
                    html += "<option value='"+v.subject_name+"'>"+v.subject_name+"</option>"
                })
                $("#subject").empty().append(html);
            }
            form.render();
        },
        error: function (res) {
            layer.error(res.msg);
        }
    });

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
                // console.log(res);
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

})