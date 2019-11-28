layui.use(['form','layer','laydate','table','laytpl','upload','element'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        upload = layui.upload,
        table = layui.table;

        // 自动在搜索框内填写登录这的姓名全拼
        $.ajax({
            url:rootUrl+"/User/teacherInfo",
            data:{},
            type:"post",
            dataType:'json',
            success:function(res){
                if(res.code == 0){
                    
                    $(".searchVal").val(res.data["full_spell"]);
                }
            },error:function(){
                layer.msg("服务器错误");
            }
        });
    
    //他引文章列表
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
            {title: '操作', width:90, templet:'#newsListBar',fixed:"right",align:"center"}
        ]] ,done: function () {
            $("[data-field='accession_number']").css('display','none');
         
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
 

    //列表操作
    table.on('tool(newsList)', function(obj){
        var layEvent = obj.event,
            data = obj.data;
        if(layEvent === 'edit'){ //编辑
            addNews(data);
        } else if(layEvent === 'del'){ //删除
            layer.confirm('确定删除此文章？',{icon:3, title:'提示信息'},function(index){
                // $.get("删除文章接口",{
                //     newsId : data.newsId  //将需要删除的newsId作为参数传入
                // },function(data){
                    tableIns.reload();
                    layer.close(index);
                // })
            });
        } else if(layEvent === 'claim'){
            claim(data);
        }
    });

    function claim(data){
        console.log(data.citation_number);
        layer.confirm('确定认领此文章？',{icon:1, title:'提示信息'},function(index){
            $.post(rootUrl+"/Citation/claimCitation",{
                citation_number : data.citation_number  //将需要删除的newsId作为参数传入
            },function(res){
                console.log(res);
                if(res.code == 0){
                    layer.msg(res.msg);
                    tableIns.reload();
                    layer.close(index);
                }else{
                    layer.msg(res.msg);
                }
            },'json')
        });
    }

})