@extends('admin::index')
@section('content')
    <section class="content-header">
        <h1>
            {{ $header or trans('admin.title') }}
            <small>{{ $description or trans('admin.description') }}</small>
        </h1>
        <!-- breadcrumb start -->
        @if ($breadcrumb)
            <ol class="breadcrumb" style="margin-right: 30px;">
                <li><a href="{{ admin_url('/') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                @foreach($breadcrumb as $item)
                    @if($loop->last)
                        <li class="active">
                            @if (array_has($item, 'icon'))
                                <i class="fa fa-{{ $item['icon'] }}"></i>
                            @endif
                            {{ $item['text'] }}
                        </li>
                    @else
                        <li>
                            <a href="{{ admin_url(array_get($item, 'url')) }}">
                                @if (array_has($item, 'icon'))
                                    <i class="fa fa-{{ $item['icon'] }}"></i>
                                @endif
                                {{ $item['text'] }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ol>
    @endif
    <!-- breadcrumb end -->
    </section>
    <section class="content">
        @include('admin::partials.alerts')
        @include('admin::partials.exception')
        @include('admin::partials.toastr')
        {!! $content !!}
    <script>
        jQuery(document).ready(function($) {
            $("select[name='product_id']").on('change', function(event) {
                event.preventDefault();
                var that = $(this);
                product_id = that.val();


                if(product_id)
                {
                    var url = '/admin/api/attributes/'+product_id;
                    $.ajax({
                        type:'get',
                        url:url,
                        dataType:'json',
                        success:function (data) {
                            console.log(data.length)
                            if (data.length == 0) {
                                /*swal({
                                    title: '该商品没有可选属性',
                                    type: 'warning'
                                });*/
                                that.parents('.form-group').siblings(".myshow").remove();
                            } else {
                                var el = '';
                                for (var i = 0; i < data.length; i++) {
                                    el += '<div class="form-group myshow"><label for="price" class="col-sm-2  control-label">'+data[i].name+'<i style="color:red;"> *</i></label><div class="col-sm-8"><div class="input-group"><span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span><input type="text" name="attributes['+data[i].id+']" value="" data-id="'+data[i].id+'" class="form-control product_attributes" placeholder="输入'+data[i].name+'"></div></div></div>';
                                }
                                that.parents('.form-group').after(el);
                            }
                        },
                        error:function (msg) {
                            swal('系统内部错误', '', 'error');
                        }

                    });
                }

            });

            $('button[type="submit"]').unbind('click');
            $('button[type="submit"]').on('click', function() {
                event.preventDefault();
                var that = $(this);
                var from = $(this).parents('form');
                var formData = from.serialize();
                var method = $("input[name='_method']").val();
                var url = from.attr('action');
                method = method || 'post'; 
                var product_attributes = $(".product_attributes");
                var is_good = true;
                if(product_attributes.length == 0)
                {
                    formData +='&attributes='
                } 
                else (product_attributes.length > 0)
                {
                    $(".product_attributes").each(function (k,v) {
                        if ($(v).val() == '') {
                            is_good = false;
                            return;
                        }
                    });

                    if(!is_good) {
                            toastr.error('请填写完所有的商品属性后再提交');
                            // swal('请填写完所有的商品属性后再提交', '', 'error');
                            return false;
                    }
                }
                

                $.ajax({
                    url: url,
                    type: method,
                    dataType: 'json',
                    data: formData,
                    beforeSend:function()
                    {
                        NProgress.start();
                    },
                    success:function (resp) {
                        //code是200并且有输出进入这里
                        var after_save = $("input[name='after-save']:checked").val();
                        data = resp.data;
                        var url = '/admin/skus';
                        if(typeof(after_save) == 'undefined')
                        {
                            url = '/admin/skus';
                        }
                        else if(after_save == 1)
                        {
                            // 继续编辑
                            url = '/admin/skus/' + data.id + '/edit';
                            
                        }
                        else
                        {
                            // 查看
                            url = '/admin/skus/' + data.id
                        }
                        window.location.href = url
                    },
                    error:function (resp) {
                        if (resp.status == 422) {
                            var obj = resp.responseJSON.errors;
                            var html = '';
                            Object.keys(obj).forEach(function(key){
                                html+=obj[key].join() + "<br/>";
                            });
                            toastr.error(resp.status + ' ' + html);
                            //swal(html, '', 'error');
                        }
                    } ,
                    complete: function (XMLHttpRequest,status) 
                    {
                        NProgress.done();
                    },
                });
                
                return false;

                /* Act on the event */
            });
        });
    </script>
    </section>
@endsection


