<?php

require_once("../../config.php");
require_once("lib.php");

$id   = optional_param('id', 0, PARAM_INT);          // Course module ID
$a    = optional_param('a', 0, PARAM_INT);           // Assignment ID
$mode = optional_param('mode', 'all', PARAM_ALPHA);  // What mode are we in?

if ($id) {
    if (! $cm = get_coursemodule_from_id('basiclti', $id)) {
        error('invalidcoursemodule');
    }

    if (! $basiclti = get_record("basiclti", "id", $cm->instance)) {
        error('invalidid', 'basiclti');
    }

    if (! $course = get_record("course", "id", $basiclti->course)) {
        error('coursemisconf', 'basiclti');
    }
} else {
    if (!$basiclti = get_record("basiclti", "id", $a)) {
        error('invalidcoursemodule');
    }
    if (! $course = get_record("course", "id", $basiclti->course)) {
        error('coursemisconf', 'basiclti');
    }
    if (! $cm = get_coursemodule_from_instance("basiclti", $basiclti->id, $course->id)) {
        error('invalidcoursemodule');
    }
}

require_login($course->id, false, $cm);

require_capability('mod/basiclti:grade', get_context_instance(CONTEXT_MODULE, $cm->id));

basiclti_submissions($cm, $course, $basiclti, $mode);   // Display or process the submissions

?>
