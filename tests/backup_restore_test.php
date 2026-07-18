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
 * Unit tests for backup and restore of the quizaccess_deadman plugin's data.
 *
 * @package    quizaccess_deadman
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_deadman;

use backup;
use backup_controller;
use restore_controller;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/deadman/tests/helper.php');

/**
 * Unit tests for backup and restore of the quizaccess_deadman quiz setting.
 *
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers     \restore_quizaccess_deadman_subplugin
 * @covers     \backup_quizaccess_deadman_subplugin
 * @covers     \quizaccess_deadman
 */
final class backup_restore_test extends \advanced_testcase {
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
     * Backup and restore a quiz and check whether our setting has been conserved.
     *
     * @param array $settings settings for our plugin
     *
     * @dataProvider provide_settings
     */
    public function test_backup_and_restore(array $settings): void {
        global $DB, $USER;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create a course and a quiz.
        $settings = (object)$settings;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_deadman_test_helper::create_test_quiz($course, $settings->enable, $settings->warningtext);

        // Backup course. By using MODE_IMPORT, we avoid the backup being zipped.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        // Delete the current course to make sure there is no data.
        delete_course($course, false);

        // Our setting must be in the quiz' XML file.
        $xmlfile = $bc->get_plan()->get_basepath() . "/activities/quiz_{$quiz->cmid}/quiz.xml";
        $xml = file_get_contents($xmlfile);
        $matches = [];
        preg_match(
            '#<quizaccess_deadman>\s*<enable>(1|0)</enable>\s*<warningtext>([^<]*)</warningtext>\s*</quizaccess_deadman>#',
            $xml,
            $matches,
        );
        self::assertEquals($settings->enable ?? '0', $matches[1]);
        self::assertEquals($settings->warningtext ?: get_string('defaultwarningtext', 'quizaccess_deadman'), $matches[2]);

        // Create a new course and restore the backup.
        $newcourse = $generator->create_course();
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
            backup::TARGET_NEW_COURSE,
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Fetch the quiz ID.
        $modules = get_fast_modinfo($newcourse->id)->get_instances_of('quiz');
        $quiz = reset($modules);

        // Fetch the setting for the given quiz.
        $data = $DB->get_record('quizaccess_deadman', ['quizid' => $quiz->instance]);
        self::assertEquals($settings->enable ?? '0', $data->enable);
        self::assertEquals($settings->warningtext ?: get_string('defaultwarningtext', 'quizaccess_deadman'), $data->warningtext);
    }

    /**
     * Test that when restoring a quiz that was created before the installation of this plugin,
     * the setting will remain OFF, regardless of the default setting in the admin panel.
     */
    public function test_restoring_old_quiz(): void {
        global $DB, $USER;

        // Login as admin user.
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Set our default.
        set_config('enabledefault', '1', 'quizaccess_deadman');

        // Create a course and a quiz.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $quiz = quizaccess_deadman_test_helper::create_test_quiz($course, null);

        // Backup course. By using MODE_IMPORT, we avoid the backup being zipped.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        // Delete our setting from the backup file.
        $xmlfile = $bc->get_plan()->get_basepath() . "/activities/quiz_{$quiz->cmid}/quiz.xml";
        $xml = file_get_contents($xmlfile);
        $xml = preg_replace(
            '#<subplugin_quizaccess_deadman_quiz>.*</subplugin_quizaccess_deadman_quiz>#s',
            '',
            $xml,
        );
        file_put_contents($xmlfile, $xml);

        // Delete the current course to make sure there is no data.
        delete_course($course, false);

        // Our setting must not be in the quiz' XML file anymore.
        $xml = file_get_contents($xmlfile);
        $matches = [];
        preg_match(
            '#<quizaccess_deadman>\s*<enable>(1|0)</enable>\s*<warningtext>XXXX</warningtext>\s*</quizaccess_deadman>#',
            $xml,
            $matches,
        );
        self::assertEmpty($matches);

        // Create a new course and restore the backup.
        $newcourse = $generator->create_course();
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
            backup::TARGET_NEW_COURSE,
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        // Fetch the quiz ID.
        $modules = get_fast_modinfo($newcourse->id)->get_instances_of('quiz');
        $quiz = reset($modules);

        // There should be no record for this quiz, because it does not have our settings.
        $data = $DB->get_record('quizaccess_deadman', ['quizid' => $quiz->instance]);
        self::assertFalse($data);
    }
}
