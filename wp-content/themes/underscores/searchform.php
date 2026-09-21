<form id="searchform" class="form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="form-group">
        <label>
            <input id="search" name="s"
                class="js-search" type="text"
                placeholder="<?php echo esc_attr_x('Tìm kiếm', 'placeholder', 'underscores'); ?>" required/>

            <button id="button-search" type="submit" value="<?php _e('Tìm kiếm', 'underscores'); ?>"></button>
        </label>
    </div>
</form>