<?php

echo $OUTPUT->box_start();     // The forms section at the top

?>

<div class="mdl-align">

<form method="post" action="index.php">
  <div>
    <input type="text" name="keyword" id="keyword_el" value="<?php p($keyword) ?>" />
    <input type="hidden" name="sesskey" value="<?php echo sesskey();?>" />
    <input type="submit" value="<?php echo get_string('spamsearch', 'tool_spamcleaner')?>" />
  </div>
</form>
<p><?php echo get_string('spameg', 'tool_spamcleaner');?></p>

<hr />

<form method="post"  action="index.php">
  <div>
    <input type="submit" name="autodetect" value="<?php echo get_string('spamauto', 'tool_spamcleaner');?>" />
  </div>
</form>


</div>

<?php
echo $OUTPUT->box_end();