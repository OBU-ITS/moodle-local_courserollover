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
defined('MOODLE_INTERNAL') || die;

if (has_capability('moodle/backup:backuptargetimport', context_system::instance())) {
    $ADMIN->add('courses', new admin_externalpage('local_courserollover', 'Course roll-over', "$CFG->wwwroot/local/courserollover/courserollover.php"));
}
