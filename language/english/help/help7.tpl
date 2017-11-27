<div id="help-template" class="outer">
    <{include file=$smarty.const.CO_EGUIDE_HELP_HEADER}>
    <h4 class="odd">MODULE UPDATES</h4><br>
    <p class="even">
        <a name="update"></a><h5>Module Update</h5>

    <ul>
        <li>When Event Guide module update, You need Module update in Module Manager in admin page.</li>
        <li>Reservation Data format convertion</li>
        <div class="note">In eguide 2.31 later internal data format changing.
            If you need read out past reservation data,
            there is data format convertion program <a href="../../admin/conv230.php">admin/conv230.php</a>. That page
            to show convertion button if there is need
            convert data.
        </div>
    </ul>

    <a name="duplication"></a><h5>Module duplicate</h5>

    <p>eguide 2.4 later module is duplicatable (sorry not yet D3).
        There is command line utility proguram for duplicate module.
        "eguide/duplicate.php" is duplicate eguide module how many you want.
        (This command support only Unix-like system)
    </p>
    <p>Example use this:</p>
    <pre>
    $ <span style="text-decoration: underline;">cd modules/eguide</span>
    $ <span style="text-decoration: underline;">php duplicate.php event ticket class</span>
    Duplicate: event
    Duplicate: ticket
    Duplicate: class
  </pre>

    <a name="changes"></a><h5>Changes</h5>

    <dl>


        <p class="even">
        <dt>2013/10/05 eguide 2.71 released</dt>
        <dd>
            <ul>
                <li>Reservation username display format customizabled (display_username)</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2013/05/30 eguide 2.70 released</dt>
        <dd>
            <ul>
                <li>Not display cancel reservation form when past event time.</li>
                <li>Use &lt;label&gt; tag in checkbox/radio type item.</li>
                <li>Fix to show email field when guest and both accept mode.</li>
                <li>Add option variable (enable_past_register), that allow register to event which past closed date</li>
                <li>Register reservation bind for user account</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2011/09/10 eguide 2.64 released</dt>
        <dd>
            <ul>
                <li>Fix notification email register/cancel not work</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2010/10/10 eguide 2.63 released</dt>
        <dd>
            <ul>
                <li>Fix replace for month/week name by local language string (2.63a)</li>
                <li>Add email confirm options (email_repeat_check)</li>
                <li>Support altsys language manager</li>
                <li>Fix miscalculate in reservation summary</li>
                <li>Fix redirect_header out absolute path (for mobile)</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2010/05/16 eguide 2.62 released</dt>
        <dd>
            <ul>
                <li>Fix version string (2.62a)</li>
                <li>Enable localization for month name in event date</li>
                <li>Fix block function conflict when module duplicated</li>
                <li>Fix Excel(XML) format malfunction over 40 records</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2010/04/04 eguide 2.61 released</dt>
        <dd>
            <ul>
                <li>Show reservation data when cancel confirm</li>
                <li>Add language/portuguese resouces (thx leco1)</li>
                <li>Add select option "Both" for "Event entry need to LOGIN" in preferences</li>
                <li>Remove limitation for excel(XML) format download.</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2010/02/27 eguide 2.6 released</dt>
        <dd>
            <ul>
                <li>add list type select in block options</li>
                <li>change admin.php form to hide some fields</li>
                <li>fix new event notification email tags not set</li>
                <li>option variables "reply_tpl_suffix" added</li>
                <li>redirect make to handle option variables</li>
                <li>add description for "Reservation limit plugins" in help.html</li>
                <li>add blocks for expired event</li>
                <li>add for "dlimit" plugins to limit total reservations (max_register_in_future)</li>
                <li>Reservation button label to change by action</li>
                <li>Allow {X_NAME} replace by user varibale</li>
                <li>add option variable "module_css" for assign module local stylesheet</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2009/12/24 eguide 2.56 released</dt>
        <dd>
            <ul>
                <li>fix EVENT_TITLE mail variable setting</li>
                <li>dlimit plugins support bound_time options</li>
                <li>blocks support bound_time</li>
                <li>add enable_copy options</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2009/12/20 eguide 2.55 released</dt>
        <dd>
            <ul>
                <li>More "Additional item options"
                    <ul>
                        <li>bound_time=01:00 -- Bound time as last day</li>
                        <li>reply_subject=Subject -- Replay email subject</li>
                        <li>from_name=Sender -- Reply from name</li>
                    </ul>
                </li>
                <li>Email subject redefine with email vars.</li>
                <li>Reply email variable EVENT_DATE defined</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2009/10/04 eguide 2.54 released</dt>
        <dd>
            <ul>
                <li>Time Table Labels apply to event date time selection</li>
                <li>FCKeditor (use_fckeditor={Basic|Default}) option added</li>
                <li>fix cancel page for new event mail notification</li>
                <li>fix in additional item at printable comment with comma</li>
                <li>event editing start time accept "HH:MM" format</li>
                <li>reservation control plugins control by option variable</li>
                <li>fix my reservation page ancker mistake</li>
                <li>fix block descption edit style mistake</li>
                <li>fix mutiple date editing template</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2009/05/24 eguide 2.53 released</dt>
        <dd>
            <ul>
                <li>Add context value for XOOPS Search module (thx tes)</li>
                <li>Add description to variable in block template</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2009/03/09 eguide 2.52 released</dt>
        <dd>
            <ul>
                <li>Describe more "REFERER errors" in reservation failer.</li>
                <li>Add detail show reservation. (thx uhouho)</li>
                <li>Fix repeat opening event mistake datetime. (thx uhouho)</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2009/03/09 eguide 2.52 released</dt>
        <dd>
            <ul>
                <li>Describe more "REFERER errors" in reservation failer.</li>
                <li>Add detail show reservation. (thx uhouho)</li>
                <li>Fix repeat opening event mistake datetime. (thx uhouho)</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2008/10/19 eguide 2.51 released</dt>
        <dd>
            <ul>
                <li>Add {RVID} tags in reply mail template (accept.tpl)</li>
                <li>Add CSV output coding system options (export_charset)</li>
                <li>follow altsys 0.6 template/blocks admin</li>
                <li>Revised Admin Interface by Gigamaster</li>
                <li>Fix category image field not stored (thx Gigamaster)</li>
                <li>Fix event active editing not work in admin list (thx Gigamaster)</li>
                <li>Assign category discription in smarty variable</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2008/07/22 eguide 2.5 released</dt>
        <dd>
            <ul>
                <li>Add item options "reply_extension" as switch reply mail template</li>
                <li>Apply "Additional item options" in defaults</li>
                <li>Change preferences "Label of persons" to "Additional item options"</li>
                <li>Not display category select button if there is no categires</li>
                <li>Add no data language resources for admin page</li>
                <li>Fix using template set mistake.</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2008/02/16 eguide 2.42 released</dt>
        <dd>
            <ul>
                <li>fix checkbox failer</li>
                <li>Add addtional field input helper (JavaScript)</li>
                <li>Fix admin menu link</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2008/02/11 eguide 2.41 released</dt>
        <dd>
            <ul>
                <li>Fix conflict with pico module</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2008/02/05 eguide 2.4 released</dt>
        <dd>
            <ul>
                <li>Categories enhancement (two level, sort order, importer, block)</li>
                <li>Fix JavaScript check failer</li>
                <li>Enable confirm page in default</li>
                <li>Add duplicatable function</li>
                <li>Fix excel template (remove hard code kanji)</li>
                <li>Using newer altsys (0.5later) function</li>
                <li>Arrangement of language resource (Separation main/common)</li>
                <li>add resource language/spanish (thx Gerardo)</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2007/12/31 eguide 2.31 released</dt>
        <dd>
            <ul>
                <li>text field quote(') handle bug fixed</li>
                <li>add const type</li>
                <li>variables handle in redirect URL</li>
                <li>multiple category selecting in index.php (e.g. cat=1,2,3)</li>
                <li>fix JavaScript mistake (thx souhalt)</li>
                <li>fix rendering mistake when occurrence errors (thx souhalt)</li>
                <li>Ignore mail sending status except reservation user</li>
                <li>Using altsys module when exists</li>
                <li>Internal Reservation data format changes (refactoring)</li>
                <li>Fix extra date editing failer when after start time (thx Jens)</li>
                <li>add breadcrumbs (xoops_breadcrumbs) smarty variable setting</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2007/03/03 eguide 2.3 released</dt>
        <dd>
            <ul>
                <li>Add setting notifiy to operation user self.</li>
                <li>Add update notification to admin with poster</li>
                <li>fix stop admin mail when disable setting in preference</li>
                <li>fix notification mail for register users</li>
                <li>eguide 2.24 released</li>
                <li>fix compat in PHP5/MySQL5 (mylist.php failer, etc)</li>
                <li>fix complex value-label behavier item in checkbox/radio</li>
                <li>update tchinese resources (thanks jax)</li>
                <li>suppress space(nbsp) when setting value at additional items typed checkbox/radio</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2006/10/14 eguide 2.23 released</dt>
        <dd>
            <ul>
                <li>fix month name index typo in japanese/german/tchinese</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2006/10/14 eguide 2.21 released</dt>
        <dd>
            <ul>
                <li>fix some language resources</li>
                <li>add language/tchinese resources (by jimmy9522 at twpug.net)</li>
                <li>fix poster uname mistake when summary CSV output</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2006/08/29 eguide 2.2 released</dt>
        <dd>
            <ul>
                <li>make display simple uname CSV format output</li>
                <li>add category filter in blocks</li>
                <li>add filling mark in block when show details</li>
                <li>new event block shows next event in multiple</li>
                <li>fix template: not display _MD_RESERV_FORM (as "Reservation Here") when reservation disable</li>
                <li>add language/german resources (from Jens Havelberg)</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2006/08/17 eguide 2.1 released</dt>
        <dd>
            <ul>
                <li>add redirect url setting after reservation</li>
                <li>revised escape in additional items and extent "prop" attribute (from Craig Taylor)</li>
                <li>fix template: not display close time when reservation disable</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dd>2006/08/13 eguide 2.01 released</dd>
        <dd>
            <ul>
                <li>fix mistake resources</li>
                <li>fix cancel failer when login user do cancel</li>
            </ul>
        </dd>
        </p>

        <p class="even">
        <dt>2006/08/03 eguide 2.0 released</dt>
        </p>

        <p class="even">
            <dt><a href="changes.html">Changes before 2.0</a></dt>
        </p>


    </dl>


    <p class="even">
    <hr>
    <address>Nobuhiro Yasutomi &lt;<a href="mailto:nobuhiro.yasutomi@nifty.ne.jp">nobuhiro.yasutomi@nifty.ne.jp</a>&gt;
    </address>
    MySite Users <a href="http://myht.org/">http://myht.org/</a><br>


    </p>
</div>
