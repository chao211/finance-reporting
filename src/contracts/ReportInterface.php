<?php

namespace finance\contracts;


interface ReportInterface
{

    const REPORT_STATUS_INIT = 0;
    const REPORT_STATUS_SUCCESS = 1;
    const REPORT_STATUS_FAIL = -1;

    public function addOrder($content, $message);

    public static function loadById($id);

    public function reportSuccess($result);


}



