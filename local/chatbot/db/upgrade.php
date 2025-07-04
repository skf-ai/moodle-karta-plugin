<?php
function xmldb_local_chatbot_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025070207) {
        // Define table student_chatbots.
        $table = new xmldb_table('student_chatbots');

        // Define all fields of the table.
        $id      = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $userid  = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $enabled = new xmldb_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        // Define keys.
        $primarykey = new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $uniqueuserid = new xmldb_key('userid_unique', XMLDB_KEY_UNIQUE, ['userid']);

        if (!$dbman->table_exists($table)) {
            // Table does not exist, create it with all fields and keys.
            $table->add_field($id);
            $table->add_field($userid);
            $table->add_field($enabled);
            $table->add_key($primarykey);
            $table->add_key($uniqueuserid);
            $dbman->create_table($table);
        } else {
            // Table exists. Ensure required fields and keys exist.
            if (!$dbman->field_exists($table, $id)) {
                $dbman->add_field($table, $id);
            }
            if (!$dbman->field_exists($table, $userid)) {
                $dbman->add_field($table, $userid);
            }
            if (!$dbman->field_exists($table, $enabled)) {
                $dbman->add_field($table, $enabled);
            }

            if (!$dbman->key_exists($table, $primarykey)) {
                $dbman->add_key($table, $primarykey);
            }
            if (!$dbman->key_exists($table, $uniqueuserid)) {
                $dbman->add_key($table, $uniqueuserid);
            }
        }

        upgrade_plugin_savepoint(true, 2025070207, 'local', 'chatbot');
    }

    if ($oldversion < 2025070208) {
        // Add remainingcredits field if it does not exist.
        $table = new xmldb_table('student_chatbots');
        $remaining = new xmldb_field('remainingcredits', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $remaining)) {
            $dbman->add_field($table, $remaining);
        }

        upgrade_plugin_savepoint(true, 2025070209, 'local', 'chatbot');
    }

    return true;
}
