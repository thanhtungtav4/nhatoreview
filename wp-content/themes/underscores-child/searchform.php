<?php
/**
 * Search form (override parent) — markup theo thiết kế header.
 * Gọi bằng get_search_form().
 */

defined('ABSPATH') || exit;
?>
<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="hd-search-box-wr">
        <div class="box-search">
            <div class="input">
                <input type="text" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php echo esc_attr_x('- Tìm kiếm -', 'placeholder', 'underscores'); ?>" aria-label="<?php echo esc_attr_x('Tìm kiếm', 'label', 'underscores'); ?>">
                <button type="submit" class="btn btn-pri btn-icon" aria-label="<?php echo esc_attr_x('Tìm kiếm', 'submit button', 'underscores'); ?>">
                    <div class="icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></div>
                </button>
            </div>
        </div>
    </div>
</form>
