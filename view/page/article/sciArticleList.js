layui.use(['form','layer','laydate','table','laytpl','upload','element'],function(){
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
        url : rootUrl+'/article/getArticleApi',
        cellMinWidth : 95,
        page : true,
        height : "full-105",
        limit : 20,
        limits : [10,20,30,40,50],
        loding : true,
        id : "newsListTable",
        cols : [[
            {type: "checkbox", fixed:"left", width:50},
            {field: 'accession_number', title: 'wos', width:0, align:"center"},
            {field: 'title', title: '文章标题', width:250},
            {field: 'author', title: '发布者', align:'center'},
            {field: 'source', title: '文章来源', align:'center'},
            {field: 'address', title: '通讯作者地址', align:'center'},
            {field: 'articleStatus', title: '论文状态', width:110,  align:'center',templet:"#articleStatus"},
            {field: 'quite_time', title: '引用次数', align:'center'},
            {field: 'is_top', title: '是否置顶', align:'center'},
            {field: 'roll', title: '卷', align:'center'},
            {field: 'period', title: '期', align:'center'},
            {field: 'page', title: '页码', align:'center'},
            // {field: 'newsTop', title: '是否置顶', align:'center', templet:function(d){
            //     return '<input type="checkbox" name="newsTop" lay-filter="newsTop" lay-skin="switch" lay-text="是|否" '+d.newsTop+'>'
            // }},
            {field: 'date', title: '发表时间', align:'center', minWidth:110},
            {field: 'is_first_inst', title: '第一机构', align:'center'},
            {field: 'impact_factor', title: '影响因子', align:'center'},
            {field: 'subject', title: '学科分类', align:'center'},
            {field: 'sci_type', title: 'SCI类型', align:'center'},
            {field: 'other_info', title: '其他信息', align:'center'},

            // 其他一些要隐藏的信息

            {title: '操作', width:70, templet:'#newsListBar',fixed:"right",align:"center"}
        ]] ,done: function () {
            // $("[data-field='accession_number']").css('display','none');
        }

    });

    //是否置顶
    form.on('switch(newsTop)', function(data){
        var index = layer.msg('修改中，请稍候',{icon: 16,time:false,shade:0.8});
        setTimeout(function(){
            layer.close(index);
            if(data.elem.checked){
                layer.msg("置顶成功！");
            }else{
                layer.msg("取消置顶成功！");
            }
        },500);
    })

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

    // 填写信息
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
        body.find(".first_author").text(nullData(data.first_author));
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

 

    //加载论文认领页面的内容
    function claim(edit){
        var accession_number = edit.accession_number;
        var index = layui.layer.open({
            title : "论文认领页面",
            type : 2,
            content : "claimPage.html",
            success : function(layero, index){
                var body = layui.layer.getChildFrame('body', index);
                if(edit){
                    // body.find(".title").val(edit.title);
                    // body.find(".abstract").val(edit.abstract);
                    // body.find(".thumbImg").attr("src",edit.newsImg);
                    // body.find("#news_content").val(edit.content);
                    // body.find(".newsStatus select").val(edit.newsStatus);
                    // body.find(".openness input[name='openness'][title='"+edit.newsLook+"']").prop("checked","checked");
                    // body.find(".newsTop input[name='newsTop']").prop("checked",edit.newsTop);
                    // form.render();
                    fillParameter(body,edit);
                    $.post(rootUrl+'/Article/verifyClaimAuthority',{'accession_number':accession_number},function(res){
                        if(res.code == 1){
                            layer.alert(res.msg, {
                                icon: 0,
                                skin: 'layer-ext-moon' //该皮肤由layer.seaning.com友情扩展。关于皮肤的扩展规则，去这里查阅
                              })
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

    //数据导入
    var uploadInst = upload.render({
        elem: '#import_data' //绑定元素
        ,url: 'http://www.cuisf.top/index.php/ExcelAction/uploadFileApi' //上传接口
        ,accept: 'file'
        ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
            layer.load(); //上传loading
        }
        ,done: function(res){
            layer.closeAll('loading'); //关闭loading
            console.log(res);
            if(res.code == 0)
            {
                layer.msg(res.msg);
            }
        }
        ,error: function(){
            console.log('error');
            layer.closeAll('loading'); //关闭loading
            layer.msg("导入错误");
        //请求异常回调
        }
    });

})