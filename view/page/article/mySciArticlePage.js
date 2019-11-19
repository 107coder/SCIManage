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


    //格式化时间
    function filterTime(val){
        if(val < 10){
            return "0" + val;
        }else{
            return val;
        }
    }

    

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
        var userTable = table.render({
            elem: '#tableDemo'
            // , height: 332
            , url: rootUrl+'/Author/getAuthorClaimArticle' //数据接口
            , type:'post'
            , where: {accession_number:accession_number}
            // , page: true //开启分页
            , cols: [[ //表头
                {field: 'full_spell', title: '姓名全拼', width: 120, fixed: 'left'}
                , {field: 'authorType', title: '作者类型', width: 150, templet: '#selectType', unresize: true}
                , {field: 'number', title: '作者职工号', edit: 'text',width: 150}
                , {field: 'name', title: '作者姓名', edit:'text', width: 150}
                , {field: 'sex', title: '性别', width: 85, templet: '#selectSex', unresize: true }
                , {field: 'xueli', title: '学历', edit:"text", width: 100}
                , {field: 'title', title: '职称', edit:"text", width: 100}
                , {field: 'tongxun', title: '是否为通讯作者', width: 150, templet:"#switchTpl"}
                , {field: 'unit', title: '工作单位', edit:"text"}
            ]]
            ,done:function(res){
                authorCount = res.count;
            }
        });
        //监听单元格编辑
        table.on('edit(tableDemo)', function(obj){
            var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,tr = obj.tr  // 获取tr的dom对象
                ,field = obj.field; //得到字段
                // 控制如果不是修改的工号，就不会给整行填写数据
                console.log(field);
                if(field != 'number'){
                    return false;
                }
                // console.log(data);
                var index = data.LAY_TABLE_INDEX;
                $.ajax({
                    url:rootUrl+"/User/getOneUser"
                    ,type:'post'
                    ,data:{job_number:data.number}
                    ,dataType:'json'
                    ,success:function(res){
                        console.log(res.data);
                        if(res.code==0)
                        {
                            updateAuthor(index,res.data);
                        }else
                        {
                            data = [];
                            updateAuthor(index,data);
                        }
                    },error:function()
                    {
                        // layer.msg('err');
                    }
                });
                // updateAuthor(index);
               

            // layer.msg('[ID: '+ data.full_spell +'] ' + field + ' 字段更改为：'+ value);
        });

        function updateAuthor(index,data)
        {
            $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
            // $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
            $("[data-index="+index+"]").find("[data-field='xueli']").find('div').text(data['edu_background']);
            $("[data-index="+index+"]").find("[data-field='title']").find('div').text(data['job_title']);
            $("[data-index="+index+"]").find("[data-field='unit']").find('div').text(data['academy']);
            // $("[data-index="+index+"]").find("[data-field='sex']").find('find').val(data['gender']);
            $("[data-index="+index+"]").find("[data-field='sex']").find('find').val('女');
            // $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
            // $("[data-index="+index+"]").find("[data-field='name']").find('div').text(data['name']);
        }



})
    

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

// 更新文章信息
function updateAuthorInfo() {
    var accession_number = $('#accession_number').text();
    
    // 获取所有表格的数据 
    var tableData = new Array();    
    for(var i=0; i<authorCount; i++)
    {
        tableData.push(getAuthor(i));
    }
    
    var flag = true;
    tableData.forEach(element => {
        if(element[0]=='' || element[4]=='' || element[7]==''){
            layer.msg('请将信息填写完整');
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

// 文章退回
function backArticle()
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
      }, function(){
        layer.msg('您已取消', {
          time: 1000, //1s后自动关闭
        });
      });

}