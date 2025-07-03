<?php
require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$search = optional_param('search', '', PARAM_RAW);
$action = optional_param('action', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);

if ($action && confirm_sesskey() && $userid) {
    $record = $DB->get_record('student_chatbots', ['userid' => $userid]);
    if ($action === 'enable') {
        if ($record) {
            $record->enabled = 1;
            $DB->update_record('student_chatbots', $record);
        } else {
            $DB->insert_record('student_chatbots', (object)[
                'userid' => $userid,
                'enabled' => 1
            ]);
        }
    } else if ($action === 'disable') {
        if ($record) {
            $record->enabled = 0;
            $DB->update_record('student_chatbots', $record);
        } else {
            $DB->insert_record('student_chatbots', (object)[
                'userid' => $userid,
                'enabled' => 0
            ]);
        }
    }
    redirect(new moodle_url('/local/chatbot/manage.php', ['search' => $search]));
}

$url = new moodle_url('/local/chatbot/manage.php', ['search' => $search]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'local_chatbot'));

$usersql = "SELECT id, firstname, lastname, email FROM {user} WHERE deleted=0";
$params = [];

if ($search !== '') {
    $like = '%' . $search . '%';
    $usersql .= " AND (".$DB->sql_like('firstname', ':s1')." OR "
                      .$DB->sql_like('lastname', ':s2')." OR "
                      .$DB->sql_like('email', ':s3').")";
    $params['s1'] = $like;
    $params['s2'] = $like;
    $params['s3'] = $like;
}

$users = $DB->get_records_sql($usersql . ' ORDER BY lastname, firstname', $params);

echo $OUTPUT->header();

echo html_writer::start_tag('form', ['method' => 'get', 'action' => new moodle_url('/local/chatbot/manage.php')]);
echo html_writer::tag('input', '', ['type' => 'text', 'name' => 'search', 'value' => $search, 'placeholder' => get_string('search')]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('search')]);
echo html_writer::end_tag('form');

$table = new html_table();
$table->head = ['ID', 'Name', 'Email', ''];

foreach ($users as $user) {
    $record = $DB->get_record('student_chatbots', ['userid' => $user->id]);
    $enabled = $record && $record->enabled;
    $buttonurl = new moodle_url('/local/chatbot/manage.php', [
        'userid' => $user->id,
        'action' => $enabled ? 'disable' : 'enable',
        'search' => $search,
        'sesskey' => sesskey()
    ]);
    $button = html_writer::tag('a', $enabled ? get_string('disable') : get_string('enable'), [
        'href' => $buttonurl,
        'class' => 'btn '.($enabled ? 'btn-danger' : 'btn-primary')
    ]);
    $name = fullname($user);
    $table->data[] = [$user->id, $name, $user->email, $button];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
