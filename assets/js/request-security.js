(function($){
 if(!$)return;
 $.ajaxPrefilter(function(options,original,xhr){
  var target=new URL(options.url,location.href);
  if(target.origin===location.origin && /^(POST|PUT|PATCH|DELETE)$/i.test(options.type||'GET')) {
   var token=document.querySelector('meta[name="csrf-token"]');
   if(token)xhr.setRequestHeader('X-CSRF-Token',token.content);
  }
 });
})(window.jQuery);
