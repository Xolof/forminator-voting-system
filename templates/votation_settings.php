<?php
if (!defined('ABSPATH')) {
  exit;
}

$existing_votation_form_ids = FVS_VOTATION_FORM_IDS;

?>

<h1><?= __('Inställningar', 'my-textdomain'); ?></h1>

<?php if (current_user_can('manage_options')): ?>
  <?php $fvs_nonce = wp_create_nonce('fvs_nonce'); ?>
  <div class="fvs_form">
    <form 
      action="<?= esc_url(admin_url('admin-post.php')); ?>"
      method="POST"
      id="fvs_form"
    >
      <fieldset>
        <legend>Formulär för omröstning</legend>
        <?php foreach ($fvs_votation_forminator_forms as $form): ?>
          <div>
            <input
              type="checkbox"
              id="<?= htmlentities($form->id) ?>"
              name="alternatives[<?= htmlentities($form->id) ?>]"
              <?= in_array($form->id, $existing_votation_form_ids) ? 'checked' : null ?>
            />
            <label for="<?= htmlentities($form->id) ?>"><?= htmlentities($form->settings['formName']) ?></label>
          </div>
        <?php endforeach; ?>
      </fieldset>
      <br>
      <fieldset>
        <legend>Tillåt flera inlämningar från samma IP-adress</legend>
        <select name="fvs_allow_multiple_votes_from_same_ip" id="fvs_allow_multiple_votes_from_same_ip">
        <option value="yes" <?= FVS_ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'yes' ? 'selected' : null ?>>Ja</option>
        <option value="no" <?= FVS_ALLOW_MULTIPLE_VOTES_FROM_SAME_IP == 'no' ? 'selected' : null ?>>Nej</option>
        </select> 
      </fieldset>
      <br>
      <fieldset>
        <legend>Blockerade IP-adresser. Ange en kommaseparerad lista med IP-adresser.</legend>
        <textarea 
          id="blocked_ips" 
          name="blocked_ips" 
          rows="5" 
          cols="35"
        ><?= htmlentities(implode(',', FVS_IP_BLOCK_LIST)) ?></textarea>
      </fieldset>
      <br>
      <input type="hidden" name="action" value="fvs_form_response" />
      <input type="hidden" name="fvs_nonce" value="<?= htmlentities($fvs_nonce) ?>" />
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Spara" />
    </form>
  </div>
<?php else: ?>
  <p>You are not authorized to perform this operation.</p>
<?php endif; ?>

