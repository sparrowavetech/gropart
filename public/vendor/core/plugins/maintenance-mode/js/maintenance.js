(()=>{function e(e,n){for(var t=0;t<n.length;t++){var a=n[t];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}var n=function(){function n(){!function(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}(this,n)}var t,a;return t=n,(a=[{key:"init",value:function(){$(document).on("click","#btn-maintenance",(function(e){e.preventDefault();var n=$(e.currentTarget);n.addClass("button-loading"),$.ajax({type:"POST",url:route("system.maintenance.run"),cache:!1,data:n.closest("form").serialize(),success:function(e){if(e.error)Botble.showError(e.message);else{Botble.showSuccess(e.message);var t=e.data;n.text(t.message),t.is_down?(n.addClass("btn-warning").removeClass("btn-info"),n.closest("form").find(".maintenance-mode-notice div span").addClass("text-danger").removeClass("text-success").text(t.notice),t.url&&($("#bypassMaintenanceMode .maintenance-mode-bypass").attr("href",t.url),$("#bypassMaintenanceMode").modal("show"))):(n.removeClass("btn-warning").addClass("btn-info"),n.closest("form").find(".maintenance-mode-notice div span").removeClass("text-danger").addClass("text-success").text(t.notice))}},error:function(e){Botble.handleError(e)},complete:function(){n.removeClass("button-loading")}})}))}}])&&e(t.prototype,a),Object.defineProperty(t,"prototype",{writable:!1}),n}();$(document).ready((function(){(new n).init()}))})();