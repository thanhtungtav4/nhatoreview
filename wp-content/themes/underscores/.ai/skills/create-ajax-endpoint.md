# Skill: Create AJAX Endpoint

## Description

This skill automates the creation of a new AJAX endpoint in Underscores Theme. It follows the security and structural guidelines in `ajax-handler.md`. The skill generates the handler file (a global function), and registers it via `includes/config/ajax.php` (consumed by `Theme\Hooks\AjaxHook`).

## Parameters

1.  `endpoint_name`: (Required) The name of the endpoint, in PascalCase (e.g., `LoadMorePosts`, `UpdateCart`). This will be used to generate the file name and function name.
2.  `logged_in_only`: (Optional) `true` → handler checks `is_user_logged_in()` / `current_user_can()` and returns 403 otherwise. Defaults to `false`.
    (`Theme\Hooks\AjaxHook` always registers both `wp_ajax_` and `wp_ajax_nopriv_`; access control lives inside the handler.)
3.  `read_only`: (Optional) `true` for public, cacheable reads (load more, filter). Consider a REST `GET` route instead — see "Lưu ý page cache + nonce" in `ajax-handler.md`.

## Execution Steps

1.  **Normalize Names**:
    -   From `endpoint_name` (e.g., `LoadMorePosts`), generate:
        -   File Name: `load-more-posts-ajax.php`
        -   Function Name: `underscores_ajax_load_more_posts`
        -   Action Name: `underscores_ajax_load_more_posts` (handle key in `includes/config/ajax.php`)

2.  **Create AJAX Handler File**:
    -   Create a new file at `includes/ajax/{{file-name}}.php`.
    -   Insert boilerplate: `ABSPATH` check + a global function `{{function_name}}` wrapped in `function_exists`, containing `check_ajax_referer('underscores-ajax-security', 'security', false)` → `wp_send_json_error([...], 403)`, logic placeholder, `wp_send_json_success([...])` (it already dies — no `wp_die()`).
    -   Queries follow `.ai/rules/performance.md` (no N+1, prime caches, `wp_reset_postdata()`).
    -   Add `require_once` for this file in `includes/bootstrap.php`.

3.  **Register in `includes/config/ajax.php`**:
    -   Add `'{{action_name}}' => '{{function_name}}',` to the returned map.
    -   `Theme\Hooks\AjaxHook::register()` wires both `wp_ajax_` and `wp_ajax_nopriv_` automatically.
    -   (If the endpoint must be logged-in only, handle the auth check inside the function.)

4.  **Report and Remind**:
    -   Notify the user that the AJAX endpoint `{{action_name}}` has been created.
    -   Provide the full path to the new handler file.
    -   Show the JS call. Do NOT add another `wp_localize_script`: `underscores_params` (`ajaxURL`, `ajaxNonce`) is already localized on handle `underscores-frontend` by `Theme\Hooks\CommonHook::localize_frontend_params()`.

## Usage Example

**User**: `> create-ajax-endpoint --name=SubmitReview`

**AI (using this skill)**:
1.  Generates `includes/ajax/submit-review-ajax.php` with the global function `underscores_ajax_submit_review` + security checks.
2.  Adds `require_once …/includes/ajax/submit-review-ajax.php;` to `includes/bootstrap.php`.
3.  Adds `'underscores_ajax_submit_review' => 'underscores_ajax_submit_review',` to `includes/config/ajax.php`.
4.  Responds: "Created the AJAX endpoint. Handler at `includes/ajax/submit-review-ajax.php`, registered via `includes/config/ajax.php`. Call it with `fetch(underscores_params.ajaxURL, { method: 'POST', body: new URLSearchParams({ action: 'underscores_ajax_submit_review', security: underscores_params.ajaxNonce }) })` — params are already localized."
