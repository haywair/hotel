define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index:function(){
            Controller.api.setImgState();
            Controller.api.addImage();
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            addImage:function(){
                $('.btn-add').click(function(){
                    var bid = $('#bnb_id').val();
                    var url = 'bnb_clean_photo/add/bid/'+bid;
                    Fast.api.open(url,'添加房间保洁图片');
                });
            },
            setImgState:function(){
                $('.btn-checkone').click(function(){
                    var act = $(this).attr('data-action');
                    var id = $(this).attr('data-id');
                    var state = '';
                    var url = 'bnb_clean_photo/updateState';
                    switch(act){
                        case 'edit':
                            url = 'bnb_clean_photo/edit/id/'+id;
                            break;
                        case 'disables':
                            state = 0;
                            break;
                        case 'ok':
                            state = 1;
                            break;
                        case 'delete':
                            state = -1;
                            break;
                    }
                    if(url == 'bnb_clean_photo/updateState'){
                        var options = {
                            url:url,
                            data:{id:id,status:state}
                        };
                        Fast.api.ajax(options);
                    }else{
                        Fast.api.open(url,'编辑房间图片');
                    }

                });
            }
        }
    };
    return Controller;
});