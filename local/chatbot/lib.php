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
    $PAGE->requires->js_call_amd('local_chatbot/chatbot', 'init', [
        'userid' => $USER->id,
        'coursename' => $coursename
    ]);
}
