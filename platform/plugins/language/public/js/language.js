(()=>{function a(e){return a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(a){return typeof a}:function(a){return a&&"function"==typeof Symbol&&a.constructor===Symbol&&a!==Symbol.prototype?"symbol":typeof a},a(e)}function e(e,t){for(var n=0;n<t.length;n++){var l=t[n];l.enumerable=l.enumerable||!1,l.configurable=!0,"value"in l&&(l.writable=!0),Object.defineProperty(e,(o=l.key,r=void 0,r=function(e,t){if("object"!==a(e)||null===e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var l=n.call(e,t||"default");if("object"!==a(l))return l;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(o,"string"),"symbol"===a(r)?r:String(r)),l)}var o,r}var t=function(){function a(){!function(a,e){if(!(a instanceof e))throw new TypeError("Cannot call a class as a function")}(this,a)}var t,n,l;return t=a,l=[{key:"formatState",value:function(a){return!a.id||a.element.value.toLowerCase().includes("...")?a.text:$('<span><img src="'+$("#language_flag_path").val()+a.element.value.toLowerCase()+'.svg" class="img-flag" width="16" alt="Language flag"/> '+a.text+"</span>")}},{key:"createOrUpdateLanguage",value:function(a,e,t,n,l,o,r,i){var g=route("languages.store");i&&(g=route("languages.edit")+"?lang_code="+n),$("#btn-language-submit").addClass("button-loading"),$httpClient.make().post(g,{lang_id:a.toString(),lang_name:e,lang_locale:t,lang_code:n,lang_flag:l,lang_order:o,lang_is_rtl:r}).then((function(e){var t=e.data;i?$(".table-language").find("tr[data-id="+a+"]").replaceWith(t.data):$(".table-language").append(t.data),Botble.showSuccess(t.message)})).finally((function(){$("#language_id").val("").trigger("change"),$("#lang_name").val(""),$("#lang_locale").val(""),$("#lang_code").val(""),$("#flag_list").val("").trigger("change"),$(".lang_is_ltr").prop("checked",!0),$("#btn-language-submit-edit").prop("id","btn-language-submit").text("Add new language"),$("#btn-language-submit").removeClass("button-loading")}))}}],(n=[{key:"bindEventToElement",value:function(){var e=this;jQuery().select2&&$(".select-search-language").select2({width:"100%",templateResult:a.formatState,templateSelection:a.formatState});var t=$(".table-language");$(document).on("change","#language_id",(function(a){var e=$(a.currentTarget).find("option:selected").data("language");void 0!==e&&e.length>0&&($("#lang_name").val(e[2]),$("#lang_locale").val(e[0]),$("#lang_code").val(e[1]),$("#flag_list").val(e[4]).trigger("change"),$(".lang_is_"+e[3]).prop("checked",!0),$("#btn-language-submit-edit").prop("id","btn-language-submit").text("Add new language"))})),$(document).on("click","#btn-language-submit",(function(e){e.preventDefault();var t=$("#lang_name").val(),n=$("#lang_locale").val(),l=$("#lang_code").val(),o=$("#flag_list").val(),r=$("#lang_order").val(),i=$(".lang_is_rtl").prop("checked")?1:0;a.createOrUpdateLanguage(0,t,n,l,o,r,i,0)})),$(document).on("click","#btn-language-submit-edit",(function(e){e.preventDefault();var t=$("#lang_id").val(),n=$("#lang_name").val(),l=$("#lang_locale").val(),o=$("#lang_code").val(),r=$("#flag_list").val(),i=$("#lang_order").val(),g=$(".lang_is_rtl").prop("checked")?1:0;a.createOrUpdateLanguage(t,n,l,o,r,i,g,1)})),t.on("click",".deleteDialog",(function(a){a.preventDefault(),$(".delete-crud-entry").data("section",$(a.currentTarget).data("section")),$(".modal-confirm-delete").modal("show")})),$(".delete-crud-entry").on("click",(function(a){a.preventDefault(),$(".modal-confirm-delete").modal("hide");var n=$(a.currentTarget).data("section");$(e).prop("disabled",!0).addClass("button-loading"),$httpClient.make().delete(n).then((function(a){var e=a.data;e.data&&(t.find("i[data-id="+e.data+"]").unwrap(),$(".tooltip").remove()),t.find('a[data-section="'+n+'"]').closest("tr").remove(),Botble.showSuccess(e.message)})).finally((function(){$(e).prop("disabled",!1).removeClass("button-loading")}))})),t.on("click",".set-language-default",(function(a){a.preventDefault();var e=$(a.currentTarget);$httpClient.make().get(e.data("section")).then((function(a){var n=a.data,l=t.find("td > i");l.replaceWith('<a data-section="'.concat(route("languages.set.default"),"?lang_id=").concat(l.data("id"),'" class="set-language-default" data-bs-toggle="tooltip" data-bs-original-title="Choose ').concat(l.data("name"),' as default language">').concat(l.closest("td").html(),"</a>")),e.find("i").unwrap(),$(".tooltip").remove(),Botble.showSuccess(n.message)}))})),t.on("click",".edit-language-button",(function(a){a.preventDefault();var e=$(a.currentTarget);$httpClient.make().get(route("languages.get")+"?lang_id="+e.data("id")).then((function(a){var e=a.data.data;$("#lang_id").val(e.lang_id),$("#lang_name").val(e.lang_name),$("#lang_locale").val(e.lang_locale),$("#lang_code").val(e.lang_code),$("#flag_list").val(e.lang_flag).trigger("change"),$(".lang_is_rtl").prop("checked",e.lang_is_rtl),$(".lang_is_ltr").prop("checked",!e.lang_is_rtl),$("#lang_order").val(e.lang_order),$("#btn-language-submit").prop("id","btn-language-submit-edit").text("Update")}))})),$(document).on("click",".button-save-language-settings",(function(a){a.preventDefault();var e=$(a.currentTarget);e.addClass("button-loading");var t=e.closest("form"),n=new FormData(t[0]);$httpClient.make().postForm(t.get("action"),n).then((function(a){var e=a.data;Botble.showSuccess(e.message),t.removeClass("dirty")})).finally((function(){e.removeClass("button-loading")}))}))}}])&&e(t.prototype,n),l&&e(t,l),Object.defineProperty(t,"prototype",{writable:!1}),a}();$(document).ready((function(){(new t).bindEventToElement()}))})();