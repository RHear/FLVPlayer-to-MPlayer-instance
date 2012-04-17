<?php
/*
 * FLV to MPlayer converter.
 * 
 * Use this script to convert all FLV player instances in Moodle(1.9) to 
 * convert them to MPlayer.
 * It finds all current FLV instances in all courses in moodle and then you 
 * can convert each video at once to MPlayer instance, by clicking "Convert!".
 * Basically it takes all the FLV instance data and then combines it with 
 * extra MPlayer default data and creates a new MPlayer instance. Then it
 * replaces the FLV instance ID in course_modules table, with the newly created
 * MPlayer ID. The old FLV instance will be deleted. Thats it!
 * 
 * Remember, you are using this at your own risk! If something goes, wrong dont
 * blame it on me. Feel free to make neccessary modification for your own need.
 * 
 * author Tõnis Tartes <tonis.tartes@gmail.com>
 * package Moodle 1.9
 * 
 */

require_once('./config.php');

global $CFG;

require_once(''.$CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/backup/lib.php');

// Admin user logged
require_login();
//Require capability - only admin access
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM, SITEID));

//Function getting all FLV Players
function get_all_flv_players() {
    
    $flv_arr = array();
    
    $flvs = get_records('flv');
    
    $i = 0;
    foreach ($flvs as $key => $value) {
        $cm = get_coursemodule_from_instance('flv', $key);
        $flv_arr[$i]['info'] = $value;
        $flv_arr[$i]['cm_info'] = $cm;
        $i++;
    }
    
    return $flv_arr;
}

//Converter function
function convert_flv_to_mplayer($flv_id, $cm_id, $course_id, $section_id) {
    
    //Old FLV record
    $flv_object = get_record('flv', 'id', $flv_id);
    
    //New elements for MPlayer instance
    $mplayer_new_elements = array(
        'infoboxcolor' => 'FFFFFF', 
        'infoboxposition' => 'none',
        'infoboxsize' => '85',
        'hdbitrate' => '1500',
        'hdfullscreen' => 'true',
        'hdstate' => 'true',
        'livestreamfile' => '',
        'livestreamimage' => '',
        'livestreaminterval' => '15',
        'livestreammessage' => 'Checking for livestream...',
        'livestreamstreamer' => '',
        'livestreamtags' => '',
        'audiodescriptionfile' => '',
        'audiodescriptionstate' => 'true',
        'audiodescriptionvolume' => '90',
        'mplayerstart' => '0',
        'smoothing' => 'true',
        'logoboxalign' => 'left',
        'logoboxfile' => '',
        'logoboxlink' => '',
        'logoboxmargin' => '15',
        'logoboxposition' => 'top',
        'logohide' => 'true',
        'logoposition' => 'bottom-left',
        'captionsback' => 'true',
        'captionsfontsize' => '14',
        'captionsstate' => 'true',
        'metaviewerposition' => '',
        'metaviewersize' => '100',
        'searchbarcolor' => 'CC0000',
        'searchbarlabel' => 'Search',
        'searchbarposition' => 'none',
        'searchbarscript' => '',
        'snapshotbitmap' => 'true',
        'snapshotscript' => 'none',
        'quality' => 'best'
    );
    
    //Elements to rename
    $mplayer_rename_elements = array(
        'flvdate' => 'mplayerdate',
        'flvfile' => 'mplayerfile',
        'flvstart' => 'mplayerstart',
        'flvrepeat' => 'mplayerrepeat',
        'logo' => 'logofile',
        'captions' => 'captionsfile'
    );
    
    //Elements to remove
    $mplayer_remove_elements = array(
        'id', 'link', 'displayclick', 'linktarget', 'abouttext', 'aboutlink', 'client', 'flvid', 'version', 'quality'
    );
    
    //MPlayer instance
    $mplayer_object = new object();
    
    //Generating new MPlayer instance
    foreach ($flv_object as $key => $value) {
        if (array_key_exists($key, $mplayer_rename_elements)) {
            $mplayer_object->$mplayer_rename_elements[$key] = $value;
        } else if (in_array($key, $mplayer_remove_elements)) {
            continue;
        } else {
            $mplayer_object->$key = $value;
        }
    }
    
    foreach ($mplayer_new_elements as $key => $value) {
        $mplayer_object->$key = $value;
    }
    
    //Adding new MPlayer
    $mplayer_id = insert_record('mplayer', $mplayer_object);
    
    $mod_type = get_record('modules', 'name', 'mplayer');
    
    $cm_details = get_coursemodule_from_id('flv', $cm_id);
    
    $cm_details->module = $mod_type->id;
    $cm_details->instance = $mplayer_id;
    $cm_details->modname = $mod_type->name;
    
    //Update course_modules table
    update_record('course_modules', $cm_details);
    
    //Delete old FLV instance
    delete_records('flv', 'id', $flv_id, 'course', $course_id);
    rebuild_course_cache($course_id);
}

//Post functions
$cnv = optional_param('cnv_now', false, PARAM_RAW);

//When Convert is clicked
if (isset($cnv) && $cnv == 'Convert!') {
    
    //Params
    $flv_id = required_param('flv_id', PARAM_RAW);
    $cm_id = required_param('cm_id', PARAM_RAW);
    $course_id = required_param('course_id', PARAM_RAW);
    $section_id = required_param('section_id', PARAM_RAW);
    
    //Actual function doing the magic
    convert_flv_to_mplayer($flv_id, $cm_id, $course_id, $section_id);
    
}


// Header
$string = 'Convert all FLVs to MPlayer instances, neccessary for Moodle 2.x upgrade';
$navigation = build_navigation(array(array('name'=> $string, null)));

print_header($string, $string, $navigation);
print_spacer(20);
//Details
print_box('<div align="center"><strong>FLV to MPlayer conversion!</strong><br />Convert all possible FLV instances to MPlayer instances</div>');

//Get all FLV players in Moodle
$flvs = get_all_flv_players();

//Table
$table = new Object();
$cnt = 0;
$table->head = array('#', 'FLV_ID', 'CM_ID', 'Section_ID', 'Course_ID', 'Course_Shortname', 'Name', 'FLV File', 'Action');

$hidden_vals ='';

//print out all instances
foreach ($flvs as $key => $value) {
    $cnt++; 
    $course_shortname = get_record('course', 'id', $value['info']->course);
    $hidden_vals = '<form method="post" action="">
                    <input type="submit" value="Convert!" name="cnv_now" />
                    <input type="hidden" value="'.$value['info']->id.'" name="flv_id" />
                    <input type="hidden" value="'.$value['cm_info']->id.'" name="cm_id" />
                    <input type="hidden" value="'.$value['info']->course.'" name="course_id" />
                    <input type="hidden" value="'.$value['cm_info']->section.'" name="section_id" />
                    </form>';
    
    $table->data[] = array('<strong>'.$cnt.'</strong>', $value['info']->id, $value['cm_info']->id, $value['cm_info']->section, $value['info']->course, '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$value['info']->course.'" target="_blank">'.$course_shortname->shortname.'</a>', '<a href="'.$CFG->wwwroot.'/mod/flv/view.php?id='.$value['cm_info']->id.'" target="_blank">'.$value['info']->name, $value['info']->flvfile, $hidden_vals);

}

//Output
print_table($table);

?>