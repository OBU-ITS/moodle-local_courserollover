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

require_once("{$CFG->libdir}/formslib.php");


class view_courserollover {

    private $form = null;


    function __construct() {
        $this->setup_page();
    }


    function set_form(moodleform $form) {
        $this->form = $form;
    }


    private function setup_page() {
        global $PAGE;
        $PAGE->set_url('/local/courserollover/courserollover.php');
        $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
        $PAGE->set_pagelayout('standard');
        $PAGE->set_title(get_string('courserollover', 'local_courserollover'));
        $PAGE->set_heading(get_string('courserollover', 'local_courserollover'));
    }

    
    private function op_header() {
        global $OUTPUT;
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('courserollover', 'local_courserollover'));
    }


    private function op_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }


    function output_form() {
        $this->setup_page();
        $this->op_header();
        echo '<p>Roll-over course IDs should be a CSV file where the format of each line is:<br />Old Course ID,New Course ID</p>';
        echo '<p style="margin-bottom: 2em;">Excluded activities should be a CSV file where the format of each line is:<br />"Activity title","Activity type"<br />where activity type is "assignment", "page", "data", etc.</p>';
        $this->form->display();
        $this->op_footer();
    }


    function coursecsv_error($error='') {
        global $CFG;
        $this->setup_page();
        $this->op_header();
        echo '<p>An error was found while checking the course IDs CSV file for validity</p>';
        if (!empty($error)) {
            echo '<p>The error is: ' . htmlspecialchars($error) . '</p>';
        }
        echo '<p><a href="' . $CFG->wwwroot . '/local/courserollover/courserollover.php">Back to course roll-over form</a></p>';
        $this->op_footer();
    }


    function excludeactivitiescsv_error($error='') {
        global $CFG;
        $this->setup_page();
        $this->op_header();
        echo '<p>An error was found while checking the exclude activities CSV file for validity</p>';
        if (!empty($error)) {
            echo '<p>The error is: ' . htmlspecialchars($error) . '</p>';
        }
        echo '<p><a href="' . $CFG->wwwroot . '/local/courserollover/courserollover.php">Back to course roll-over form</a></p>';
        $this->op_footer();
    }


    function output_processing_start() {
        $this->setup_page();
        $this->op_header();
        echo '<table class="generaltable boxaligncenter">';
        echo '<tr class="heading">';
        echo '<th>Old course ID</td><th>New course ID</th><th>Status</th>';
        echo '</tr>';
    }


    function output_processing_row(array $fields) {
        echo '<tr>';
        foreach ($fields as $field) {
            echo '<td>' . s($field) . '</td>';
        }
        echo '</tr>';
    }


    function output_processing_end() {
        global $CFG;
        echo '</table>';
        echo '<p><a href="' . $CFG->wwwroot . '/local/courserollover/courserollover.php">Back to course roll-over form</a></p>';
        $this->op_footer();
    }
 
}
