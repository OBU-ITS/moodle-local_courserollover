<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course rollover plugin
 * @package   courserollover
 * @copyright 2014 Oxford Brookes University
 * @author    Peter Andrew
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('../../backup/util/includes/backup_includes.php');
require_once('../../backup/util/includes/restore_includes.php');

require_once('form_courserollover.php');
require_once('view_courserollover.php');


function split_csv($rawcsv) {
    // Remove Windows \r\n new lines
    $rawcsv = str_replace("\r\n", "\n", $rawcsv);
    $csvrows = array();
    $lines = explode("\n", $rawcsv);
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            $fields = str_getcsv($line);
            $csvrows[] = $fields;
        }
    }
    return $csvrows;
}


function backup_restore_course($oldid, $newid, $excludeactivities) {
    global $CFG,
           $DB,
           $USER;

    // General options
    $options = array(
        'activities' => 1,
        'blocks' => 1,
        'filters' => 1,
        'users' => 0,
        'role_assignments' => 0,
        'comments' => 0,
        'logs' => 0);

    // Check old / new courses exist
    if (!$oldcourse = $DB->get_record('course', array('id'=>$oldid), '*')) {
        return array(false, 'Old course not found');
    }
    if (!$newcourse = $DB->get_record('course', array('id'=>$newid), '*')) {
        return array(false, 'New course not found');
    }

    // Check old course is in correct format
    if ($oldcourse->format == 'site' || $oldcourse->format == 'social' || $oldcourse->format == 'scorm') {
        return array(false, 'Old course is not in weekly or topic format');
    }

    // Perform backup
    $bc = new backup_controller(backup::TYPE_1COURSE, $oldid, backup::FORMAT_MOODLE, backup::INTERACTIVE_YES, backup::MODE_IMPORT, $USER->id);

    // Set general options
    foreach ($options as $name => $value) {
        $setting = $bc->get_plan()->get_setting($name);
        if ($setting->get_status() == backup_setting::NOT_LOCKED) {			            	
            $setting->set_value($value);
        }
    }

    // Exclude specified activities from backup
    $tasks = $bc->get_plan()->get_tasks();
    foreach ($tasks as $task) {
        if ( isset($excludeactivities[ $task->get_name() ]) ) {
            $tasksettings = $task->get_settings();
            $settingtaskincluded = $tasksettings[0];
            $settingsegments = explode('_', $settingtaskincluded->get_name());
            if ($settingsegments[0] == $excludeactivities[ $task->get_name() ]) {
                if ($settingtaskincluded->get_status() == backup_setting::NOT_LOCKED) {
                    $settingtaskincluded->set_value(0);
                }
            }
        }
    }

    $backupid = $bc->get_backupid();
    $backupbasepath = $bc->get_plan()->get_basepath();

    $bc->save_controller();
    $bc->finish_ui();

    $bc->execute_plan();
    $bc->destroy();

    // Check backup succeded
    $tempdestination = $CFG->tempdir . '/backup/' . $backupid;
    if (!file_exists($tempdestination) || !is_dir($tempdestination)) {
        return array(false, 'Error backing up old course');
    }

    // Perform restoration
    $rc = new restore_controller($backupid, $newid, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, backup::TARGET_CURRENT_ADDING);

    // Set general options
    foreach ($options as $name => $value) {
        $setting = $rc->get_plan()->get_setting($name);
        if ($setting->get_status() == backup_setting::NOT_LOCKED) {			            	
            $setting->set_value($value);
        }
    }

    // Check for errors in backup
    if (!$rc->execute_precheck()) {
        $precheckresults = $rc->get_precheck_results();
        if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($backupbasepath);
            }

            $errorinfo = '';
			
            foreach ($precheckresults['errors'] as $error) {
                $errorinfo .= $error;
            }

            if (array_key_exists('warnings', $precheckresults)) {
			    foreach ($precheckresults['warnings'] as $warning) {
                    $errorinfo .= $warning;
                }
            }

            return array(false, $errorinfo);
        }
    }

    $rc->execute_plan();
    $rc->destroy();
    fulldelete($tempdestination);

    return array(true, '');
}


function process_form(view_courserollover $view, form_courserollover $form) {
    // Course IDs CSV
    $courserows = split_csv($form->get_file_content('rolloverids'));
    // Check basic validity of course IDs CSV file
    $i = 1;
    foreach ($courserows as $row) {
        if (count($row) <> 2) {
            $view->coursecsv_error('Incorrect number of fields on line ' . $i);
        }
        $i++;
    }

    // Excluded activities CSV
    $excludeactivities = split_csv($form->get_file_content('excludeactivities'));
    // Check basic validity of exclude activities CSV file
    $i = 1;
    foreach ($excludeactivities as $row) {
        if (count($row) <> 2) {
            $view->excludeactivitiescsv_error('Incorrect number of fields on line ' . $i);
        }
        $i++;
    }
    // Convert exclude activities rows into associative array with activity title as key and
    // activity type as value
    $temp = array();
    foreach ($excludeactivities as $row) {
        $temp[$row[0]] = $row[1];
    }
    $excludeactivities = $temp;

    $view->output_processing_start();

    foreach ($courserows as $row) {
        $tablefields = array($row[0], $row[1]);
        $ret = backup_restore_course($row[0], $row[1], $excludeactivities);
        if (!$ret[0]) {
            $tablefields[] = $ret[1];
        } else {
            $tablefields[] = 'Done';
        }
        $view->output_processing_row($tablefields);
    }

    $view->output_processing_end();
}



// Check for correct permissions
require_login();
require_capability('moodle/backup:backuptargetimport', context_system::instance());

// Create view
$view = new view_courserollover();
$form = new form_courserollover();
$view->set_form($form);

if (!$formdata = $form->get_data()) {
    $view->output_form();
} else {
    process_form($view, $form);
}
