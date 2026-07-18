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

use mod_quiz\quiz_settings;
use mod_quiz\local\access_rule_base;

/**
 * Rule definition class for the quizaccess_deadman plugin.
 *
 * @package   quizaccess_deadman
 * @copyright 2026, Philipp Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_deadman extends access_rule_base {
    #[\Override]
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        if (!self::must_activate($quizobj)) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    /**
     * The dead man's switch should be enabled according to the corresponding quiz setting.
     *
     * @param quiz_settings $quizobj the quiz
     * @return bool
     */
    protected static function must_activate(quiz_settings $quizobj): bool {
        // Silently leave if the quiz settings do not enable our plugin.
        $quiz = $quizobj->get_quiz();
        if (empty($quiz->deadman_enable)) {
            return false;
        }

        return true;
    }


    /**
     * If the given string is empty, fetch the default warning text from the admin settings and
     * if that one is also empty, fetch the one from the language file.
     *
     * @param string $warningtext the warning text
     * @return string
     */
    protected static function get_fallback_warning(string $warningtext): string {
        if (empty($warningtext)) {
            $configureddefaultmessage = get_config('quizaccess_deadman', 'defaultmessage');
            $messagefromlanguagefile = get_string('defaultwarningtext', 'quizaccess_deadman');
            $warningtext = $configureddefaultmessage ?: $messagefromlanguagefile;
        }
        return $warningtext;
    }

    #[\Override]
    public function setup_attempt_page($page) {
        // Do not activate the plugin during reviews or on the summary page.
        if ($page->pagetype === 'mod-quiz-review' || $page->pagetype === 'mod-quiz-summary') {
            return;
        }

        // Our work is done client-side, so we just initialize our JS module. The delay is set in the
        // admin settings, the warning text is set in the quiz settings. If the text is missing (which
        // should not happen), we use the one from the admin settings or from the language file.
        $delay = get_config('quizaccess_deadman', 'delay');
        $warningtext = self::get_fallback_warning($this->quiz->deadman_warningtext ?? '');
        $page->requires->js_call_amd(
            'quizaccess_deadman/deadman',
            'init',
            [$delay, $warningtext],
        );
    }

    #[\Override]
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        $element = $mform->createElement('header', 'deadman', get_string('sectiontitle', 'quizaccess_deadman'));
        $mform->addElement($element);

        $element = $mform->createElement(
            'selectyesno',
            'deadman_enable',
            get_string('enable')
        );
        $mform->setDefault('deadman_enable', get_config('quizaccess_deadman', 'enabledefault'));
        $mform->addElement($element);

        $element = $mform->createElement(
            'textarea',
            'deadman_warningtext',
            get_string('message', 'quizaccess_deadman')
        );
        $mform->hideIf('deadman_warningtext', 'deadman_enable', 'yes');
        $mform->setDefault(
            'deadman_warningtext',
            self::get_fallback_warning(''),
        );
        $mform->addElement($element);
    }

    #[\Override]
    public static function save_settings($quiz) {
        global $DB;

        // Check if there are already settings for this quiz. If there are, we update them.
        // Otherwise, we create a new record.
        $record = $DB->get_record('quizaccess_deadman', ['quizid' => $quiz->id]);
        if ($record) {
            $record->enable = $quiz->deadman_enable;
            $record->warningtext = $quiz->deadman_warningtext;
            $DB->update_record('quizaccess_deadman', $record);
        } else {
            // Check the system preferences to see whether our plugin should, by default, be active
            // for a new quiz.
            $defaultsetting = get_config('quizaccess_deadman', 'enabledefault') ?: '0';

            // Use the settings from the form. If they do not exist (which should not happen), then use the
            // corresponding defaults from the system preferences.
            $record = [
                'quizid' => $quiz->id,
                'enable' => $quiz->deadman_enable ?? $defaultsetting,
                'warningtext' => self::get_fallback_warning($quiz->deadman_warningtext ?? ''),
            ];
            $DB->insert_record('quizaccess_deadman', $record);
        }
    }

    #[\Override]
    public static function delete_settings($quiz) {
        global $DB;
        $DB->delete_records('quizaccess_deadman', ['quizid' => $quiz->id]);
    }

    #[\Override]
    public static function get_settings_sql($quizid) {
        return [
            'deadman.enable AS deadman_enable, '
            . 'deadman.warningtext AS deadman_warningtext',
            'LEFT JOIN {quizaccess_deadman} deadman ON deadman.quizid = quiz.id ',
            [],
        ];
    }
}
