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
 * Script to implement the Dead man's switch for Javascript.
 *
 * @module     quizaccess_deadman/deadman
 * @copyright  2026, Philipp Imhof
 * @author     Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Delay (in seconds) until the warning message should be shown
 */
var delay;

/**
 * Initialisation.
 *
 * @param {int} interval time (in seconds) until the warning message should be shown
 * @param {string} warning text for the warning message
 */
export const init = (interval, warning) => {
    delay = interval;
    addWarning(warning);
    heartbeat();
};

/**
 * Inject the <div> that contains the warning message.
 *
 * @param {string} warning text for the warning message
 */
const addWarning = (warning) => {
    let div = document.createElement('div');
    div.id = 'quizaccess_deadman_warning';
    div.role = 'alert';
    div.innerText = warning;
    document.body.appendChild(div);
};

/**
 * Function to be run periodically. This function will reset the warning <div>'s transition and
 * make sure the warning does not appear -- unless the function is not invoked anymore.
 *
 * @returns void
 */
const heartbeat = () => {
    // Fetch the div. If it does not exist, we leave.
    const div = document.getElementById('quizaccess_deadman_warning');
    if (div === null) {
        return;
    }

    // Reset the transition and opacity of the warning div.
    div.style.transition = 'none';
    div.style.opacity = '0';

    // Avoid the browser's batch optimisation by forcing a "reflow".
    void div.offsetHeight;

    // Set the transition and target opacity.
    div.style.transition = `opacity ${delay}s steps(1, end)`;
    div.style.opacity = '1';

    // Make sure this function is called again after half of the delay is passed.
    // The delay is set in seconds, so we multiply by 500 (ms/s).
    setTimeout(heartbeat, delay * 500);
};

export default {init};