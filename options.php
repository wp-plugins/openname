<div class="wrap">
<h2>Openname</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<?php settings_fields('openname'); ?>

<p><a href="https://openname.org" target="_blank">Openname</a> is a blockchain-based (the technology behind Bitcoin), decentralized
identity system.</p>

<p>Your users can keep their Wordpress avatar in sync with their Openname
avatar.</p>

<p>Changes to your Openname avatar will take about an hour and or two to be
  reflected in your Wordpress installation. This is because changes to an Openname
  take about 20 minutes to propagate and we also cache Openname
   avatar URLs locally in Wordpress for an hour to improve performance of your site.</p>

<p>You can run your own Openname endpoint and set it below or use the recommended endpoint.</p>
<p>Recommended endpoint: <code>https://onename.com/</code></p>


<table class="form-table">

<tr valign="top">
<th scope="row">Openname Endpoint (advanced):</th>
<td><input type="url" name="openname_endpoint" value="<?php echo get_option('openname_endpoint'); ?>" required /></td>
</tr>

</tr>

</table>


<input type="hidden" name="action" value="update" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>
