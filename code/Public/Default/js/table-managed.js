
var TableManaged = function () {

    return {
		default:{
			"formName":'',
			"dataUrl":'',
			"searchField":'',
			"columns":'',
		},
        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }
			var option = {};
			if(arguments.length > 0)
			    option = arguments[0];
			this.default.formName = option.formName;
			this.default.dataUrl = decodeURIComponent(option.dataUrl);
			this.default.searchField = option.searchField;
			this.default.columns = option.columns;

            
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
            var oDT = jQuery(TableManaged.default.formName).dataTable({
                "aoColumns": TableManaged.default.columns,
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
                        'aTargets': [TableManaged.default.columns.length-1],
                        'mData':null,
                        'mRender':function(data,type,full){
                        	var operator = jQuery.parseJSON(full[TableManaged.default.columns.length-1]);
                        	return '<button  class="btn green mini" onclick="TableManaged.del(\''+operator.del+'\')">删除</button>&nbsp;&nbsp;<button  class="btn blue mini" onclick="TableManaged.edit(\''+operator.edit+'\')" >编辑</button>';
                        }
                    }
                ],
                'sAjaxSource':this.default.dataUrl,
                fnInfoCallback:function(){
                    var set = jQuery(TableManaged.default.formName+' .group-checkable').attr('data-set');
                    jQuery(set).uniform();
                },
				initComplete:function(){
                    var oSearchWrapper = jQuery(TableManaged.default.formName).prev().children('.span12');
					$('table thead th').each(function(index,element){
						if(typeof(TableManaged.default.searchField[index]) != 'undefined' && TableManaged.default.searchField[index] != ''){
							var oSearch = jQuery('<input type="text" name="'+TableManaged.default.searchField[index]+'"style="margin-right:7px;" placeholder="search '+jQuery(this).text()+'" />');
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
					});//table thead th
				}
            });
            

            
            
            jQuery(TableManaged.default.formName+' .group-checkable').change(function () {
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

            //jQuery('#sample_1_wrapper .dataTables_filter input').addClass("m-wrap medium"); // modify table search input
            //jQuery('#sample_1_wrapper .dataTables_length select').addClass("m-wrap xsamll"); // modify table per page dropdown
            
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
	        				$(TableManaged.default.formName).dataTable().fnDraw(false);
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