!function($){"use strict";$.fn.conditional=function(t){var i=$.extend({data:"conditional",value:"conditional-value",displayOnEnabled:"conditional-display"},t);return this.each(function(){if(void 0===$(this).data(i.data))return!0;var t,a,n,e;$(this).on("change",function(){switch(t=$(this).data(i.data).split(","),a=$(this).data(i.displayOnEnabled),void 0===a&&(a=!0),n=$(this).data(i.value),n=void 0===n?"":String(n).split(","),e=!1,$(this).attr("type")){case"checkbox":e=a?$(this).is(":checked"):!$(this).is(":checked");break;default:e=a?n.length>0?-1!=n.indexOf(String($(this).val())):""!==$(this).val()&&"0"!==$(this).val():n.length>0?-1==n.indexOf(String($(this).val())):""===$(this).val()||"0"===$(this).val();break}for(var h=0;h<t.length;h++){var s;s=$("#"+t[h]).length>0?$("#"+t[h]):$("."+t[h],$(this).parent()),e?$(s).fadeIn(300):$(s).fadeOut(300)}}),$(this).trigger("change")}),this}}(jQuery);