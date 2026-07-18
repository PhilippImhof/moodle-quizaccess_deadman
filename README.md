![GitHub Release](https://img.shields.io/github/v/release/PhilippImhof/moodle-quizaccess_deadman)
[![Automated code checks](https://github.com/PhilippImhof/moodle-quizaccess_deadman/actions/workflows/checks.yml/badge.svg)](https://github.com/PhilippImhof/moodle-quizaccess_deadman/actions/workflows/checks.yml) [![Automated testing](https://github.com/PhilippImhof/moodle-quizaccess_deadman/actions/workflows/testing.yml/badge.svg)](https://github.com/PhilippImhof/moodle-quizaccess_deadman/actions/workflows/testing.yml)

moodle-quizaccess_deadman
----------------------------

This is a quiz access rule plugin that acts as a "dead man's switch" for the browser's Javascript interpreter. If the browser stops running JS for whatever reason, Moodle's autosave mechanism will stop working and students might lose some of their answers. This plugin makes sure that students will be notified that autosave is no longer running.


#### Installation

Install the plugin to the folder `$MOODLE_ROOT/mod/quiz/accessrule/deadman`.

For more information, please see the [Moodle docs](https://docs.moodle.org/en/Installing_plugins).


#### Usage

1. Create a quiz.
2. In the settings form, click on "Dead man's switch for Javascript".
3. At the end of the section, set "Enable" to "Yes".