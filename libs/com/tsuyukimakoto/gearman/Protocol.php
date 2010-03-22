<?php
#import("com.tsuyukimakoto.gearman.ProtocolException");
require_once "ProtocolException.php";
class Protocol {
  static private $COMMANDS = array(
      1=> array('can_do', array('func')),
      2=> array('cant_do', array('func')),
      3=> array('reset_abilities', array()),
      4=> array('pre_sleep', array()),
      6=> array('noop', array()),
      7=> array('submit_job', array('func', 'uniq', 'arg')),
      8=> array('job_created', array('handle')),
      9=> array("grab_job", array()),
      10=> array("no_job", array()),
      11=> array("job_assign", array("handle", "func", "arg")),
      12=> array("work_status", array("handle", "numerator", "denominator")),
      13=> array("work_complete", array("handle", "result")),
      14=> array("work_fail", array("handle")),
      15=> array("get_status", array("handle")),
      16=> array("echo_req", array("text")),
      17=> array("echo_res", array("text")),
      18=> array("submit_job_bg", array("func", "uniq", "arg")),
      19=> array("error", array("err_code", "err_text")),
      20=> array("status_res", array("handle", "known", "running", "numerator", "denominator")),
      21=> array("submit_job_high", array("func", "uniq", "arg")),
      22=> array("set_client_id", array("client_id")),
      23=> array("can_do_timeout", array("func", "timeout")),
      24=> array("all_yours", array()),
  );
  static private $R_COMMANDS = array(
    "can_do"=> array(1, array("func")),
    "cant_do"=> array(2, array("func")),
    "reset_abilities"=> array(3, array()),
    "pre_sleep"=> array(4, array()),
    "noop"=> array(6, array()),
    "submit_job"=> array(7, array("func", "uniq", "arg")),
    "job_created"=> array(8, array("handle")),
    "grab_job"=> array(9, array()),
    "no_job"=> array(10, array()),
    "job_assign"=> array(11, array("handle", "func", "arg")),
    "work_status"=> array(12, array("handle", "numerator", "denominator")),
    "work_complete"=> array(13, array("handle", "result")),
    "work_fail"=> array(14, array("handle")),
    "get_status"=> array(15, array("handle")),
    "echo_req"=> array(16, array("text")),
    "echo_res"=> array(17, array("text")),
    "submit_job_bg"=> array(18, array("func", "uniq", "arg")),
    "error"=> array(19, array("err_code", "err_text")),
    "status_res"=> array(20, array("handle", "known", "running", "numerator", "denominator")),
    "submit_job_high"=> array(21, array("func", "uniq", "arg")),
    "set_client_id"=> array(22, array("client_id")),
    "can_do_timeout"=> array(23, array("func", "timeout")),
    "all_yours"=> array(24, array()),
  );
  static private $regx_txt_command = '/^[\w\n\r]+/';
}














