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
 * Unit tests for the quizaccess_deadman class.
 *
 * @package    quizaccess_deadman
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_deadman;

use mod_quiz\quiz_settings;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/deadman/tests/helper.php');

/**
 * Unit tests for the quizaccess_deadman class.
 *
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \quizaccess_deadman
 */
final class rule_test extends \advanced_testcase {
    /**
     * Data provider.
     *
     * @return array
     */
    public static function provide_settings(): array {
        return [
            [['enable' => true, 'warningtext' => 'foobar']],
            [['enable' => true, 'warningtext' => '']],
            [['enable' => false, 'warningtext' => 'foobar']],
            [['enable' => false, 'warningtext' => '']],
            [['enable' => null, 'warningtext' => 'foobar']],
            [['enable' => null, 'warningtext' => '']],
        ];
    }

    /**
     * Make sure our settings are removed from the DB when a quiz is deleted.
     *
     * @param array $settings settings for our plugin
     *
     * @dataProvider provide_settings
     */
    public function test_delete_settings(array $settings): void {
        global $DB;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Set default warning text (admin setting).
        $message = 'Configured default message.';
        set_config('defaultmessage', $message, 'quizaccess_deadman');

        // Create a course and a quiz.
        $settings = (object)$settings;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_deadman_test_helper::create_test_quiz($course, $settings->enable, $settings->warningtext);
        $data = $DB->get_record('quizaccess_deadman', ['quizid' => $quiz->id]);

        // The value must be in the DB now.
        self::assertEquals($settings->enable ?? '0', $data->enable);
        self::assertEquals($settings->warningtext ?: $message, $data->warningtext);

        // Delete the current course to make sure there is no data.
        delete_course($course, false);

        // Our setting must not be in the DB anymore.
        $data = $DB->get_record('quizaccess_deadman', ['quizid' => $quiz->id]);
        self::assertEmpty($data);
    }

    /**
     * Test that the default warning text from the language file is used, if no text has been set in
     * the admin settings.
     */
    public function test_use_default_text_from_language_file(): void {
        global $DB;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and a quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_deadman_test_helper::create_test_quiz($course, true, '');
        $data = $DB->get_record('quizaccess_deadman', ['quizid' => $quiz->id]);

        // The value must be in the DB now.
        self::assertEquals('1', $data->enable);
        self::assertEquals(get_string('defaultwarningtext', 'quizaccess_deadman'), $data->warningtext);
    }

    /**
     * Make sure the plugin is activated if necessary, and not activated if not needed.
     *
     * @param array $settings settings for our plugin
     *
     * @dataProvider provide_settings
     */
    public function test_plugin_activates_or_not(array $settings): void {
        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and a quiz.
        $settings = (object)$settings;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quizwith = quizaccess_deadman_test_helper::create_test_quiz($course, $settings->enable);

        // The rule should be activated according to the setting.
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $quizobj = quiz_settings::create($quizwith->id);
        $manager = $quizobj->get_access_manager(time());
        $rules = $manager->get_active_rule_names();
        if ($settings->enable) {
            self::assertContains('quizaccess_deadman', $rules);
        } else {
            self::assertNotContains('quizaccess_deadman', $rules);
        }
    }
}
