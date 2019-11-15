<input
        type="checkbox"
        id="<?php echo esc_attr($args['label_for']); ?>"
        name="<?php echo $inputName ?? '' ?>"
        <?php if (($options[$args['label_for']] ?? '') == 'on'): ?>checked="checked"<?php endif ?>
/>

<?php if (isset($args['description'])): ?>
<p class="description">
    <?php esc_html_e( $args['description'], CS_LOCAL_FONT_TEXT_DOMAIN); ?>
</p>
<?php endif ?>
