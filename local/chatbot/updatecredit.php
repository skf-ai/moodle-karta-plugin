<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_login();
require_sesskey();

$userid = $USER->id;
$DB = $DB ?? $GLOBALS['DB'];

$record = $DB->get_record('student_chatbots', ['userid' => $userid]);
$credits = 0;
if ($record && $record->enabled) {
    if ($record->remainingcredits > 0) {
        $record->remainingcredits -= 1;
        $credits = $record->remainingcredits;
        $DB->update_record('student_chatbots', $record);
    } else {
        $credits = $record->remainingcredits;
    }
}
header('Content-Type: application/json');
echo json_encode(['credits' => (int)$credits]);

