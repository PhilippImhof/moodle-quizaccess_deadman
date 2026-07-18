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

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * Behat quizaccess_deadman related steps and selector definitions.
 *
 * @package    quizaccess_deadman
 * @category   test
 * @copyright  2026, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_quizaccess_deadman extends behat_base {
    use behat_session_trait;

    /**
     * Return the list of exact named selectors.
     *
     * @return array
     */
    public static function get_exact_named_selectors(): array {
        return [
            new behat_component_named_selector(
                'warning',
                ["//div[@id='quizaccess_deadman_warning']"],
            ),
        ];
    }

    /**
     * Create a global SafeExamBrowser object to simulate an SEB instance. Also, save the version
     * string to the local storage where the client's Javascript can then recreate the SafeExamBrowser
     * object once the next page is loaded. When using the special string "no SEB" as the version, the
     * Javascript will know that it should not create the global object and thus not simulate SEB.
     *
     * @Given /^I stop Javascript timers for the deadman quizaccess plugin$/
     *
     */
    public function i_stop_javascript_timers_for_quizaccess_deadman_plugin(): void {
        $this->execute_script(
            "window.setTimeout = () => {}"
        );
        $this->execute_script(
            "window.setInterval = () => {}"
        );
    }
}
