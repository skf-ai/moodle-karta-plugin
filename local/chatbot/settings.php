<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_chatbot', get_string('pluginname', 'local_chatbot'));
    $settings->add(new admin_setting_configtext('local_chatbot/enabledusers',
        get_string('enabledusers', 'local_chatbot'),
        get_string('enabledusers_desc', 'local_chatbot'), '', PARAM_TEXT));
    $ADMIN->add('localplugins', $settings);
}
