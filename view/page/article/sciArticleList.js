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
            url:rootUrl+"/User/userInfo",
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
    
    //新闻列表
    var tableIns = table.render({
        elem: '#newsList',
        url : rootUrl+'/article/getArticleApi',
        cellMinWidth : 95,
        page : true,
        height : "full-105",
        limit : 20,
        limits : [10,20,30,40,50],
        loding : true,
        id : "newsListTable",
        cols : [[
            // {type: "checkbox", fixed:"left", width:50},
            {field: 'accession_number', title: 'wos', width:0, align:"center"},
            {field: 'title', title: '文章标题', width:250},
            {field: 'author', title: '文章作者', align:'center',width:200},
            {field: 'source', title: '文章来源', align:'center'},
            {field: 'address', title: '通讯作者', align:'center',width:200},
            {field: 'articleStatus', title: '论文状态', width:110,  align:'center',templet:"#articleStatus"},
            {field: 'owner_name', title: '认领人', align:'center'},
            // {field: 'is_top', title: '是否置顶', align:'center'},
            // {field: 'roll', title: '卷', align:'center'},
            // {field: 'period', title: '期', align:'center'},
            // {field: 'page', title: '页码', align:'center'},
            
            {field: 'date', title: '发表时间', align:'center', minWidth:110},
            // {field: 'is_first_inst', title: '第一机构', align:'center'},
            // {field: 'impact_factor', title: '影响因子', align:'center'},
            // {field: 'subject', title: '学科分类', align:'center'},
            {field: 'sci_type', title: '论文类型', align:'center',width:'250'},
            // {field: 'other_info', title: '其他信息', align:'center'},

            // 其他一些要隐藏的信息

            {title: '操作', width:'90', templet:'#newsListBar',fixed:"right",align:"center"}
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

 
   var pageTitle = '<label class="layui-form-label">论文认领页面</label>'
                   +'<label class="layui-form-label" style="color:red;font-wight:bold;">认领步骤：</label>'
                   +'<label style="padding:3px 10px;">第一步：</label><button type="button" class="layui-btn layui-btn-sm" style="width:180px;background-color:#FF5722;">查看论文基本信息</button>' 
                   +'<label  style="padding:3px 10px;" class="">第二步：</label><button type="button" class="layui-btn layui-btn-sm" style="width:180px;background-color:#FF5722;">填写论文作者信息</button>'
                   +'<label  style="padding:3px 10px;" class="">第三步：</label><a href="#claim"><button type="button" class="layui-btn layui-btn-sm" style="width:180px;background-color:#FF5722;" >论文认领</button></a>';
    //加载论文认领页面的内容
    function claim(edit){
        var accession_number = edit.accession_number;
        var index = layui.layer.open({
            title : pageTitle,
            type : 2,
            content : "claimPage.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                if(edit){
                    
                    fillParameter(body,edit);
                    $.post(rootUrl+'/Article/verifyClaimAuthority',{'accession_number':accession_number},function(res){

                        if(res.code == 1){
                            body.find(".claimArticle").addClass('layui-btn-disabled');
                            layer.alert(res.msg, {
                                icon: 0,
                                skin: 'layer-ext-moon' 
                              });
                        }else{
                            if(res.data['articleStatus']!=0){
                                body.find(".claimArticle").addClass('layui-btn-disabled');
                            }
                        }
                    },'json');
                
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
        } else if(layEvent === 'del'){ //删除
            layer.confirm('确定删除此文章？',{icon:3, title:'提示信息'},function(index){
                // $.get("删除文章接口",{
                //     newsId : data.newsId  //将需要删除的newsId作为参数传入
                // },function(data){
                    tableIns.reload();
                    layer.close(index);
                // })
            });
        } else if(layEvent === 'look'){
            claim(data);
        }
    });

})