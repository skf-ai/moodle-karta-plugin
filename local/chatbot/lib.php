<?php

defined('MOODLE_INTERNAL') || die();

function local_chatbot_before_footer() {
    // Bring Moodle globals into scope so they’re not null.
    global $USER, $COURSE, $PAGE;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    $enabled = get_config('local_chatbot', 'enabledusers');
    if (!empty($enabled)) {
        $ids = array_map('intval', array_map('trim', explode(',', $enabled)));
        if (!in_array($USER->id, $ids)) {
            return;
        }
    }

    $coursename = isset($COURSE->fullname) ? $COURSE->fullname : '';
    $username = fullname($USER);

    // js_call_amd expects an array of arguments. Pass a single options object
    // so the JS init function receives one parameter with userid, username and
    // course name.
    $PAGE->requires->js_call_amd('local_chatbot/chatbot', 'init', [[
        'userid' => $USER->id,
        'username' => $username,
        'coursename' => $coursename
    ]]);
}
