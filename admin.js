var bloggers_circle = {
  submitPost:function(){
    setTimeout(bloggers_circle._submitPost,0);
    return false;
  },
  _submitPost:function(){
    try{
      jQuery("#dialog-bloggers_circle-sumbit").dialog({
        height: 350,
        width:480,
        modal: true,
        buttons: {
          "Submit Draft": function() {
            jQuery( this ).dialog( "close" );
            var data={
              action:'bloggers_circle_submitPost',
              post_title:jQuery("#poststuff input[name='post_title']").val(),
              content:jQuery("#content").val(),
              post_ID:jQuery("#post_ID").val(),
              post_type:jQuery("#post_type").val(),
              post_name:jQuery("#post_name").val(),
              tags:jQuery("#tax-input-post_tag").val(),
              rating:jQuery("#dialog-bloggers_circle-sumbit").find("select[name='bloggers_circle_rating']").val()
            };
            var cost=parseInt(data.rating);
            var pointsToSpend = parseInt(jQuery("#bloggers_circle_pointsToSpend").text());

            if(cost > pointsToSpend){
              alert("Sorry! But you can't spend more points than you have. Try reviewing some other bloggers drafts to earn points.");
              return;
            }

            jQuery.post(ajaxurl, data, function(response) {
              if(response && response.success == true){
                var cost=parseInt(data.rating);
                var pointsToSpend = parseInt(jQuery("#bloggers_circle_pointsToSpend").text());
                jQuery("#bloggers_circle_pointsToSpend").text(pointsToSpend-cost);

                var pendingDrafts = parseInt(jQuery("#bloggers_circle_pendingDrafts").text());
                jQuery("#bloggers_circle_pendingDrafts").text(pendingDrafts+1);

                if(response && response.message){
                  bloggers_circle.message(response.message);
                }
                else {
                  alert('Draft submitted. Thankyou!');
                }

              }else{
                if(response && response.message){
                  bloggers_circle.message(response.message);
                }
                else {
                  alert('Error submitting Draft. Please try again.');
                }
              }
            },'json');/*.ajaxError(function(){
                                        alert("Sorry, there was an error communicating with your wordpress server. Please try again latter.");
                                    });*/
          },
          Cancel: function() {
            jQuery( this ).dialog( "close" );
          }
        }
      });
      jQuery("#dialog-bloggers_circle-sumbit").dialog("open");
    }catch(e){
      setTimeout(bloggers_circle._submitPost,100);
    }
    return false;


  },
  reviewDraft:function(){
    setTimeout(bloggers_circle._reviewDraft,0);
    return false;
  },
  _reviewDraft:function(){

    try{

      jQuery("#dialog-bloggers_circle-review IFRAME").attr("src",bloggers_circle_global.APISite + "list.php?uid=" + bloggers_circle_global.uid);
      jQuery("#dialog-bloggers_circle-review").dialog({
        height: 650,
        width:720,
        modal: true,
        resizeable:true,
        buttons: {
          "Done": function() {
            jQuery( this ).dialog( "close" );
          }
        }
      });
    }catch(e){
      setTimeout(bloggers_circle._reviewDraft,100);
    }
    jQuery("#dialog-bloggers_circle-review").dialog("open");
    return false;
  },
  newReviews:[],
  currentReview:false,
  displayReview:function(revid,lnkName){

    try{
      var data={
        action:'bloggers_circle_displayReview',
        revid:revid
      };
      bloggers_circle.currentReview = revid;
      jQuery.post(ajaxurl, data, function(response) {
        if(response && response.success){
          jQuery("#dialog-bloggers_circle-displayReview").attr('title',"Review for: '"+jQuery('#title').val()+"' "+lnkName);
          jQuery("#dialog-bloggers_circle-displayReview div.displayReview").html(response.text);
          setTimeout(bloggers_circle._displayReview,0);
        }

      },'json')/*.ajaxError(function(){
                                        alert("Sorry, there was an error communicating with your wordpress server. Please try again latter.");
                                    });*/
    }catch(e){
      alert('Error fetching the review, try again.');
    }
    return false;
  },
  _displayReview:function(){
    try{
      jQuery("#dialog-bloggers_circle-displayReview").dialog({
        height: 650,
        width:720,
        modal: true,
        resizeable:true,
        buttons: {
        
          "Done":function() {
            jQuery( this ).dialog( "close" );
            if(jQuery('#'+bloggers_circle.currentReview.replace('.','_')).hasClass('new'))
              bloggers_circle.rateReview();
          }
        },
        close:function(){
        }
      });
      jQuery("#dialog-bloggers_circle-displayReview").dialog("open");
    }catch(e){
      setTimeout(bloggers_circle._displayReview,100);
    }
    return false;
  },

  rateReview:function(){
    try{
      jQuery("#dialog-bloggers_circle-rateReview").dialog({
        height: 400,
        width:470,
        modal: true,
        resizeable:true,
        buttons: {
          "Rate It!":function() {
            jQuery( this ).dialog( "close" );
            var data={
              action:'bloggers_circle_rateReview',
              revid:bloggers_circle.currentReview,
              rating:jQuery("select[name='bloggers_circle_ratingReview']").val(),
              uid:bloggers_circle_global.uid
            };

            jQuery.post(ajaxurl, data, function(response) {
              if(response && response.success){
                jQuery('#'+bloggers_circle.currentReview.replace('.','_')).removeClass('new');

                if(response && response.message){
                	bloggers_circle.message(response.message);
		          }else {
		            alert("Thankyou!");
		          }
              }else{
                if(response && response.message){
                	bloggers_circle.message(response.message);
		          }else {
		            alert("Error communicating with server");
		          }
              
              }

              
            },'json')/*.ajaxError(function(){
                                        alert("Sorry, there was an error communicating with your wordpress server. Please try again latter.");
                                    });*/

          }
        }
      });
      jQuery("#dialog-bloggers_circle-rateReview").dialog("open");
    }catch(e){
      setTimeout(bloggers_circle.rateReview,100);
    }
    
    return false;
  },
  spamReview:function(){
    jQuery("#dialog-bloggers_circle-rateReview").dialog( "close" );
    var data={
      action:'bloggers_circle_rateReview',
      revid:bloggers_circle.currentReview,
      rating:-5
    };

    jQuery.post(ajaxurl, data, function(response) {
      if(response && response.success){
        jQuery('#'+bloggers_circle.currentReview).css({'display':'none'});
        
        if(response.message){
          bloggers_circle.message(response.message);
        }
        else {
          alert("Thankyou! The spammer will be dealt with harshly!");
        }
      }else{
        
        if(response && response.message){
          bloggers_circle.message(response.message);
        }
        else {
          alert("Sorry, there was an error communicating with your wordpress server. Please try again latter.");
        }
      }
    },'json')/*.ajaxError(function(){
                                        alert("Sorry, there was an error communicating with your wordpress server. Please try again latter.");
                                    });*/
  },
  message:function(userOptions){
    try{
      var options={
        target:"#dialog-bloggers_circle-message",
        title:"Bloggers-Circle",
        content:"<p>A message</p>",
        width:480,
        height:480,
        buttons:{
          Close: function() {
            jQuery( this ).dialog( "close" );
          }
        }
      }
      jQuery.extend(true,options,userOptions);


      jQuery(options.target).attr('title',options.title).html(options.content);


      jQuery(options.target).dialog({
        height: options.height,
        width:options.width,
        modal: true,
        resizeable:true,
        buttons:options.buttons
      });
      jQuery(options.target).dialog("open");
    }catch(e){
      setTimeout(function(){
        bloggers_circle.message(userOptions)
      },100);
    }

  }
}

jQuery(function(){
  //	jQuery("#rat").children().not("select, #rating_title").hide();

  // Create caption element

  // Create stars
  var retry=function(){

    try{
      //var capt = jQuery('#dialog-bloggers_circle-sumbit div.starsRating');
      jQuery("select.bloggers_circle_starify").jstar();

    }catch(e){
      setTimeout(retry,100);
    }
  }();
/*
            <?php
            if(!empty($this->message)){
               $jsonMessage = json_encode($this->message);
               echo "{$this->tag}.message({$jsonMessage});";
            }?>
*/

});
