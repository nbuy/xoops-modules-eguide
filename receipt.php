<?php
// Event Receiption for Poster
//

include __DIR__ . '/header.php';
require_once __DIR__ . '/perm.php';

$op   = param('op', 'view');
$rvid = param('rvid');
$eid  = param('eid');
$exid = param('sub');
$myts = MyTextSanitizer::getInstance();
$nlab = eguide_form_options('label_persons');

if ($rvid) {
    if ('view' === $op) {
        $op = 'one';
    }
    $result = $xoopsDB->query('SELECT r.*, optfield FROM ' . RVTBL . ' r, ' . OPTBL . " o WHERE rvid=$rvid AND r.eid=o.eid");
    if (!$result || 0 == $xoopsDB->getRowsNum($result)) {
        redirect_header(EGUIDE_URL . '/', 2, _NOPERM);
    }
    $data    = $xoopsDB->fetchArray($result);
    $eid     = $data['eid'];
    $exid    = $data['exid'];
    $back    = EGUIDE_URL . '/receipt.php?eid=' . $eid . ($exid ? '&sub=' . $exid : '');
    $backanc = "<a href='$back'>" . _MD_RESERV_RETURN . '</a>';
    if ('save' === $op) {
        $status = (int)$_POST['status'];
        $email  = $xoopsDB->quoteString(post_filter($_POST['email']));
        $info   = post_filter($_POST['info']);
        $vals   = unserialize_text($data['info']);
        $num    = (_RVSTAT_REFUSED != $data['status']) ? (isset($vals[$nlab]) ? $vals[$nlab] : 1) : 0;
        $xoopsDB->query('UPDATE ' . RVTBL . " SET email=$email, status=$status," . 'info=' . $xoopsDB->quoteString($info) . " WHERE rvid=$rvid");
        $vals = unserialize_text($info);
        $nnum = (_RVSTAT_REFUSED != $status) ? (isset($vals[$nlab]) ? $vals[$nlab] : 1) : 0;
        update_reserv($eid, $exid, $nnum - $num);
        redirect_header($back, 2, _MD_DBUPDATED);
    } else {
        $result = $xoopsDB->query('SELECT * FROM ' . RVTBL . " WHERE rvid=$rvid");
        $rvdata = $xoopsDB->fetchArray($result);
    }
}

$result = $xoopsDB->query('SELECT * FROM ' . OPTBL . " WHERE eid=$eid");
$opts   = $xoopsDB->fetchArray($result);

$result = $xoopsDB->query('SELECT IF(exdate,exdate,edate) edate, title, uid,
summary, cdate, counter, style, topicid FROM ' . EGTBL . ' e LEFT JOIN ' . EXTBL . " x
ON eid=eidref AND exid=$exid WHERE eid=$eid");
$head   = $xoopsDB->fetchArray($result);
$edate  = $head['edate'];
if ($exid) {
    $extents = [];
    $result  = $xoopsDB->query('SELECT exdate FROM ' . EXTBL . " WHERE exid=$exid");
    list($edate) = $xoopsDB->fetchRow($result);
} else {
    $extents = get_extents($eid, true);
}

$title  = eventdate($edate) . ' ' . htmlspecialchars($head['title']);
$poster = new XoopsUser($head['uid']);

if (empty($op)) {
    $op = 'view';
}
$print = 'print' === $op;

// make optional field and countable list.
if ($eid) {
    $result = $xoopsDB->query('SELECT optfield,optvars FROM ' . OPTBL . " WHERE eid=$eid");
    $opts   = $xoopsDB->fetchArray($result);
    eguide_form_options(unserialize_vars($opts['optvars']));
    $item = [];
    foreach (explode("\n", preg_replace('/\r/', '', $opts['optfield'])) as $ln) {
        // comment line
        if (preg_match('/^\s*#/', $ln) || preg_match('/^\s*$/', $ln)) {
            continue;
        }
        $fld  = explode(',', $ln);
        $lab  = preg_replace('/^!\s*/', '', preg_replace('/[\*#]$/', '', array_shift($fld)));
        $type = isset($fld[0]) ? strtolower($fld[0]) : false;
        if ('checkbox' === $type || 'radio' === $type || 'select' === $type) {
            $mc[$lab] = $type;    // it's countable
        }
        $item[] = $lab;
    }
}

$result = $xoopsDB->query('SELECT IF(x.reserved,x.reserved,o.reserved) FROM ' . EGTBL . ' e LEFT JOIN ' . OPTBL . ' o ON e.eid=o.eid LEFT JOIN ' . EXTBL . " x ON e.eid=eidref AND x.exid=$exid WHERE e.eid=$eid");
list($nrsv) = $xoopsDB->fetchRow($result);
$result = $xoopsDB->query('SELECT * FROM ' . RVTBL . " WHERE eid=$eid AND exid=$exid ORDER BY rvid");
$nrec   = $xoopsDB->getRowsNum($result);

// output records in CSV format
if ($nrec && 'csv' === $op) {
    $outs = [];
    if ($xoopsModuleConfig['export_field']) {
        $temp = $item;
        array_unshift($temp, _MD_ORDER_DATE, _MD_EMAIL, _MD_RVID, _MD_UNAME);
        $exports = preg_split('/\\s*,\\s*/', $xoopsModuleConfig['export_field']);
        foreach ($exports as $k) {
            if ('*' === $k) {    // left all
                foreach ($temp as $v) {
                    if (!isset($outs[$v])) {
                        $outs[$v] = true;
                    }
                }
                break;
            } elseif (preg_match('/^\\d+$/', $k)) {    // present position
                if (isset($temp[$k])) {
                    $outs[$temp[$k]] = true;
                }
            } elseif ('' == $k || in_array($k, $temp)) { // present name
                $outs[$k] = true;
            }
        }
    }
    $outs = array_keys($outs);
    $temp = [];
    foreach ($outs as $v) {
        $temp[] = '"' . preg_replace('/"/', '""', $v) . '"';
    }
    $out = implode(',', $temp) . "\n";
    // body
    while ($a = $xoopsDB->fetchArray($result)) {
        $row                 = unserialize_text($a['info']);
        $row[_MD_EMAIL]      = $a['email'];
        $row[_MD_ORDER_DATE] = formatTimestamp($a['rdate']);
        $row[_MD_UNAME]      = display_username($a['uid']);
        $row[_MD_RVID]       = $a['rvid'];
        $temp                = [];
        foreach ($outs as $k) {
            $v      = $row[$k];
            $temp[] = '"' . preg_replace('/\"/', '""', $v) . '"';
        }
        $out .= implode(',', $temp) . "\n";
    }

    $file    = 'eguide_' . formatTimestamp(time(), 'Ymd') . '.csv';
    $charset = eguide_form_options('export_charset', _MD_EXPORT_CHARSET);
    header('Content-Type: text/plain; Charset=' . $charset);
    header('Content-Disposition:attachment;filename="' . $file . '"');
    if (_CHARSET != $charset) {
        if (function_exists('mb_convert_encoding')) {
            $out = mb_convert_encoding($out, $charset, _CHARSET);
        } elseif (function_exists('iconv')) {
            $out = iconv(_CHARSET, $charset, $out);
        }
    }
    echo $out;
    exit;
}

include XOOPS_ROOT_PATH . '/header.php';
assign_module_css();

if (count($extents) > 1) {
    $xoopsTpl->assign('extents', $extents);
}
$xoopsTpl->assign([
                      'title' => $title,
                      'eid'   => $eid,
                      'exid'  => $exid
                  ]);
$paths                   = [];
$paths[$title]           = "event.php?eid=$eid" . ($exid ? "&sub=$exid" : '');
$paths[_MD_RESERV_ADMIN] = "receipt.php?eid=$eid" . ($exid ? "&sub=$exid" : '');
set_eguide_breadcrumbs($head['topicid'], $paths);

$evurl = EGUIDE_URL . "/event.php?eid=$eid" . ($exid ? "&sub=$exid" : '');
switch ($op) {
    case 'active':
        $result = $xoopsDB->query('SELECT optfield FROM ' . OPTBL . ' WHERE eid=' . $eid);
        list($optfield) = $xoopsDB->fetchRow($result);
        $labs  = explodeopts($optfield);
        $isnum = in_array($nlab, $labs);
        $cnt   = 0;
        echo "<p><a href='$evurl' class='evhead'>$title</a></p>\n";
        foreach ($_POST['act'] as $i) {
            $rvid   = (int)$i;
            $yesno  = param('yesno');
            $result = $xoopsDB->query('SELECT * FROM ' . RVTBL . " WHERE rvid=$rvid AND status=" . _RVSTAT_ORDER);
            $data   = $xoopsDB->fetchArray($result);
            if ($data) {
                $msg         = param('msg', '');
                $xoopsMailer = xoops_getMailer();
                $xoopsMailer->useMail();
                $xoopsMailer->setSubject('Re: ' . $title);
                $xoopsMailer->setBody($msg);
                $xoopsMailer->setFromEmail($poster->email());
                if ($data['uid']) {
                    $user  = new XoopsUser($data['uid']);
                    $uinfo = sprintf("%s: %s (%s)\n", _MD_UNAME, $user->getVar('uname'), $user->getVar('name'));
                    $xoopsMailer->setToUsers($user);
                } else {
                    $xoopsMailer->setToEmails($data['email']);
                    $uinfo = sprintf("%s: %s\n", _MD_EMAIL, $email);
                }
                $xoopsMailer->assign('REQ_UNAME', $xoopsUser->getVar('uname'));
                $xoopsMailer->assign('REQ_NAME', $xoopsUser->getVar('name'));
                $xoopsMailer->setFromName(eguide_from_name());
                $xoopsMailer->assign('INFO', $uinfo . $data['info']);
                $curl = EGUIDE_URL . "/reserv.php?op=cancel&rvid=$rvid&key=" . $data['confirm'];
                $xoopsMailer->assign('RVID', $rvid);
                $xoopsMailer->assign('CANCEL_URL', $curl);
                if (_RVSTAT_RESERVED == $yesno) {
                    $ret = _MD_RESERV_ACTIVE;
                } else {
                    $ret = _MD_RESERV_REFUSE;
                    if ($isnum) {
                        $vals = unserialize_text($data['info']);
                        $num  = (int)$vals[$nlab];
                        if ($num < 1) {
                            $num = 1;
                        }
                        $cnt -= $num;
                    } else {
                        $cnt--;
                    }
                    if ($xoopsModuleConfig['use_plugins']) {
                        require_once __DIR__ . '/plugins.php';
                        foreach ($hooked_function['cancel'] as $func) {
                            if (!$func($data['eid'], $data['exid'], $data['uid'], $head['uid'])) {
                                echo 'Cancel failed';
                            }
                        }
                    }
                }
                $xoopsMailer->assign('RESULT', $ret);
                if ($xoopsMailer->send()) {
                    $xoopsDB->query('UPDATE ' . RVTBL . " SET status='$yesno' WHERE rvid=$rvid");
                    echo '<div>' . _MD_INFO_MAILOK . "</div>\n";
                    echo $xoopsMailer->getSuccess();
                } else {
                    echo '<div>' . _MD_INFO_MAILNG . "</div>\n";
                    echo $xoopsMailer->getErrors();
                }
                if ($data['uid']) {
                    echo $user->getVar('uname');
                }
            }
        }
        update_reserv($eid, $exid, $cnt);
        break;

    case 'edit':
        echo "<h3 class='page-header'>" . _MD_RESERV_EDIT . '</h3>';
        echo "<form action='receipt.php' method='post' class='form-inline'>\n";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<input type='hidden' name='op' value='save'>\n";
        echo "<input type='hidden' name='rvid' value='$rvid'>\n";
        echo "<input type='hidden' name='eid' value='$eid'>\n";
        echo "<table class='outer table table-bordered table-striped'>\n";
        echo "<tr><th align='left'>" . _MD_RVID . "</th><td class='even'>$rvid</td></tr>\n";
        echo "<tr><th align='left'>" . _MD_ORDER_DATE . "</th><td class='odd'>" . formatTimestamp($data['rdate'], _MD_TIME_FMT) . "</td></tr>\n";
        echo "<tr><th align='left'>" . _MD_EMAIL . "</th><td class='even'><input type='text' size='40' name='email' value='" . $data['email'] . "' class='form-control'></td></tr>\n";
        echo "<tr><th align='left'>" . _MD_STATUS . "</th><td class='odd'>\n";
        $s = $data['status'];
        echo "<select name='status' class='form-control'>\n";
        foreach ($rv_stats as $i => $v) {
            $ck = ($i == $s) ? ' selected' : '';
            echo "<option value='$i'$ck>$v</option>\n";
        }
        echo "</select></td></tr>\n";
        echo "<tr><th align='left'>" . _MD_RESERV_ITEM . "</th><td class='even'>\n";
        echo "<textarea name='info' cols='40' rows='5' class='form-control'>" . htmlspecialchars($data['info']) . "</textarea>\n";
        echo "</td></tr>\n";
        echo "<tr><th></th><td class='odd'><input type='submit' value='" . _MD_SAVECHANGE . "' class='btn btn-primary'></td></tr>\n";
        echo "</table>\n</form>\n";
        echo "<p align='center'>$backanc</p>\n";
        break;

    case 'one':

        $GLOBALS['xoopsOption']['template_main'] = EGPREFIX . '_confirm.tpl';
        $xoopsTpl->assign('lang_title', _MD_RESERV_REC);
        $edit = "<a href='receipt.php?op=edit&rvid=$rvid' class='btn btn-primary btn-xs' role='button'>" . _EDIT . '</a>';
        $del  = "<a href='reserv.php?op=cancel&rvid=$rvid&back=' class='btn btn-danger btn-xs' role='button'>" . _MD_RESERV_DEL . '</a>';

        $items   = [];
        $items[] = ['label' => _MD_RVID, 'value' => "$rvid &nbsp; $edit &nbsp; $del"];
        if ($data['email']) {
            $items[] = ['label' => _MD_EMAIL, 'value' => $myts->displayTarea($data['email'])];
        }

        if ($data['uid']) {
            $items[] = ['label' => _MD_UNAME, 'value' => display_username($data['uid'], true)];
        }
        if ($rvdata['operator']) {
            $items[] = [
                'label' => _MD_RESERV_REGISTER,
                'value' => XoopsUserUtility::getUnameFromId($rvdata['operator'])
            ];
        }
        $items[] = ['label' => _MD_STATUS, 'value' => $rv_stats[$data['status']]];
        $items[] = ['label' => _MD_ORDER_DATE, 'value' => formatTimestamp($data['rdate'], _MD_TIME_FMT)];
        foreach (unserialize_text($rvdata['info']) as $lab => $v) {
            $items[] = ['label' => $lab, 'value' => $myts->displayTarea($v)];
        }
        edit_eventdata($head);
        $xoopsTpl->assign('event', edit_eventdata($head));
        $xoopsTpl->assign('items', $items);
        $xoopsTpl->assign('submit', $backanc);
        break;

    default:
        $GLOBALS['xoopsOption']['template_main'] = EGPREFIX . '_receipt.tpl';

        $xoopsTpl->assign('lang_title', _MD_RESERVATION);

        $status   = 0;
        $pat      = $rep = [];
        $pat[]    = '{TITLE}';
        $rep[]    = $title;
        $pat[]    = '{EVENT_URL}';
        $rep[]    = EGUIDE_URL . "/event.php?eid=$eid" . ($exid ? "&sub=$exid" : '');
        $pat[]    = '{REQ_UNAME}';
        $rep[]    = $xoopsUser->getVar('uname');
        $template = file_get_contents(template_dir('confirm.tpl') . '/confirm.tpl');
        $mailmsg  = htmlspecialchars(str_replace($pat, $rep, $template));
        $max      = $xoopsModuleConfig['max_item'];
        $xoopsTpl->assign([
                              'order_count' => $nrec,
                              'reserv_num'  => sprintf(_MD_RESERV_REG, $nrsv),
                              'print_date'  => formatTimestamp(time(), _MD_POSTED_FMT),
                              'labels'      => array_merge([$xoopsModuleConfig['member_only'] ? _MD_UNAME : _MD_EMAIL], array_slice($item, 0, $max)),
                              'reserv_msg'  => $mailmsg,
                              'operations'  => [
                                  _RVSTAT_RESERVED => _MD_ACTIVATE,
                                  _RVSTAT_REFUSED  => _MD_REFUSE
                              ],
                          ]);

        $citem   = $list = $nitem = [];
        $confirm = 0;
        while ($order = $xoopsDB->fetchArray($result)) {
            $order['confirm'] = $cf = (_RVSTAT_ORDER == $order['status']);
            if ($cf) {
                ++$confirm;
            }
            $order['date'] = formatTimestamp($order['rdate'], _MD_TIME_FMT);
            $order['stat'] = $rv_stats[$order['status']];
            $add           = [];
            $ok            = _RVSTAT_RESERVED == $order['status'];
            foreach (unserialize_text($order['info']) as $lab => $v) {
                if ($ok) {
                    if (isset($nitem[$lab])) {
                        if ('' !== $nitem[$lab]) {
                            if (preg_match('/^-?\d+$/', $v)) {
                                $nitem[$lab] += $v;
                            } else {
                                $nitem[$lab] = ''; // include not numeric
                            }
                        }
                    } else {
                        $nitem[$lab] = preg_match('/^-?\d+$/', $v) ? $v : '';
                    }
                    if ($v && isset($mc[$lab])) {
                        $mv = ('checkbox' === $mc[$lab]) ? explode(',', $v) : [$v];
                        foreach ($mv as $i) {
                            if (empty($i)) {
                                continue;
                            }
                            $x = "$lab/$i";
                            if (isset($citem[$x])) {
                                $citem[$x]++;
                            } else {
                                $citem[$x] = 1;
                            }
                        }
                    }
                }
                if (count($add) < $max) {
                    $add[] = htmlspecialchars($v);
                }
            }
            $order['add'] = $add;
            if ($order['uid']) {
                $order['uname'] = display_username($order['uid']);
            }
            $list[] = $order;
        }
        $sl = '(' . _MD_SUM . ')';
        foreach ($nitem as $k => $v) {
            foreach (preg_grep('/^' . preg_quote($k . '/', '/') . '\d+$/', array_keys($citem)) as $ki) {
                unset($citem[$ki]);
            }
            if ('' !== $v) {
                $citem[$k . $sl] = $v;
            }
        }
        $xoopsTpl->assign('list', $list);
        $xoopsTpl->assign('confirm', $confirm);
        ksort($citem);
        $xoopsTpl->assign('citem', $citem);
}

if ($print) {
    $xoopsTpl->display('db:' . EGPREFIX . '_receipt_print.tpl');
} else {
    include XOOPS_ROOT_PATH . '/footer.php';
}

/**
 * @param $eid
 * @param $exid
 * @param $num
 */
function update_reserv($eid, $exid, $num)
{
    global $xoopsDB;
    if (0 == $num) {
        return;
    }
    if ($exid) {
        $xoopsDB->query('UPDATE ' . EXTBL . " SET reserved=reserved+($num) WHERE exid=$exid");
    } else {
        $xoopsDB->query('UPDATE ' . OPTBL . " SET reserved=reserved+($num) WHERE eid=$eid");
    }
}
