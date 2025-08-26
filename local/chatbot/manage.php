<?php
require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$search = optional_param('search', '', PARAM_RAW);
$action = optional_param('action', '', PARAM_ALPHA);
$userid = optional_param('userid', 0, PARAM_INT);
$credits = optional_param('credits', 0, PARAM_INT);
$bulkusers = optional_param('bulkusers', '', PARAM_RAW);

if ($action && confirm_sesskey()) {
    if ($action === 'bulkenable' && $bulkusers !== '') {
        $lines = preg_split("/\r\n|\n|\r/", trim($bulkusers));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $user = $DB->get_record('user', ['username' => $line, 'deleted' => 0]);
            if (!$user) {
                $user = $DB->get_record('user', ['email' => $line, 'deleted' => 0]);
            }
            if ($user) {
                $record = $DB->get_record('student_chatbots', ['userid' => $user->id]);
                if ($record) {
                    $record->enabled = 1;
                    $record->remainingcredits += $credits;
                    $DB->update_record('student_chatbots', $record);
                } else {
                    $DB->insert_record('student_chatbots', (object)[
                        'userid' => $user->id,
                        'enabled' => 1,
                        'remainingcredits' => $credits
                    ]);
                }
            }
        }
        redirect(new moodle_url('/local/chatbot/manage.php', ['search' => $search]));
    } else if ($action === 'downloadcsv') {
        $records = $DB->get_records_sql("SELECT u.id, u.firstname, u.lastname, u.email, u.institution, sc.remainingcredits
                                         FROM {user} u
                                         JOIN {student_chatbots} sc ON sc.userid = u.id
                                         WHERE u.deleted = 0 AND sc.remainingcredits > 0
                                         ORDER BY u.lastname, u.firstname");

        $filename = 'chatbot_user_credits_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', get_string('institution'), 'Email', get_string('credits', 'local_chatbot')]);
        foreach ($records as $record) {
            $name = fullname($record);
            fputcsv($output, [$record->id, $name, $record->institution, $record->email, $record->remainingcredits]);
        }
        fclose($output);
        exit;
    } else if ($userid) {
        $record = $DB->get_record('student_chatbots', ['userid' => $userid]);
        if ($action === 'enable') {
            if ($record) {
                $record->enabled = 1;
                $record->remainingcredits += $credits;
                $DB->update_record('student_chatbots', $record);
            } else {
                $DB->insert_record('student_chatbots', (object)[
                    'userid' => $userid,
                    'enabled' => 1,
                    'remainingcredits' => $credits
                ]);
            }
        } else if ($action === 'disable') {
            if ($record) {
                $record->enabled = 0;
                $DB->update_record('student_chatbots', $record);
            } else {
                $DB->insert_record('student_chatbots', (object)[
                    'userid' => $userid,
                    'enabled' => 0,
                    'remainingcredits' => 0
                ]);
            }
        } else if ($action === 'addcredits' && $record) {
            $record->remainingcredits += $credits;
            $DB->update_record('student_chatbots', $record);
        }
        redirect(new moodle_url('/local/chatbot/manage.php', ['search' => $search]));
    }
}

$url = new moodle_url('/local/chatbot/manage.php', ['search' => $search]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'local_chatbot'));

$usersql = "SELECT id, firstname, lastname, email, institution FROM {user} WHERE deleted=0";
$params = [];

if ($search !== '') {
    $like = '%' . $search . '%';
    $usersql .= " AND (".$DB->sql_like('firstname', ':s1')." OR "
                      .$DB->sql_like('lastname', ':s2')." OR "
                      .$DB->sql_like('email', ':s3')." OR "
                      .$DB->sql_like('institution', ':s4').")";
    $params['s1'] = $like;
    $params['s2'] = $like;
    $params['s3'] = $like;
    $params['s4'] = $like;
}

$users = $DB->get_records_sql($usersql . ' ORDER BY lastname, firstname', $params);

echo $OUTPUT->header();
$PAGE->requires->js_call_amd('local_chatbot/manage', 'init');

echo html_writer::start_tag('form', ['method' => 'get', 'action' => new moodle_url('/local/chatbot/manage.php')]);
echo html_writer::tag('input', '', ['type' => 'text', 'name' => 'search', 'value' => $search, 'placeholder' => get_string('search')]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('search')]);
echo html_writer::end_tag('form');

echo html_writer::tag('button', get_string('bulkaddstudents', 'local_chatbot'), [
    'type' => 'button',
    'class' => 'btn btn-secondary mb-3',
    'id' => 'bulk-add-button'
]);

$downloadurl = new moodle_url('/local/chatbot/manage.php', [
    'action' => 'downloadcsv',
    'sesskey' => sesskey()
]);
echo html_writer::tag('a', get_string('downloadcreditsreport', 'local_chatbot'), [
    'href' => $downloadurl,
    'class' => 'btn btn-secondary mb-3 ml-2'
]);

$table = new html_table();
$table->id = 'chatbot-user-table';
$table->head = ['ID', 'Name', get_string('institution'), 'Email', get_string('credits', 'local_chatbot'), ''];

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
        'class' => 'btn '.($enabled ? 'btn-danger' : 'btn-primary'),
        'data-action' => $enabled ? '' : 'enable',
        'data-name' => fullname($user)
    ]);

    if ($enabled) {
        $addurl = new moodle_url('/local/chatbot/manage.php', [
            'userid' => $user->id,
            'action' => 'addcredits',
            'search' => $search,
            'sesskey' => sesskey()
        ]);
        $addbutton = html_writer::tag('a', get_string('addcredits', 'local_chatbot'), [
            'href' => $addurl,
            'class' => 'btn btn-secondary ml-1',
            'data-action' => 'addcredits',
            'data-name' => fullname($user)
        ]);
        $button .= ' '.$addbutton;
    }

    $name = fullname($user);
    $creditsleft = $record ? $record->remainingcredits : 0;
    $table->data[] = [$user->id, $name, $user->institution, $user->email, $creditsleft, $button];
}

echo html_writer::table($table);

$modal = html_writer::start_div('modal fade', ['id' => 'bulk-add-modal', 'tabindex' => '-1', 'role' => 'dialog', 'aria-hidden' => 'true']);
$modal .= html_writer::start_div('modal-dialog', ['role' => 'document']);
$modal .= html_writer::start_div('modal-content');
$modal .= html_writer::start_tag('form', ['method' => 'post', 'action' => $url]);
$modal .= html_writer::start_div('modal-header');
$modal .= html_writer::tag('h5', get_string('bulkaddstudents', 'local_chatbot'), ['class' => 'modal-title']);
$modal .= html_writer::tag('button', html_writer::tag('span', '&times;', ['aria-hidden' => 'true']), [
    'type' => 'button',
    'class' => 'close',
    'data-dismiss' => 'modal',
    'aria-label' => get_string('close')
]);
$modal .= html_writer::end_div();
$modal .= html_writer::start_div('modal-body');
$modal .= html_writer::start_div('form-group');
$modal .= html_writer::tag('label', get_string('studentlist', 'local_chatbot'), ['for' => 'bulk-users-textarea']);
$modal .= html_writer::tag('textarea', '', ['class' => 'form-control', 'id' => 'bulk-users-textarea', 'name' => 'bulkusers', 'rows' => 10]);
$modal .= html_writer::end_div();
$modal .= html_writer::start_div('form-group');
$modal .= html_writer::tag('label', get_string('creditsperstudent', 'local_chatbot'), ['for' => 'bulk-credits-input']);
$modal .= html_writer::empty_tag('input', ['type' => 'number', 'class' => 'form-control', 'id' => 'bulk-credits-input', 'name' => 'credits', 'value' => 300]);
$modal .= html_writer::end_div();
$modal .= html_writer::end_div();
$modal .= html_writer::start_div('modal-footer');
$modal .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'bulkenable']);
$modal .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
$modal .= html_writer::tag('button', get_string('savechanges'), ['type' => 'submit', 'class' => 'btn btn-primary']);
$modal .= html_writer::tag('button', get_string('cancel'), ['type' => 'button', 'class' => 'btn btn-secondary', 'data-dismiss' => 'modal']);
$modal .= html_writer::end_div();
$modal .= html_writer::end_tag('form');
$modal .= html_writer::end_div();
$modal .= html_writer::end_div();
$modal .= html_writer::end_div();

echo $modal;

echo $OUTPUT->footer();
