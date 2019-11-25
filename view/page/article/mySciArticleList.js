layui.use(['form','layer','laydate','table','laytpl'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        table = layui.table;

    //新闻列表
    var tableIns = table.render({
        elem: '#newsList',
        url : rootUrl + '/article/mySciArticle',
        cellMinWidth : 95,
        page : true,
        height : "full-125",
        limit : 20,
        limits : [10,15,20,25],
        id : "newsListTable",
        cols : [[
            // {type: "checkbox", fixed:"left", width:50},
            {field: 'accession_number', title: 'wos', width:0, align:"center"},
            {field: 'title', title: '文章标题', width:250},
            {field: 'author', title: '论文作者', align:'center',width:200},
            {field: 'source', title: '文章来源', align:'center'},
            {field: 'address', title: '通讯作者', align:'center',width:200},
            {field: 'articleStatus', title: '论文状态', width:110,  align:'center',templet:"#articleStatus"},
            {field: 'owner_name', title: '认领人', align:'center'},
            {field: 'date', title: '发表时间', align:'center', minWidth:110},
            {field: 'sci_type', title: '论文类型', align:'center'},

            // 其他一些要隐藏的信息

            {title: '操作', width:70, templet:'#newsListBar',fixed:"right",align:"center"}
        ]] ,done: function () {
            $("[data-field='accession_number']").css('display','none');
        }
    });

    
     // 预览文章时候  填写论文的基本信息信息
     function fillParameter(body,data){
        //判断字段数据是否存在
        function nullData(data){
            if(data == '' || data == "undefined"){
                return "未填写";
            }else{

                return data;
            }
        }
        body.find("#accession_number").text(nullData(data.accession_number));
        body.find("#article_title").text(nullData(data.title));  
        body.find(".article_type").text(nullData(data.article_type));        
        body.find(".claim_author").text(nullData(data.claim_author));
        body.find(".other_author").text(nullData(data.other_author));     
        body.find(".date").text(nullData(data.date));    
        body.find(".source").text(nullData(data.source));    
        body.find(".zk_type").text(nullData(data.zk_type));
        body.find(".sci_type").text(nullData(data.sci_type));
        body.find(".subject").text(nullData(data.subject));
        body.find(".address").text(nullData(data.address));
        body.find(".roll").text(nullData(data.roll));
        body.find(".period").text(nullData(data.period));
        body.find(".page").text(nullData(data.page));
        body.find(".is_first_inst").text(nullData(data.is_first_inst));
        body.find(".is_cover").text(nullData(data.is_cover));
        body.find(".is_top").text(nullData(data.is_top));
        body.find(".other_info").text(nullData(data.other_info));
        body.find(".organization").text(nullData(data.organization));
        body.find(".claim_time").text(nullData(data.claim_time));
        body.find(".impact_factor").text(nullData(data.impact_factor));
   }


    //弹出查看文章的页面
    function previewArticle(edit){
        var index = layui.layer.open({
            title : "查看文章",
            type : 2,
            content : "mySciArticlePage.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                if(edit){
                    fillParameter(body,edit);
                }
                setTimeout(function(){
                    layui.layer.tips('点击此处返回文章列表', '.layui-layer-setwin .layui-layer-close', {
                        tips: 3
                    });
                },500)
            }
        })
        layui.layer.full(index);
        //改变窗口大小时，重置弹窗的宽高，防止超出可视区域（如F12调出debug的操作）
        $(window).on("resize",function(){
            layui.layer.full(index);
        })
    }



    

    //列表操作
    table.on('tool(newsList)', function(obj){
        var layEvent = obj.event,
            data = obj.data;

        if(layEvent === 'edit'){ //编辑
            addNews(data);
        } else if(layEvent === 'back'){ //删除
            layer.confirm('确定退回此论文？',{icon:3, title:'提示信息'},function(index){
                // $.get("删除文章接口",{
                //     newsId : data.newsId  //将需要删除的newsId作为参数传入
                // },function(data){
                    tableIns.reload();
                    layer.close(index);
                // })
            });
        } else if(layEvent === 'look'){ //预览
            previewArticle(data);
        }
    });

})