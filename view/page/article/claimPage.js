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
        table.render({
            elem: '#tableDemo'
            // , height: 332
            , url: rootUrl+'/Config/userInfo' //数据接口
            , where: {accession_number:accession_number}
            , type:'post'
            // , page: true //开启分页
            , cols: [[ //表头
                {field: 'full_spell', title: '姓名全拼', width: 120, fixed: 'left'}
                , {field: 'Type', title: '作者类型', width: 150, templet: '#selectType', unresize: true}
                , {field: 'name', title: '作者姓名', edit:'text', width: 150}
                , {field: 'Number', title: '作者职工号', edit: 'text',width: 150}
                , {field: 'Sex', title: '性别', width: 85, templet: '#selectSex', unresize: true }
                , {field: 'Xueli', title: '学历', edit:"text", width: 100}
                , {field: 'Title', title: '职称', edit:"text", width: 100}
                , {field: 'Tongxun', title: '是否为通讯作者', width: 150, templet:"#switchTpl"}
                , {field: 'Unit', title: '工作单位', edit:"text"}
            ]]
        });
        //监听单元格编辑
        table.on('edit(tableDemo)', function(obj){
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,tr = obj.tr  // 获取tr的dom对象
                ,field = obj.field; //得到字段
                var index = data.LAY_TABLE_INDEX;
                $.ajax({
                    url:rootUrl+"/User/checkUser"
                    ,type:'post'
                    ,data:{job_number:data.Number}
                    ,dataType:'json'
                    ,success:function(res){
                        // layer.msg(res);
                        console.log(res);
                    },error:function()
                    {
                        // layer.msg('err');
                    }
                });
                // updateArticle(index);
               

            // layer.msg('[ID: '+ data.full_spell +'] ' + field + ' 字段更改为：'+ value);
        });

        function updateArticle(index,data)
        {
            $("[data-index="+index+"]").find("[data-field='name']").find('div').text('ee');
            $("[data-index="+index+"]").find("[data-field='name']").find('div').text('ee');
            $("[data-index="+index+"]").find("[data-field='Xueli']").find('div').text('ee');
            $("[data-index="+index+"]").find("[data-field='Title']").find('div').text('ee');
            $("[data-index="+index+"]").find("[data-field='Unit']").find('div').text('ee');
            // $("[data-index="+index+"]").find("[data-field='name']").find('div').text('ee');
            // $("[data-index="+index+"]").find("[data-field='name']").find('div').text('ee');
            // $("[data-index="+index+"]").find("[data-field='name']").find('div').text('ee');
        }




})
function claimArticle() {
    var accession_number = $('#accession_number').text();
    $.ajax({
        url:rootUrl+"/Article/claimArticle",
        type:'post',
        data:{accession_number:accession_number},
        dataType: 'json',
        success:function(res)
        {
            console.log(res.data);
            if(res.code == 0)
            {
                layer.msg(res.msg);
            }else
            {
                layer.msg(res.msg);
                return false;
            }

            console.log(res);
        },error:function (res) {
            layer.msg("服务器出现错误，请联系系统管理员！");
        }
    });
    // layer.msg(accession_number);
}