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
        url : rootUrl+'/article/getArticleByAcademyApi',
        cellMinWidth : 95,
        page : true,
        height : "full-105",
        limit : 20,
        limits : [10,20,30,40,50],
        loding : true,
        id : "newsListTable",
        cols : [[
            {field: 'accession_number', title: 'wos', width:0, align:"center"},
            {field: 'title', title: '文章标题', width:250},
            {field: 'author', title: '论文作者', align:'center',width:200},
            {field: 'source', title: '文章来源', align:'center'},
            {field: 'address', title: '通讯作者', align:'center',width:200},
            {field: 'articleStatus', title: '论文状态', width:110,  align:'center',templet:"#articleStatus"},
            {field: 'owner_name', title: '认领人', align:'center'},
            {field: 'date', title: '发表时间', align:'center', minWidth:110},
            {field: 'sci_type', title: '论文类型', align:'center'},

            {title: '操作', width:170, templet:'#newsListBar',fixed:"right",align:"center"}
        ]]
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
        // console.log(data.value); //得到被选中的值
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
        body.find(".author").text(nullData(data.author));
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


     /**
      * 
      * @param 查看文章的内容 学校管理员 edit 
      */
     function review(edit){
        var accession_number = edit.accession_number;
        var index = layui.layer.open({
            title : "论文审核页",
            type : 2,
            content : "SciArticlePage.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                if(edit){
                    
                    fillParameter(body,edit);
                    // 这里控制不同的论文状态的按钮
                    var articleStatus = edit.articleStatus;
                    if(articleStatus == 3){
                        body.find('[lay-filter="cancal"]').css('display','none');
                    }else if(articleStatus==5){
                        body.find('[lay-filter="pass"]').css('display','none');
                        body.find('[lay-filter="back"]').css('display','none');
                    }else if(articleStatus==4){
                        body.find('[lay-filter="pass"]').css('display','none');
                    }else{
                        body.find('[lay-filter="pass"]').css('display','none');
                        body.find('[lay-filter="back"]').css('display','none');
                        body.find('[lay-filter="cancal"]').css('display','none');
                    }
                }
                setTimeout(function(){
                    layui.layer.tips('点击此处返回文章列表', '.layui-layer-setwin .layui-layer-close', {
                        tips: 3,time: 40000
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

     /**
      * 
      * @param 查看文章的内容 院级管理员 edit 
      */
     function review_admin(edit){
        var accession_number = edit.accession_number;
        var index = layui.layer.open({
            title : "论文审核页",
            type : 2,
            content : "SciArticlePage.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                if(edit){
                    
                    fillParameter(body,edit);
                    // 这里控制不同的论文状态的按钮
                    var articleStatus = edit.articleStatus;
                    if(articleStatus == 1){
                        body.find('[lay-filter="cancal"]').css('display','none');
                    }else if(articleStatus==3){
                        body.find('[lay-filter="pass"]').css('display','none');
                        body.find('[lay-filter="back"]').css('display','none');
                    }else if(articleStatus==2){
                        body.find('[lay-filter="pass"]').css('display','none');
                    }else{
                        body.find('[lay-filter="pass"]').css('display','none');
                        body.find('[lay-filter="back"]').css('display','none');
                        body.find('[lay-filter="cancal"]').css('display','none');
                    }
                }
                setTimeout(function(){
                    layui.layer.tips('点击此处返回文章列表', '.layui-layer-setwin .layui-layer-close', {
                        tips: 3,time: 40000
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

        if(layEvent === 'pass'){ //编辑
            pass(data);
        } else if(layEvent === 'back'){ //删除
            layer.confirm('确定退回文章？',{icon:3, title:'提示信息'},function(index){
                $.get(rootUrl+"/Manage/backArticle",{
                    accession_number:data.accession_number  //将需要删除的newsId作为参数传入
                },function(res){
                    if(res.code == 0){
                        tableIns.reload();
                        layer.close(index);
                    }else{
                        layer.msg(res.msg);
                    }
                  
                })
            });
        } else if(layEvent === 'look_root'){ // 校级管理员加载审核页面 
            review(data);
        }else if(layEvent === 'look_admin'){ // 院级管理员加载审核页面
            review_admin(data);
        }
    });

    function pass(data){
        var accession_number = data.accession_number;
        $.ajax({
            url:rootUrl+"/Manage/passArticle",
            data:{accession_number:accession_number},
            type:'post',
            dataType:'json',
            success:function(res){
                if(res.code == 0){
                    layer.msg(res.msg); 
                    setTimeout(() => {
                        tableIns.reload();
                        layer.close(index);
                    }, 1000);
                }else{
                    layer.msg(res.msg);
                }
            },error:function(){
                layer.msg("服务器错误");
            }
        });
    }

})