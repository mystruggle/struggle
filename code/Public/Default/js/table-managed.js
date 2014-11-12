
var TableManaged = function () {

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }
            
            // begin first table
			/*
             * l - length changing input control
             * f - filtering input
             * t - The table!
             * i - Table information summary
             * p - pagination control
             * r - processing display element
			 * <>  表示 <div></div>
			 * <"class" >表示<div class="class"></div>
			 * <"#id" >表示<div id="class"></div>
             */
            var oDT = jQuery('#menu_1').dataTable({
                "aoColumns": [
                  { "bSortable": false},
                  { "bSortable": false,"sName":"id"},
                  { "bSortable": false ,'sClass':'center','sName':'name'},
                  { "bSortable": false,"bSearch":'' },
                  { "bSortable": false,"sName":'desc' },
                  { "bSortable": false ,"sName":'parent_id'},
                  { "bSortable": false ,"bSearch":''},
                  { "bSortable": false ,"sName":'create_time'},
                  { "bSortable": false ,"bSearch":''}
                ],
                "aLengthMenu": [
                    [5, 15, 20, -1],
                    [5, 15, 20, "All"] // change per page values here
                ],
                // set the initial value
                'bServerSide':true,
                "sServerMethod":"post",
                "bFilter":true,
                "iDisplayLength": 5,
                "sDom": "<'row-fluid'<'span12'>r>t<'row-fluid'<'span4'l><'span2'i><'span6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                	"sSearch":'查询：',
                	//'sProcessing':'sProcessing',
                	'sZeroRecords':'没有找到记录',
                	'sInfoEmpty':'没有数据',
                	'sInfoFiltered':'(从 _MAX_ 条数据中检索)',
                    "sLengthMenu": "每页  _MENU_ 记录",
                    'sInfo':'当前第 _START_ - _END_ 条 共计 _TOTAL_ 条',
                    "oPaginate": {
                    	'sFirst':'首页',
                        "sPrevious": "上一页",
                        "sNext": "下一页",
                        'sLast':'尾页'
                    }
                },
                //'bStateSave':true,
                "aoColumnDefs": [{
                        'bSortable': false,
                        'aTargets': [0],
                        'mData':null,
                        'mRender':function(data,type,full){
                        	return '<input type="checkbox" class="checkboxes" value="1">';
                        }
                    },
                    {
                        'bSortable': false,
                        'aTargets': [8],
                        'mData':null,
                        'mRender':function(data,type,full){
                        	var operator = jQuery.parseJSON(full[8]);
                        	return '<button  class="btn green mini" onclick="TableManaged.del(\''+operator.del+'\')">删除</button>&nbsp;&nbsp;<button  class="btn blue mini" onclick="TableManaged.edit(\''+operator.edit+'\')" >编辑</button>';
                        }
                    }
                ],
                'sAjaxSource':_menuListUrl,
                fnInfoCallback:function(){
                    var set = jQuery('#menu_1 .group-checkable').attr('data-set');
                    jQuery(set).uniform();
                },
				initComplete:function(){
                    var oSearchWrapper = jQuery('#menu_1').prev().children('.span12');
					$('table thead th').each(function(index,element){
						if(index != 0 && index!=8 && index!=3 && index!=6){
							var sSearchName = '';
							if(index == 1)sSearchName = 'id';
							if(index == 2)sSearchName = 'name';
							if(index == 4)sSearchName = 'desc';
							if(index == 5)sSearchName = 'parent_id';
							if(index == 7)sSearchName = 'create_time';
							var oSearch = jQuery('<input type="text" name="'+sSearchName+'"style="margin-right:7px;" placeholder="search '+jQuery(this).text()+'" />');
            	            oSearchWrapper.append(oSearch);
							oSearch.on('keydown',function(e){
								var key = e.which;
								if(key != 13)return;
								e.preventDefault();
								var data = '';
								oSearchWrapper.children().each(function(){
									var sKey = $(this).attr('name');
									var sVal = $(this).val();
									data += '"'+sKey+'":"'+sVal+'",';
								});
								if(data.length>0){
									data = "{"+data.substr(0,data.length-1)+'}';
									data = jQuery.parseJSON(data);
								}
					            oDT.fnMultiFilter(data);
							});
						}
					});
		/*
					jQuery('#menu_1 thead th').each(function(index){
						if(index != 0 && index!=8 && index!=3 && index!=6){
							oSearchWrapper.append('<input type="text" style="margin-right:7px;" placeholder="search '+jQuery(this).text()+'" />');
						}
					});
            oSearchWrapper.delegate('input','blur',function(){
                oDT.column(0).search( this.value ).draw();
            	//this.api().column(0).search($(this).val(),true,false).draw();
            });
					*/






				}
            });
            

            
            
            jQuery('#menu_1 .group-checkable').change(function () {
                var set = jQuery(this).attr("data-set");
                var checked = jQuery(this).is(":checked");
                jQuery(set).each(function () {
                    if (checked) {
                        $(this).attr("checked", true);
                    } else {
                        $(this).attr("checked", false);
                    }
                });
                jQuery.uniform.update(set);
            });

            jQuery('#sample_1_wrapper .dataTables_filter input').addClass("m-wrap medium"); // modify table search input
            jQuery('#sample_1_wrapper .dataTables_length select').addClass("m-wrap xsamll"); // modify table per page dropdown
            //jQuery('#sample_1_wrapper .dataTables_length select').select2(); // initialzie select2 dropdown
/*
            // begin second table
            $('#sample_2').dataTable({
                "aLengthMenu": [
                    [5, 15, 20, -1],
                    [5, 15, 20, "All"] // change per page values here
                ],
                // set the initial value
                "iDisplayLength": 5,
                "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                    "sLengthMenu": "_MENU_ per page",
                    "oPaginate": {
                        "sPrevious": "Prev",
                        "sNext": "Next"
                    }
                },
                "aoColumnDefs": [{
                        'bSortable': false,
                        'aTargets': [0]
                    }
                ]
            });

            jQuery('#sample_2 .group-checkable').change(function () {
                var set = jQuery(this).attr("data-set");
                var checked = jQuery(this).is(":checked");
                jQuery(set).each(function () {
                    if (checked) {
                        $(this).attr("checked", true);
                    } else {
                        $(this).attr("checked", false);
                    }
                });
                jQuery.uniform.update(set);
            });

            jQuery('#sample_2_wrapper .dataTables_filter input').addClass("m-wrap small"); // modify table search input
            jQuery('#sample_2_wrapper .dataTables_length select').addClass("m-wrap small"); // modify table per page dropdown
            jQuery('#sample_2_wrapper .dataTables_length select').select2(); // initialzie select2 dropdown

            // begin: third table
            $('#sample_3').dataTable({
                "aLengthMenu": [
                    [5, 15, 20, -1],
                    [5, 15, 20, "All"] // change per page values here
                ],
                // set the initial value
                "iDisplayLength": 5,
                "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                    "sLengthMenu": "_MENU_ per page",
                    "oPaginate": {
                        "sPrevious": "Prev",
                        "sNext": "Next"
                    }
                },
                "aoColumnDefs": [{
                        'bSortable': false,
                        'aTargets': [0]
                    }
                ]
            });

            jQuery('#sample_3 .group-checkable').change(function () {
                var set = jQuery(this).attr("data-set");
                var checked = jQuery(this).is(":checked");
                jQuery(set).each(function () {
                    if (checked) {
                        $(this).attr("checked", true);
                    } else {
                        $(this).attr("checked", false);
                    }
                });
                jQuery.uniform.update(set);
            });

            jQuery('#sample_3_wrapper .dataTables_filter input').addClass("m-wrap small"); // modify table search input
            jQuery('#sample_3_wrapper .dataTables_length select').addClass("m-wrap small"); // modify table per page dropdown
            jQuery('#sample_3_wrapper .dataTables_length select').select2(); // initialzie select2 dropdown
            */

        },
        del:function(url){
        	if(confirm('确定要删除吗？')){
	        	jQuery.ajax({
	        		url:url,
	        		dataType:'json',
	        		type:'get',
	        		success:function(feed,ts,jqXhr){
	        			alert(feed.message);
	        			if(feed.status){
	        				$('#menu_1').dataTable().fnDraw(false);
	        			}
	        		},
	        		error:function(xhr,eTxt,eThrown){
	        			alert(eTxt);
	        		}
	        	});
        	}
        },
        edit:function(url){
        	location.href = url;
        }
      

    };

}();