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
 * Global configuration settings for the quizaccess_deadman plugin.
 *
 * @package    quizaccess_deadman
 * @author     Philipp Imhof
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && $ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configcheckbox(
            'quizaccess_deadman/enabledefault',
            new lang_string('enabledefault', 'quizaccess_deadman'),
            new lang_string('enabledefault_desc', 'quizaccess_deadman'),
            '0',
        )
    );

    $duration = new admin_setting_configduration(
        'quizaccess_deadman/delay',
        new lang_string('delay', 'quizaccess_deadman'),
        new lang_string('delay_desc', 'quizaccess_deadman'),
        3 * MINSECS,
        MINSECS,
    );
    $duration->set_max_duration(1800);
    $duration->set_min_duration(60);
    $settings->add($duration);

    $settings->add(
        new admin_setting_configtextarea(
            'quizaccess_deadman/defaultmessage',
            new lang_string('defaultmessage', 'quizaccess_deadman'),
            new lang_string('defaultmessage_desc', 'quizaccess_deadman'),
            get_string('defaultwarningtext', 'quizaccess_deadman'),
            PARAM_RAW,
            60,
            4,
        )
    );
}
