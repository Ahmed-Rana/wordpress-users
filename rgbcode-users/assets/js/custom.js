jQuery(document).ready(function($){
    $(window).on('load', function () {
      setTimeout(loadtable, 200);
    });
    function loadtable(){
	   var dataTable = $('#user_table').DataTable({  
           "processing":true,  
           "serverSide": true,  
           "order":[],  
           "pageLength": 10,
           "ajax":{
			 url: ajax_object.ajaxurl,
			 data:{action: "rgbcode_ajaxusersearch"},
			 type:"POST"
		   }  
      });
    }
});