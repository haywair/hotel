define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/admin/index',
                    add_url: 'auth/admin/add',
                    edit_url: 'auth/admin/edit',
                    bind_url: 'auth/admin/bindBnb',
                    del_url: 'auth/admin/del',
                    multi_url: 'auth/admin/multi',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'groups_text', title: __('Group'), operate:false, formatter: Table.api.formatter.label},
                        {field: 'email', title: __('Email')},
                        {field: 'status', title: __("Status"), formatter: Controller.api.formatter.status},
                        {field: 'logintime', title: __('Login time'), formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Controller.api.events.operate, formatter: function (value, row, index) {
                                if(row.id == Config.admin.id){
                                    return '';
                                }
                                return Controller.api.formatter.operate.call(this, value, row, index);
                            }}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        bindbnb:function(){
            Fast.api.getCitys('province','city');
            Fast.api.refresh();
            Controller.api.bindBnb.checkboxall();
            Controller.api.bindBnb.checkone();
            Controller.api.bindBnb.checkall();
            Controller.api.bindBnb.subform();
            Controller.api.bindBnb.openbind();
            Controller.api.bindevent();
        },
        addbnb:function(){
            Fast.api.getCitys('provinceCode','cityCode');
            Controller.api.bindBnb.getBnbs();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                status: function (value, row, index) {
                    //颜色状态数组,可使用red/yellow/aqua/blue/navy/teal/olive/lime/fuchsia/purple/maroon
                    var colorArr = {"1": 'success', "0": 'grey', "-1": 'danger'};
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    switch(value){
                        case '1':
                            value = '正常';
                            break;
                        case '0':
                            value = '隐藏';
                            break;
                        case '-1':
                            value = "已删除";
                            break;
                    }
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> '+ value+ '</span>';
                    return html;
                },

                datetime: function (value, row, index) {
                    return new Date(parseInt(value) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
                },
                city: function (value, row, index) {
                    var html = "";
                    if (row.city_name) {
                        var html = '<span class="text">' + row.city_name + '</span>';
                    } else {
                        html = '未填写';
                    }
                    return html;
                },
                province: function (value, row, index) {
                    var html = '<span class="text">' + row.province_name + '</span>';
                    return html;
                },
                operate: function (value, row, index) {
                    var table = this.table;
                    // 操作配置
                    var options = table ? table.bootstrapTable('getOptions') : {};
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    /*  buttons.push({name: 'dragsort', icon: 'fa fa-arrows', classname: 'btn btn-xs btn-primary btn-dragsort'});*/
                    buttons.push({name: 'edit', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-editone'});
                    buttons.push({name: 'bind', text: '绑定民宿', classname: 'btn btn-xs btn-primary btn-bindone btn-addtabs'});
                    buttons.push({name: 'del', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-delone'});
                    var html = [];
                    var url, classname, icon, text, title, extend;
                    $.each(buttons, function (i, j) {
                        if (j.name === 'dragsort' && typeof row[Table.config.dragsortfield] === 'undefined') {
                            return true;
                        }
                        if (['add', 'edit', 'del', 'multi', 'dragsort'].indexOf(j.name) > -1 && !options.extend[j.name + "_url"]) {
                            return true;
                        }
                        var attr = table.data("operate-" + j.name);
                        if (typeof attr === 'undefined' || attr) {
                            url = j.url ? j.url : '';
                            if (url.indexOf("{ids}") === -1) {
                                url = url ? url + (url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk] : '';
                            }
                            url = Table.api.replaceurl(url, value, row, table);
                            url = url ? Fast.api.fixurl(url) : 'javascript:;';
                            classname = j.classname ? j.classname : 'btn-primary btn-' + name + 'one';
                            icon = j.icon ? j.icon : '';
                            text = j.text ? j.text : '';
                            title = j.title ? j.title : text;
                            extend = j.extend ? j.extend : '';
                            html.push('<a href="' + url + '" class="' + classname + '" ' + extend + ' title="' + title + '"><i class="' + icon + '"></i>' + (text ? ' ' + text : '') + '</a>');
                        }
                    });
                    return html.join(' ');
                }

            },
            events: {
                operate: {
                    'click .btn-xxxone': function (e, value, row, index) {
                        e.stopPropagation();
                        alert('xxx');
                    },

                    'click .btn-editone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var host = window.location.host;
                        Fast.api.open(options.extend.edit_url + (options.extend.edit_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk],'编辑');
                    },
                    'click .btn-bindone': function (e, value, row, index) {
                        e.stopPropagation();
                        var options = $(this).closest('table').bootstrapTable('getOptions');
                        var url = options.extend.bind_url + (options.extend.bind_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + row[options.pk];
                        Backend.api.addtabs(Backend.api.replaceids(this,url), $(this).attr("title"));
                    },
                    'click .btn-delone': function (e, value, row, index) {
                        e.stopPropagation();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var index = Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function () {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    }

                }
            },
            bindBnb:{
                checkboxall:function(){
                    $('.check-btn-all').click(function(){
                        if($(this).is(':checked')){
                            $('input[name="ids[]"]').prop('checked',true);
                        }else{
                            $('input[name="ids[]"]').removeAttr('checked');
                        }
                    });
                },
                checkone:function(){
                    $(document).on("click",".btn-checkone",function(){
                        var dataId = $(this).attr('data-id');
                        var uid = $('#uid').val();
                        $.ajax({
                            type:'post',
                            url:'auth/admin/removebind',
                            data:{'ids[]':dataId,uid:uid},
                            dataType:'json',
                            success:function(result){
                                if(result.code == 1){
                                    layer.msg(result.msg);
                                    var t=setTimeout("window.location.reload()",2000);
                                }else{
                                    layer.mag(result.msg);
                                }
                            }
                        })
                    });
                },
                checkall:function(){
                    $('.btn-check-all').click(function(){
                        var ids = [];
                        var uid = $('#uid').val();
                        $('input[name="ids[]"]').each(function(i){
                            if(($(this).is(':checked')) && ($(this).val())) {
                                ids[i] = $(this).val();
                            }
                        });
                        $.ajax({
                            type:'post',
                            url:'auth/admin/removebind',
                            data:{'ids[]':ids,uid:uid},
                            dataType:'json',
                            success:function(result){
                                if(result.code == 1){
                                    layer.msg(result.msg);
                                    var t=setTimeout("window.location.reload()",2000);
                                }else{
                                    layer.mag(result.msg);
                                }
                            }
                        })

                    });
                },
                openbind:function(){
                    $('.btnBind').click(function(){
                        var uid = $('#uid').val();
                        var url = 'auth/admin/addBnb?uid='+uid;
                        Fast.api.open(url,'添加绑定');
                    });
                },
                getBnbs:function(){
                    $('#cityCode').change(function(){
                        var uid = $('#ids').val();
                        var provinceCode = $('#provinceCode').val();
                        var cityCode = $('#cityCode').val();
                        var url = 'auth/admin/getBnbs';
                        $.ajax({
                            url:url,
                            type:'post',
                            data:{uid:uid,areacode:cityCode},
                            dataType:'json',
                            success:function(ret) {
                                if (ret.code == 1) {
                                    var bnbData = ret.data;
                                    var html="<option value=''>请选择</option>";
                                    $.each(bnbData,function(i,item){
                                        html += "<option value='"+item.id+"'>"+item.name+"</option>";
                                    });
                                    $('#bnb_ids').html(html);
                                    $('#bnb_ids').selectpicker('refresh');
                                    $('#bnbs').show();
                                } else {
                                    Toastr.error(ret.msg);
                                }
                            }
                        });
                    });
                },
                subform:function(){
                    $('.btn-submit').click(function(){
                        var uid = $('#uid').val();
                        var bnbName = $('#name').val();
                        var city = $('#city').val();
                        var province = $('#province').val();
                        var html = '';
                        $.ajax({
                            type:'get',
                            url:'auth/admin/bindBnb',
                            data:{ids:uid,name:bnbName,province:province,city:city},
                            dataType:'json',
                            success:function(result){
                                if(result.code == 1) {
                                    var data = result.rows.data;
                                    if(data.length > 0) {
                                        $.each(data, function (i, item) {
                                            html += '<tr><td>';
                                            html += '<input class="check-btn" id="check-' + item.id + '" type="checkbox" name="ids[]"  value="' + item.id + '"></td>';
                                            html += '<td><img src="' + item.bnb_image + '" width="50" height="50"></td>';
                                            html += '<td>' + item.name + '</td>';
                                            html += '<td>' + item.status + '</td>';
                                            html += '<td>' + item.province_name + item.city_name + '</td>';
                                            html += '<td>' + item.room_people + '</td><td>';
                                            html += '<a href="javascript:void(0);" class="btn btn-danger btn-checkone" data-id="' + item.id + '">删除绑定</a>';
                                            html += '</td></tr>';
                                        });
                                    }else{
                                        html = "<tr><td colspan='6'>暂无相关记录</td></tr>"
                                    }
                                    if (result.rows.name) {
                                        $('#name').val(result.rows.name);
                                    }
                                    if (result.rows.province) {
                                        var province = result.rows.province;
                                        $('option[value="' + province + '"]').attr('selected', 'selected');
                                    }
                                    if (result.rows.city) {
                                        var city = result.rows.city;
                                        $('option[value="' + city + '"]').attr('selected', 'selected');
                                    }
                                    $('#page').html(result.page);
                                    $('tbody').html(html);
                                }else{
                                    Toastr.error(result.msg);
                                }
                            }
                        })
                    });
                }
            }
        }
    };
    return Controller;
});