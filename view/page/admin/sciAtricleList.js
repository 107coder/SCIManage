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
        url : rootUrl+'/article/getArticleApi',
        cellMinWidth : 95,
        page : true,
        height : "full-105",
        limit : 30,
        limits : [10,20,30,40,50,100],
        loding : true,
        id : "newsListTable",
        cols : [[
            {type: "checkbox", fixed:"left", width:50},
            // {field: 'newsId', title: 'ID', width:60, align:"center"},
            {field: 'title', title: '文章标题', width:250},
            {field: 'author', title: '发布者', align:'center'},
            {field: 'source', title: '文章来源', align:'center'},
            {field: 'address', title: '通讯作者地址', align:'center'},
            {field: 'quite_time', title: '引用次数', align:'center'},
            {field: 'is_top', title: '是否置顶', align:'center'},
            {field: 'roll', title: '卷', align:'center'},
            {field: 'period', title: '期', align:'center'},
            {field: 'page', title: '页码', align:'center'},
            {field: 'articleStatus', title: '论文状态', width:110, align:'center',templet:"#articleStatus"},
            // {field: 'newsTop', title: '是否置顶', align:'center', templet:function(d){
            //     return '<input type="checkbox" name="newsTop" lay-filter="newsTop" lay-skin="switch" lay-text="是|否" '+d.newsTop+'>'
            // }},
            // {field: 'newsTime', title: '发布时间', align:'center', minWidth:110, templet:function(d){
            //     return d.newsTime.substring(0,10);
            // }},
            {field: 'is_first_inst', title: '是否为第一机构', align:'center'},
            {field: 'impact_factor', title: '影响因子', align:'center'},
            {field: 'subject', title: '学科分类', align:'center'},
            {field: 'sci_type', title: 'SCI类型', align:'center'},
            {field: 'other_info', title: '其他信息', align:'center'},

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
            content : "sciArticleAdd.html",
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
                },50000)
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
        } else if(layEvent === 'look'){ //预览
            layer.alert("此功能需要前台展示，实际开发中传入对应的必要参数进行文章内容页面访问")
        }
    });

    //数据导入
    var uploadInst = upload.render({
        elem: '#import_data' //绑定元素
        ,url: rootUrl+'/ExcelAction/uploadFileApi' //上传接口
        ,accept: 'file'
        ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
            layer.load(); //上传loading
        }
        ,done: function(res){
            layer.closeAll('loading'); //关闭loading
            // console.log(res);
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

  

    // 数据导出
    $('#export_data').click(function(){
        layer.load(1,{shade:[0.2,"#000"]}); //上传loading
        // location.href = rootUrl+'/ExcelAction/sciExport';
        // console.log('d');
        // layer.closeAll('loading'); //关闭loading
        $.ajax({
            type:'post',
            url:rootUrl+'/ExcelAction/sciExport',
            data:{},
            // dataType:'arraybuffer',
            responseType:'arraybuffer',
            success:function(res){
                // console.log(res);
                // download(res);
                downloadFile(res,'test.xls');
                layer.closeAll('loading'); //关闭loading
                // if(res.code == 0)
                // {
                //     layer.msg(res.msg);
                // }
            },error:function(){
                console.log('error');
                layer.closeAll('loading'); //关闭loading
                layer.msg("导出错误");
            }
        });
        // download();
    });
    function download() {
        var url = rootUrl+'/ExcelAction/sciExport';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);        // 也可以使用POST方式，根据接口
        xhr.responseType = "blob";    // 返回类型blob
        // 定义请求完成的处理函数，请求前也可以增加加载框/禁用下载按钮逻辑
        xhr.onload = function () {
            // 请求完成
            if (this.status === 200) {
                // 返回200
                var blob = this.response;
                var reader = new FileReader();
                reader.readAsDataURL(blob);    // 转换为base64，可以直接放入a表情href
                reader.onload = function (e) {
                    // 转换完成，创建一个a标签用于下载
                    var a = document.createElement('a');
                    a.download = 'data.xlsx';
                    a.href = e.target.result;
                    $("body").append(a);    // 修复firefox中无法触发click
                    a.click();
                    $(a).remove();
                }
            }
        };
        // 发送ajax请求
        xhr.send()
        console.log('seccess');
     }

     /**
       * 使用文件数据流下载文件
       * @param {String} content 文件的数据流
       * @param {String} fileName 保存的文件全名（文件名 + 后缀）
       */
       function downloadFile(content, fileName) {
        const blob = new Blob([content], { type: 'application/vnd.ms-excel;charset=utf-8;name="1575463799.xls"' });
        const link = document.createElement('a');
        const url = window.URL || window.webkitURL || window.moxURL;

        link.href = url.createObjectURL(blob); // 将文件流转化为文件地址
        link.download = fileName;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        URL.revokeObjectURL(link.href); // 释放URL 对象
        document.body.removeChild(link);
      }

})