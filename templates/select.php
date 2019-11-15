<select
        id="<?php echo esc_attr( $args['label_for']); ?>"
        data-custom="<?php echo esc_attr( $args['wporg_custom_data'] ); ?>"
        name="<?php echo $optionName ?? '' ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
>
    <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'red pill', 'wporg' ); ?>
    </option>
    <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
        <?php esc_html_e( 'blue pill', 'wporg' ); ?>
    </option>

</select>

<?php if (isset($args['description'])): ?>
<p class="description">
    <?php esc_html_e( $args['description'], CS_LOCAL_FONT_TEXT_DOMAIN); ?>
</p>
<?php endif ?>
