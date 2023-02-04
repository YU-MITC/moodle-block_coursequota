<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block implementation script for block_coursequota.
 *
 * @package   block_coursequota
 * @copyright (C) 2023 Yamaguchi University <gh-cc@mlex.cc.yamaguchi-u.ac.jp>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Tomoya Saito
 */

/**
 * Main class for block_coursequota.
 *
 * @package   block_coursequota
 * @copyright (C) 2023 Yamaguchi University <gh-cc@mlex.cc.yamaguchi-u.ac.jp>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Tomoya Saito
 */
class block_coursequota extends block_base {
    /**
     * Block initialization function.
     */
    public function init() {
        $this->title = get_string('coursequota', 'block_coursequota');
        $this->set_course();
        $this->set_user();
        $this->set_system_context();
        $this->set_course_context();
    }

    /**
     * This function set couse context.
     */
    public function set_course() {
        global $COURSE;

        $this->course = $COURSE;
    }

    /**
     * This function set user object.
     */
    public function set_user() {
        global $USER;

        $this->user = $USER;
    }

    /**
     * This function set system context.
     */
    private function set_system_context() {
        $this->system_context = context_system::instance();
    }

    /**
     * This function set course context.
     */
    private function set_course_context() {
        $this->course_context = context_course::instance($this->course->id);
    }

    /**
     * This function returns content of block.
     * @return string - HTML content for block.
     */
    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;
        require_once($CFG->dirroot . '/local/coursequota/locallib.php');

        if (!has_capability('moodle/course:manageactivities', $this->course_context)) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $enablequota = (int)get_config('local_coursequota', 'enable_quota');
        $quotaaction = (int)get_config('local_coursequota', 'quota_action');

        $contents = local_coursequota_get_contents();
        $limit = local_coursequota_get_limit();

        $mode = get_string('notworking', 'block_coursequota');

        if ($enablequota == 1) {
            if ($quotaaction == ACTION_PROHIBIT) {
                $mode = get_string('restriction', 'block_coursequota');
            } else {
                $mode = get_string('warningonly', 'block_coursequota');
            }
        }

        $limitmessage = (string)$limit;
        if ($limit == 0 || !local_coursequota_enable()) {
            $limitmessage = get_string('unlimited', 'block_coursequota');
        }

        $this->content = new stdClass;
        $this->content->text = get_string('mode_label', 'block_coursequota') . ':&nbsp;' . $mode;
        $this->content->text .= '<br>';
        $this->content->text .= get_string('numcontents_label', 'block_coursequota') . ':&nbsp;'. (string)$contents;
        $this->content->text .= '<br>';
        $this->content->text .= get_string('limit_label', 'block_coursequota') . ':&nbsp;'. $limitmessage;

        if (empty($this->instance)) {
            return $this->content;
        }

        return $this->content;
    }

    /**
     * This function prevent duplicate creation.
     * @return bool - prevent.
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * This function return applicable formats used to print block.
     */
    public function applicable_formats() {
        return array(
                     'site-index' => false,
                     'course-view' => true,
                     'course-view-social' => false,
                     'mod' => false,
                     'mod-quiz' => false
                    );
    }
}

