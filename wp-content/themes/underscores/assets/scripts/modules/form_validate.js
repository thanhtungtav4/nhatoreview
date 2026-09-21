/**
 * Validate name
 * 
 * @param {*} element 
 */
export function validate_name(element) {
    let regex = /[0-9]+/g;
    let content = element.val();

    if ( regex.test(content) ) {
        element.val(content.replace(regex, ''));
    }
}

/**
 * Validate phone
 * 
 * @param {*} element 
 */
export function validate_phone(element) {
    let regex = /[^0-9\s]+/g;
    let content = element.val();

    if ( regex.test(content)  ) {
        element.val(content.replace(regex, ''));
    }
}

/**
 * Remove special characters
 * 
 * @param {*} element 
 */
export function remove_special_characters(element) {
    // Regex
    const regex_emoji = /(\u00a9|\u00ae|[\u2000-\u3300]|\ud83c[\ud000-\udfff]|\ud83d[\ud000-\udfff]|\ud83e[\ud000-\udfff])/g;
    const regex_specials = /[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/υαŤＲᎥⒶᎶм]+/gi;

    // Verify
    let content = element.val();
    content = content.replace(regex_emoji, '');
    content = content.replace(regex_specials, '');
    element.val(content);
}