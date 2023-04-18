/*
Document   :  Sync Monitor
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneSyncWidget = (function ($) {
  "use strict";

  // public
  var debug_level = 0;
  var maincontainer = null;
  var loading = null;
  var loaded_page = 0;
  
  var lang = null;
  
  // init function, autoload
  (function init() {
    // load the triggers
    $(document).ready(function(){
      
      maincontainer = $("#WooZone-wrapper");
      loading = maincontainer.find("#WooZone-main-loading");

      // lang = maincontainer.find('#WooZone-lang-translation').html();
      // //lang = JSON.stringify(lang);
      // lang = JSON && JSON.parse(lang) || $.parseJSON(lang);
    });
  })();
  
  // :: CRONJOB STATS
  var cronjob_status = (function() {
    
    var DISABLED              = false; // disable this module!
    var debug_level           = 0,
      reload_timer            = null,
      reload_interval         = 20, // reload products interval in seconds
      reload_countdown        = reload_interval,
      maincontainer           = null,
      what                    = '';

    // Test!
    function __() {};

    // get public vars
    function get_vars() {
      return $.extend( {}, {} );
    };

    // init function, autoload
    (function init() {
      // load the triggers
      $(document).ready(function() {
        maincontainer = $("#WooZone .WooZone-sync-stats");
        what          = maincontainer.data('what');
        console.log( maincontainer, what );
        triggers();
      });
    })();

    // Triggers
    function triggers() {
      if ( DISABLED ) return false;
      else {
        reload_();
      }
    }

    // make request
    function make_request() {
      var data = [];
      
      //WooZone.to_ajax_loader( lang.loading );

      what = $.inArray(what, ['mainstats']) > -1 ? what : '';
      if ( '' == what ) {
        //WooZone.to_ajax_loader_close();
        return false;
      }

      var sub_action = 'cronjob_stats_' + what; //cronjob_stats_mainstats
      data.push({name: 'action', value: 'WooZoneNoAWS_SyncWidget'});
      data.push({name: 'sub_action', value: sub_action});
      data.push({name: 'debug_level', value: debug_level});
      
      data = $.param( data ); // turn the result into a query string
      
      // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
      jQuery.post(ajaxurl, data, function(response) {
        if( response.status == 'valid' ){
          maincontainer.find('table').remove();
          maincontainer.append( response.html );
          reload_();
        }
        //WooZone.to_ajax_loader_close();
      }, 'json');
    }

    function reset_timer() {
      // delete old timer
      clearTimeout(reload_timer);
      reload_timer = null;
    }

    function stop_reload() {
      return reload_countdown <= 0 ? true : false;
    }

    function reload_() {

      // verify if stopped!
      if ( stop_reload() ) {
        // delete old timer
        reset_timer();
        return false;            
      }

      function reload() {
        //console.log( reload_timer, ',', reload_countdown );

        // verify if stopped!
        if ( stop_reload() ) {
          // delete old timer
          reset_timer();
          return false;            
        }
  
        reload_countdown--;
        if ( reload_countdown <= 0 ) {
          // delete old timer
          reset_timer();
          
          reload_countdown = reload_interval;
          
          // load products
          make_request();
        } else {
          reload_timer = setTimeout(reload, 1000);
        }
      };
      reload_timer = setTimeout(reload, 1000);
    }
  })();

  var misc = {
  
    hasOwnProperty: function(obj, prop) {
      var proto = obj.__proto__ || obj.constructor.prototype;
      return (prop in obj) &&
      (!(prop in proto) || proto[prop] !== obj[prop]);
    },
  
    size: function(obj) {
      var size = 0;
      for (var key in obj) {
        if (misc.hasOwnProperty(obj, key)) size++;
      }
      return size;
    }
    
  };

  // external usage
  return {
  }
})(jQuery);
