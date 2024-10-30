(function($){

  $.fn.jstar = function(){
    this.each(function(){
      var select = $(this);
      var parent = select.parent();

      if(select.hasClass('starified')) return;
      select.addClass('starified');

      select.css({
        display:'none'
      });

      var ul = $(document.createElement('ul')).addClass('star-rating');
      var caption = $("<div class='caption'></div>");
      var cnt = 0;
      select.find('option').each(function(){
        cnt++;

        var opt = $(this);
        var li = $("<li><a href='#' onclick='return false;' title='"+opt.text()+"' class='stars"+cnt+"'>"+opt.val()+"</a></li>");
        ul.append(li);

        if(select.val()==opt.val()){
          li.addClass('selected');
          caption.text(opt.text());
        }
      
        li.find('a').mouseenter(function(){
          ul.find('li.selected').removeClass('selected');
          li.addClass('selected');
          select.val(opt.val());
          caption.text(opt.text());
        });

     
      });
    
      parent.append(ul);
      parent.append(caption);
    });
  };

})(jQuery);
