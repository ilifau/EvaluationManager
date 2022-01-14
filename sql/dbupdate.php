<#1>
<?php
if (!$ilDB->tableExists("rep_robj_xema_eval")) {
    $fields = array(
        'eval_id' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'eval_semester' => array(
            'type' => 'text',
            'length' => 255
        ),
        'doc_function' => array(
            'type' => 'text',
            'length' => 255
        ),
        'doc_salutation' => array(
            'type' => 'text',
            'length' => 4000
        ),
        'doc_title' => array(
            'type' => 'text',
            'length' => 255
        ),
        'doc_firstname' => array(
            'type' => 'text',
            'length' => 255
        ),
        'doc_lastname' => array(
            'type' => 'text',
            'length' => 255
        ),
        'doc_email' => array(
            'type' => 'text',
            'length' => 255
        ),
        'eval_name' => array(
            'type' => 'text',
            'length' => 255
        ),
        'eval_key' => array(
            'type' => 'text',
            'length' => 255
        ),
        'eval_type' => array(
            'type' => 'text',
            'length' => 255
        ),
        'eval_questionnaire' => array(
            'type' => 'text',
            'length' => 255
        ),
        'eval_remarks' => array(
            'type' => 'text',
            'length' => 4000
        ),
        'em_ref_id' => array(
            'type' => 'integer',
            'length' => 4
        )
    );
    $ilDB->createTable("rep_robj_xema_eval", $fields);
    $ilDB->createSequence("rep_robj_xema_eval");
    $ilDB->addPrimaryKey("rep_robj_xema_eval", array("eval_id"));
}
?>
<#2>
<?php
if (!$ilDB->tableExists("rep_robj_xema_assign")) {
    $fields = array(
        'eval_id' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'ilias_obj' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'type' => array(
            'type' => 'text',
            'length' => 255
        ),
        'lecture_name' => array(
            'type' => 'text',
            'length' => 255
        ),
        'lecturer_name' => array(
            'type' => 'text',
            'length' => 255
        )
    );
    $ilDB->createTable("rep_robj_xema_assign", $fields);
    $ilDB->addPrimaryKey("rep_robj_xema_assign", array("eval_id", "ilias_obj"));
}
?>
<#3>
<?php
if (!$ilDB->tableExists("rep_robj_xema_key")) {
    $fields = array(
        'eval_id' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'keyword' => array(
            'type' => 'text',
            'length' => 255
        ),
        'type' => array(
            'type' => 'text',
            'length' => 255
        )
    );
    $ilDB->createTable("rep_robj_xema_key", $fields);
    $ilDB->addPrimaryKey("rep_robj_xema_key", array("eval_id", "keyword"));
}
?>
<#4>
<?php
if (!$ilDB->tableExists("rep_robj_xema_settings")) {
    $fields = array(
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'root_ref_id' => array(
            'type' => 'integer',
            'length' => 4
        )
    );
    $ilDB->createTable("rep_robj_xema_settings", $fields);
    $ilDB->addPrimaryKey("rep_robj_xema_settings", array("ref_id"));
}
?>
<#5>
<?php
    /**
     * Create the table to mark objects for evaluation
     */
    if (!$ilDB->tableExists('eval_marked_objects')) {
        $ilDB->createTable('eval_marked_objects', array(
            'ref_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true)
        ));
        $ilDB->addPrimaryKey('eval_marked_objects', array('ref_id'));
    }
?>