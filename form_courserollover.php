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


class form_courserollover extends moodleform {

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('filepicker', 'rolloverids', 'Roll-over course IDs');
        $mform->addRule('rolloverids', null, 'required');

        $mform->addElement('filepicker', 'excludeactivities', 'Excluded activities');

        $this->add_action_buttons(false, 'Roll-over courses');
    }

}
