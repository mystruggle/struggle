

 <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->

    <!-- BEGIN CORE PLUGINS -->

    <script src="../Public/Default/js/jquery-1.10.1.min.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>

    <!-- IMPORTANT! Load jquery-ui-1.10.1.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->

    <script src="../Public/Default/js/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>      

    <script src="../Public/Default/js/bootstrap.min.js" type="text/javascript"></script>

    <!--[if lt IE 9]>

    <script src="../Public/Default/js/excanvas.min.js"></script>

    <script src="../Public/Default/js/respond.min.js"></script>  

    <![endif]-->   

    <script src="../Public/Default/js/jquery.slimscroll.min.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.blockui.min.js" type="text/javascript"></script>  

    <script src="../Public/Default/js/jquery.cookie.min.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.uniform.min.js" type="text/javascript" ></script>

    <!-- END CORE PLUGINS -->

    <!-- BEGIN PAGE LEVEL PLUGINS -->

    <script src="../Public/Default/js/jquery.vmap.js" type="text/javascript"></script>   

    <script src="../Public/Default/js/jquery.vmap.russia.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.vmap.world.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.vmap.europe.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.vmap.germany.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.vmap.usa.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.vmap.sampledata.js" type="text/javascript"></script>  

    <script src="../Public/Default/js/jquery.flot.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.flot.resize.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.pulsate.min.js" type="text/javascript"></script>

    <script src="../Public/Default/js/date.js" type="text/javascript"></script>

    <script src="../Public/Default/js/daterangepicker.js" type="text/javascript"></script>     

    <script src="../Public/Default/js/jquery.gritter.js" type="text/javascript"></script>

    <script src="../Public/Default/js/fullcalendar.min.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.easy-pie-chart.js" type="text/javascript"></script>

    <script src="../Public/Default/js/jquery.sparkline.min.js" type="text/javascript"></script>  

    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN PAGE LEVEL SCRIPTS -->

    <script src="../Public/Default/js/app.js" type="text/javascript"></script>

    <script src="../Public/Default/js/index.js" type="text/javascript"></script>        

    <!-- END PAGE LEVEL SCRIPTS -->  

    <script>

        jQuery(document).ready(function() {    

           App.init(); // initlayout and core plugins

           Index.init();

           Index.initJQVMAP(); // init index page's custom scripts

           Index.initCalendar(); // init index page's custom scripts

           Index.initCharts(); // init index page's custom scripts

           Index.initChat();

           Index.initMiniCharts();

           Index.initDashboardDaterange();

           Index.initIntro();

        });

    </script>

    <!-- END JAVASCRIPTS -->
</html>