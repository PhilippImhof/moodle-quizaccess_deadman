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
 * Restore helper functions for the quizaccess_deadman plugin.
 *
 * @package    quizaccess_deadman
 * @category   backup
 * @author     Philipp Imhof
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');

/**
 * Restore helper functions for the quizaccess_deadman plugin.
 *
 * @copyright 2026, Philipp Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizaccess_deadman_subplugin extends restore_mod_quiz_access_subplugin {
    #[\Override]
    protected function define_quiz_subplugin_structure() {
        return [
            new restore_path_element(
                'quizaccess_deadman',
                $this->get_pathfor('/quizaccess_deadman'),
            ),
        ];
    }

    /**
     * Process the restored data for the quizaccess_deadman table.
     *
     * @param stdClass $data Data for quizaccess_deadman retrieved from backup xml.
     */
    public function process_quizaccess_deadman($data) {
        global $DB;

        // Update quizid with new reference.
        $data = (object) $data;
        $data->quizid = $this->get_new_parentid('quiz');

        $DB->insert_record('quizaccess_deadman', $data);
    }
}
