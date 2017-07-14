<?php
if ($_POST['hpfi-submit']) {
    update_option('hpfi-canvasFrames', $_POST['hpfi-canvasFrames']);
    update_option('hpfi-ImageSizeW', $_POST['hpfi-ImageSizeW']);
    update_option('hpfi-ImageSizeH', $_POST['hpfi-ImageSizeH']);
    echo ('<div class="updated"><p><strong>handwrite post for iPad Options Saved</strong></p></div>');
}
?>
<div class="wrap">
  <h2>handwrite post for iPad</h2>
  <form method="post">
<?php wp_nonce_field('update-options'); ?>
    <table class="form-table">
      <tr valign="top">
          <th scope="row">canvas frames</th>
          <td><input type="text" id="hpfi-canvasFrames" name="hpfi-canvasFrames" value="<?php echo get_option('hpfi-canvasFrames'); ?>" /></td>
      </tr>
      <tr valign="top">
          <th scope="row">image display width</th>
          <td><input type="text" id="hpfi-ImageSizeW" name="hpfi-ImageSizeW" value="<?php echo get_option('hpfi-ImageSizeW'); ?>" /></td>
      </tr>
      <tr valign="top">
          <th scope="row">image display height</th>
          <td><input type="text" id="hpfi-ImageSizeH" name="hpfi-ImageSizeH" value="<?php echo get_option('hpfi-ImageSizeH'); ?>" readonly /></td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e( 'Save Settings' ); ?>" />
    </p>
    <input type="hidden" name="hpfi-submit" id="hpfi-submit" value="1" />
  </form>
</div>
<script>
//<![CDATA[

    var widthRatio = 4;
    var heightRatio =3;

    var imgW = jQuery('#hpfi-ImageSizeW');
    var imgH = jQuery('#hpfi-ImageSizeH');

    imgW.blur(calcHeight);

    function calcHeight(){
        var me = jQuery(this);
        var num = me.val() / widthRatio;
        imgH.val(Math.round(num*3));
    }

//]]>
</script>