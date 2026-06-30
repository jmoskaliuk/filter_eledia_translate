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

use filter_translations\translation;

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl',
    (new moodle_url('/filter/translations/managetranslations.php'))->out(false), PARAM_URL);

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('filter/translations:edittranslations', $context);

$translation = new translation($id);
$translation->set('translationsource', translation::SOURCE_MANUAL);
$translation->update();

redirect(new moodle_url($returnurl));
