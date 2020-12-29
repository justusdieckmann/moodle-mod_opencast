<?php
// This file is part of Moodle - https://moodle.org/
//
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_opencast.
 *
 * @package     mod_opencast
 * @copyright   2020 Tobias Reischmann <tobias.reischmann@wi.uni-muenster.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_opencast\local\paella_player;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$o  = optional_param('o', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('opencast', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('opencast', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($o) {
    $moduleinstance = $DB->get_record('opencast', array('id' => $o), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('opencast', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_opencast'));
}

require_login($course, true, $cm);

$episode = optional_param('e', null, PARAM_ALPHANUMEXT);

$modulecontext = context_module::instance($cm->id);

$event = \mod_opencast\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('opencast', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/opencast/player.php', array('id' => $cm->id));
$PAGE->set_pagelayout("embedded");
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

$paellaplayer = new paella_player();

if ($episode) {
    $paellaplayer->view($episode, $moduleinstance->opencastid);
} else {
    $paellaplayer->view($moduleinstance->opencastid);
}

echo $OUTPUT->footer();