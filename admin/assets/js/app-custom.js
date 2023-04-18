/*
Document: app-custom.js
Author: Bylancer
Description: Write your custom code here
*/

// Below is an example of function and its initialization
var AppCustom = function() {
	var showAppName = function() {
		//console.log( 'Quickad Classified - Admin & Frontend template' );
	};
    var addAppCredit = function() {
    	var admin_login_card = jQuery('#admin_login_card'),
		admincredit_template = '<div class="text-center"><small>Design Credit : AppUi <a href="https://codentheme.com/item/appui-free-responsive-html-vuejs-angularjs-admin-dashboard/" target="_blank">angular js admin dashboard templates</a></small></div>';
        admin_login_card.after(admincredit_template);
    };
    var AppSettings = function() {
        var url = window.location.href;
        var activeTab = url.substring(url.indexOf("#") + 1);
        if(url.indexOf("#") > -1){
            if(activeTab.length > 0){
                $(".quickad-nav-item").removeClass("active");
                $(".tab-pane").removeClass("active in");
                $("li[data-target = #"+activeTab+"]").addClass("active");
                $("#" + activeTab).addClass("active in");
                $('a[href="#'+ activeTab +'"]').tab('show')
            }
        }
        $(".save-changes").click(function(){
            $(".save-changes").addClass("bookme-progress");
        });
        // wait for the DOM to be loaded
        $(document).ready(function() {
            // bind 'myForm' and provide a simple callback function
            $('form').ajaxForm(function(data) {
                if (data == 0) {
                    alertify.error("Unknown Error generated.");
                } else {
                    data = JSON.parse(data);
                    if (data.status == "success") {
                        alertify.success(data.message);
                    }
                    else {
                        alertify.error(data.message);
                    }
                }
                $(".save-changes").removeClass('bookme-progress');
            });

            /* Mail Method Changer */
            $("#email_type").on('change',function(){
                $(".mailMethods").hide();
                $(".mailMethod-"+$(this).val()).fadeIn('fast');
            });
        });
    };
	return {
		init: function() {
			showAppName();
            addAppCredit();
            AppSettings();
		}
	}
}();

// Initialize AppCustom when page loads
jQuery( function() {
	AppCustom.init();
});
