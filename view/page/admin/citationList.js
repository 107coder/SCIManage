layui.use(['form','layer','laydate','table','laytpl','upload'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        upload = layui.upload,
        table = layui.table;

    
    //新闻列表
    var tableIns = table.render({
        elem: '#newsList',
        url : rootUrl+'/citation/getCitation',
        cellMinWidth : 95,
        page : true,
        height : "full-105",
        limit : 20,
        limits : [10,20,30,40,50],
        loding : true,
        id : "newsListTable",
        cols : [[
            
            {field: 'title', title: '文章标题', width:250},
            {field: 'author', title: '文章作者', align:'center',width:200},
            {field: 'source', title: '文章来源', align:'center',minWidth:200},
            {field: 'reprint_author', title: '通讯作者', align:'center',width:200},
            {field: 'status', title: '论文状态', width:110,  align:'center',templet:"#articleStatus"},
            {field: 'claimer_name', title: '认领人', align:'center',width:100},
            
            {field: 'date', title: '发表时间', align:'center', minWidth:110},
            
            {field: '2018_time', title: '2018年他引次数', align:'center',width:150},
            {field: '2019_time', title: '2019年他引次数', align:'center',width:150},
            {title: '操作', width:180, templet:'#newsListBar',fixed:"right",align:"center"}
        ]] ,done: function () {
            // $("[data-field='accession_number']").css('display','none');
        }
    });

     //搜索【此功能需要后台配合，所以暂时没有动态效果演示】
     $(".search_btn").on("click",function(){
        if($(".searchVal").val() != ''){
            table.reload("newsListTable",{
                page: {
                    curr: 1 //重新从第 1 页开始
                },
                where: {
                    key: $(".searchVal").val()  //搜索的关键字
                }
            })
        }else{
            layer.msg("请输入搜索的内容");
        }
    });
    $(document).on('keydown', function (event) {  //按enter键搜索
        if (event.keyCode == 13) {
            $(".search_btn").click();
            return false
        }
  });

      // 内容选择查看
      form.on('select(articleType)', function(data){
        console.log(data.value); //得到被选中的值
        table.reload("newsListTable",{
            page: {
                curr: 1 //重新从第 1 页开始
            },
            where: {
                selectType:'articleStatus',
                key: data.value  //搜索的关键字
            }
        })
    });  


  
    //添加文章
    function addNews(edit){
        var index = layui.layer.open({
            title : "添加文章",
            type : 2,
            content : "citationAdd.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                if(edit){
                    body.find(".newsName").val(edit.newsName);
                    body.find(".abstract").val(edit.abstract);
                    body.find(".thumbImg").attr("src",edit.newsImg);
                    body.find("#news_content").val(edit.content);
                    body.find(".newsStatus select").val(edit.newsStatus);
                    body.find(".openness input[name='openness'][title='"+edit.newsLook+"']").prop("checked","checked");
                    body.find(".newsTop input[name='newsTop']").prop("checked",edit.newsTop);
                    form.render();
                }
                setTimeout(function(){
                    layui.layer.tips('点击此处返回文章列表', '.layui-layer-setwin .layui-layer-close', {
                        tips: 3
                    });
                },5000)
            }
        })
        layui.layer.full(index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize",function(){
            layui.layer.full(index);
        })
    }
    $(".addNews_btn").click(function(){
        addNews();
    })

    //批量删除
    $(".delAll_btn").click(function(){
        var checkStatus = table.checkStatus('newsListTable'),
            data = checkStatus.data,
            newsId = [];
        if(data.length > 0) {
            for (var i in data) {
                newsId.push(data[i].newsId);
            }
            layer.confirm('确定删除选中的文章？', {icon: 3, title: '提示信息'}, function (index) {
                // $.get("删除文章接口",{
                //     newsId : newsId  //将需要删除的newsId作为参数传入
                // },function(data){
                tableIns.reload();
                layer.close(index);
                // })
            })
        }else{
            layer.msg("请选择需要删除的文章");
        }
    })

    //列表操作
    table.on('tool(newsList)', function(obj){
        var layEvent = obj.event,
            data = obj.data;

        if(layEvent === 'edit'){ //编辑
            addNews(data);
        } else if(layEvent === 'del'){ //删除
            layer.confirm('确定删除此文章？',{icon:3, title:'提示信息'},function(index){
                $.post(rootUrl+"/Manage/deleteCitation",{
                    citation_number : data.citation_number  //将需要删除的citation_number作为参数传入
                },function(res){
                    if(res.code === 0){
                        layer.msg(res.msg);
                        tableIns.reload();
                        layer.close(index);
                    }else{
                        layer.msg(res.msg);
                        layer.close(index);
                    }
                },'json')
            });
        } else if(layEvent === 'reset'){ //预览
            layer.confirm('确定要重置此文章？',{icon:3, title:'提示信息'},function(index){
                $.post(rootUrl+"/Manage/resetCitation",{
                    citation_number : data.citation_number  //将需要删除的citation_number作为参数传入
                },function(res){
                    if(res.code === 0){
                        layer.msg(res.msg);
                        tableIns.reload();
                        layer.close(index);
                    }else{
                        layer.msg(res.msg);
                        layer.close(index);
                    }
                },'json')
            });
        }
    });
    
    //数据导入
    var uploadInst = upload.render({
        elem: '#import_data' //绑定元素
        ,url: rootUrl+'/ExcelAction/uploadCitationApi' //上传接口
        ,accept: 'file'
        ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
            layer.load(0,{shade: [0.2, '#000']});  //上传loading
        }
        ,done: function(res){
            layer.closeAll('loading'); //关闭loading
            // console.log(res);
            if(res.code == 0)
            {
                layer.msg(res.msg);
                tableIns.reload();
            }
        }
        ,error: function(){
            console.log('error');
            layer.closeAll('loading'); //关闭loading
            layer.msg("导入错误");
        //请求异常回调
        }
    });

    // 数据导出
    $('#export_data').click(function(){
        // 想要通过一个弹出窗口能够 对 想要导出的窗口进行选择
        layer.load(1,{shade:[0.2,"#000"]}); //上传loading
        $.ajax({
            type:'post',
            url:rootUrl+'/ExcelAction/citationExport',
            data:{},
            dataType:'json',
            success:function(res){
                if(res.code == 0){
                    layer.msg(res.msg);
                    location.href = rootUrl+'/ExcelAction/downloadFile?filename='+res.data['filename'];
                    layer.closeAll('loading'); //关闭loading
                }else{
                    layer.closeAll('loading');
                    layer.msg(res.msg);
                }

            },error:function(){
                console.log('error');
                layer.closeAll('loading'); //关闭loading
                layer.msg("导出错误");
            }
        });
        // download();
    });


})