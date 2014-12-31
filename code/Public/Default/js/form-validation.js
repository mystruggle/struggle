var FormValidation = function () {


    return {
		default:{},
        //main function to initiate the module
        init: function () {

            // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation
/*
            var form1 = $('#form_sample_1');
            var error1 = $('.alert-error', form1);
            var success1 = $('.alert-success', form1);

            form1.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",
                rules: {
                    name: {
                        minlength: 2,
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    url: {
                        required: true,
                        url: true
                    },
                    number: {
                        required: true,
                        number: true
                    },
                    digits: {
                        required: true,
                        digits: true
                    },
                    creditcard: {
                        required: true,
                        creditcard: true
                    },
                    occupation: {
                        minlength: 5,
                    },
                    category: {
                        required: true
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit              
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element)
                        .closest('.control-group').removeClass('success').addClass('error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change dony by hightlight
                    $(element)
                        .closest('.control-group').removeClass('error'); // set error class to the control group
                },

                success: function (label) {
                    label
                        .addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                    .closest('.control-group').removeClass('error').addClass('success'); // set success class to the control group
                },

                submitHandler: function (form) {
                    success1.show();
                    error1.hide();
                }
            });

            //Sample 2
            $('#form_2_select2').select2({
                placeholder: "Select an Option",
                allowClear: true
            });
*/

			var option = {};
			if(arguments.length > 0)
			    option = arguments[0];
			this.default.formName = option.formName;

            var form = $(this.default.formName);
            var err = $('.alert-error', form);
            var oSuccess = $('.alert-success', form);

            form.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-inline', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",
/*                rules: {
                    name: {
                        required: true,
                        menuName:true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    category: {
                        required: true
                    },
                    options1: {
                        required: true
                    },
                    options2: {
                        required: true
                    },
                    occupation: {
                        minlength: 5,
                    },
                    membership: {
                        required: true
                    },
                    service: {
                        required: true,
                        minlength: 2
                    }
                },

                messages: { // custom messages for radio buttons and checkboxes
                	name:{
                		required:'菜单名称不能为空！'
                	},
                    membership: {
                        required: "Please select a Membership type"
                    },
                    service: {
                        required: "Please select  at least 2 types of Service",
                        minlength: jQuery.format("Please select  at least {0} types of Service")
                    }
                },
*/
                errorPlacement: function (error, element) { // render error placement for each input type
                    if (element.attr("name") == "education") { // for chosen elements, need to insert the error after the chosen container
                        error.insertAfter("#form_2_education_chzn");
                    } else if (element.attr("name") == "membership") { // for uniform radio buttons, insert the after the given container
                        error.addClass("no-left-padding").insertAfter("#form_2_membership_error");
                    } else if (element.attr("name") == "service") { // for uniform checkboxes, insert the after the given container
                        error.addClass("no-left-padding").insertAfter("#form_2_service_error");
                    } else {
                        error.insertAfter(element); // for other inputs, just perform default behavoir
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit   
                    oSuccess.hide();
                    err.show();
                    App.scrollTo(err, -200);
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.help-inline').removeClass('ok'); // display OK icon
                    $(element)
                        .closest('.control-group').removeClass('success').addClass('error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change dony by hightlight
                    $(element)
                        .closest('.control-group').removeClass('error'); // set error class to the control group
                },

                success: function (label) {
                    if (label.attr("for") == "service" || label.attr("for") == "membership") { // for checkboxes and radip buttons, no need to show OK icon
                        label
                            .closest('.control-group').removeClass('error').addClass('success');
                        label.remove(); // remove error label here
                    } else { // display success icon for other inputs
                        label
                            .addClass('valid').addClass('help-inline ok') // mark the current input as valid and display OK icon
                        .closest('.control-group').removeClass('error').addClass('success'); // set success class to the control group
                    }
                },

                submitHandler: function (form) {
                    oSuccess.show();
                    err.hide();
                    form.submit();
                }

            });
            $(':input[name="name"]').rules('remove');
			//form.rules('remove');
         /*
            $.validator.addMethod('menuName',function(value,element,param){
            	$iLength = value.length;
            	var regexp = /[\u4e00-\u9fa5a-z_0-9]/i;
            	if(!regexp.test(value)){
            		return false;
            	}
            	return true;
            },$.validator.format('只能包含汉字、a-z、_、0-9'));

            //apply validation on chosen dropdown value change, this only needed for chosen dropdown integration.
            $('.chosen, .chosen-with-diselect', form2).change(function () {
                form2.validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
            });

             //apply validation on select2 dropdown value change, this only needed for chosen dropdown integration.
            $('.select2', form2).change(function () {
                form2.validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
            });
*/          
        },
	    addValidMethod:function(name,rule,message){
            $.validator.addMethod(name,function(value,element,param){
				var sMode = '';
				if(typeof(rule[1])!=='undefined'){
					sMode = rule[1];
				}
            	var regexp = new RegExp(rule,sMode);
            	if(!regexp.test(value)){
            		return false;
            	}
            	return true;
            },$.validator.format(message));
        }/*,
		addValidRule:function(rules){
			if(Object.prototype.toString.call(rules) === '[object Array]'){
			}
			$.validator.addClassRules({
				rule:
			});
		},
		addValidRuleMsg:function(message){
			$.validator.addClassMessage();
		}
*/
    };

}();