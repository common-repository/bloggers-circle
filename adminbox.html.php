<div id='bloggers_circle_section'>
  <div class='misc-pub-section'>
    <div title='Your score calculated from the quality of all of your reviews. The higher your score, the more points you earn each time you review another bloggers draft.'><label style='padding-bottom:14px;'>Your Rating:</label><div style='display:inline-block'>
        <?php
          for ($i = 0; $i < 5; $i++) {
          	$uistar = ($i < intval($this->options['rating'])) ? "star-on":"star-empty";
            echo "<div class='{$uistar}'></div>";
          }
        ?></div></div>
    <?php
        if (!empty($this->options['supportPage']) && intval($this->options['rating']) <= 0)
          echo "<p style='clear:both;font-size:80%;color:red;'>(you may have been falsely identified as a spammer. <a href='{$this->options['supportPage']}?type=Labled_SPAM'>Click Here</a> to contact support.)</p>";
    ?>
        <div title='Points are like currency. You earn them by reviewing other peoples drafts, and you spend them of hiring other bloggers to review your drafts.'><label>Points to spend:</label> <b id="bloggers_circle_pointsToSpend"><?php echo $this->options['points'] ?></b></div>
        <div title='How many drafts for this post are awating review?'><label>Pending Drafts:</label> <b id="bloggers_circle_pendingDrafts"><?php echo $this->options['pending'] ?></b></div>
        <div title='How many other bloggers are waiting to review your drafts.'><label>Community Size:</label><b> <?php echo $this->options['networkSize'] ?></b></div>
      </div>
      <div class='misc-pub-section' style='text-align:center;line-height:3em;'>
        <a href='#' class='button' onclick="return bloggers_circle.submitPost();" title='Submit your draft for a review by clicking this button.'>Send Draft</a>
        <a href='' class='button' onclick="return bloggers_circle.reviewDraft();" title='Review other bloggers drafts, and earn points. Start by clicking this button.'>Earn Points</a>
      
    <?php
        if (!empty($this->options['bL'])) {
          echo $this->options['bL'];
        }
    ?>
      </div>

      <div class='misc-pub-section' style='text-align:left;font-size:1.1em;'>
    <?php
        if (!empty($reviews)) {
          echo "<div><label>Reviews for this draft:</label></div>";
          echo "<ol class='reviews'>";


          foreach ($reviews as $review) {
            $new = (empty($review->rating) || $review->rating == 0);
            $date = date("D M d Y", strtotime($review->created));
            
				$revid = str_replace('.','_',$review->id);

            echo $new ? "<li id='{$revid}' class='new'>" : "<li id='{$revid}'>";
            echo "<a title='' href='#' onclick='return bloggers_circle.displayReview(\"{$review->id}\",\"{$date}\");'>Revew from {$date}";
            echo "</a>";
            if ($new) {
              echo " <span class='new'>(new)</span>";
              $newReviews[] = $review->id;
            }
          }

          echo "</ol>";
        }
    ?>
        <!-- script>
    <?php /* echo $this->tag?>.newReviews=['<?php echo implode("','",$newReviews) */ ?>'];
</script -->
  </div>
</div>

<div id="dialog-bloggers_circle-sumbit" title="Submit a Draft for Review" class="bloggers_circle_dialog">
  <div class='ui-widget'>
    <blockquote class='ui-corner-all'>
      <p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>	How highly ratted do you want your reviewer to be(at least)?</p>
      <p>(Note: A 2 star reviewer costs 2 points, a 5 star reviewer costs 5 points, and so on)</p>
    </blockquote>
  </div>
  <fieldset  style='position:relative;white-space:nowrap;' class='ui-corner-top'>
    <legend>Choose Minimum Reviewer Rating/Cost:</legend>

    <div>
      <select name="bloggers_circle_rating" class="bloggers_circle_starify">
        <option value="1"  >Not so great (costs:1 point)</option>
        <option value="2"  >Quite good (costs:2 points)</option>
        <option value="3" selected="selected" >Good (costs:3 points)</option>
        <option value="4"  >Great! (costs:4 points)</option>
        <option value="5"  >Excellent! (costs:5 points)</option>
      </select>
    </div>

  </fieldset>
</div>

<div id="dialog-bloggers_circle-review" title="Review Drafts to earn points" class="bloggers_circle_dialog">
  <IFRAME style="border:none;background:transparent;width:100%;height:100%;scroll-x:none;scroll-y:auto;"></IFRAME>
</div>

<div id="dialog-bloggers_circle-displayReview" title="" class="bloggers_circle_dialog">
  <div class='ui-widget'>
    <div class="displayReview ui-corner-all ui-widget-content ui-state-normal-text ui-priority-primary" style="padding:1em;"></div>
  </div>
</div>

<div id="dialog-bloggers_circle-rateReview" title="Rate this Review" class="bloggers_circle_dialog">
  <div class='ui-widget'>
    <blockquote class='ui-corner-all'>
      <p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
        <strong>Our Community depends</strong> on reviews from users, to encourage top quality reviews.</p>
      <p>Please rate the review you just read, to the benefit of the reviewer.</p>
      <p>If the review was abusive or SPAM please indicate below!</p>
    </blockquote>
  </div>
  <fieldset style='position:relative;' class='ui-corner-top'>
    <legend>How would you rate the review?</legend>

    <table border=0 width=100%>
      <tr>
        <td style="text-align:left; white-space:nowrap;">
          <select name="bloggers_circle_ratingReview"  class='bloggers_circle_starify'>
            <option value="1"  >Not so great</option>
            <option value="2"  >Quite good</option>
            <option value="3" selected="selected" >Good</option>
            <option value="4"  >Great!</option>
            <option value="5"  >Excellent!</option>
          </select>
        </td>
        <td style="text-align:right; white-space:nowrap;">
          <strong>or report</strong>
          <button id="button" class="ui-button ui-widget ui-state-normal ui-corner-all ui-button-text-only" role="button" aria-disabled="false" onclick="bloggers_circle.spamReview(); return false;">
            <span class="ui-button-text ui-state-error-text ui-priority-primary">SPAM / Abuse</span>
          </button>
        </td>
      </tr>
    </table>
  </fieldset>
</div>


<div id="dialog-bloggers_circle-message" title="Bloggers-Circle" style="display:none";>
</div>

<?php
            if(!empty($this->message)){
                           $jsonMessage = json_encode($this->message);
                                          echo "<script>bloggers_circle.message({$jsonMessage});</script>";
                                                      }?>

