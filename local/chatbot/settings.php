<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage('local_chatbot', get_string('pluginname', 'local_chatbot'), new moodle_url('/local/chatbot/manage.php')));
}
