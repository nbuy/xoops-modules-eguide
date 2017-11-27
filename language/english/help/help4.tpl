<style>
    .note {
        color: #800000;
    }
</style>
<div id="help-template" class="outer">
    <{include file=$smarty.const.CO_EGUIDE_HELP_HEADER}>
    <h4 class="odd">EVENT CONFIGURATION</h4><br>
    <p class="even">

    <h4>Event Guide module setup items as follow:<h4><br>

    <blockquote>
        <table border="0" cellspacing="1" cellpadding="4" bgcolor="#808080">
            <tr class="hd">
                <th>Item</th>
                <th>value</th>
                <th>description</th>
            </tr>
            <tr class="even">
                <th align="left">Group of Event Post</th>
                <td>Group (<span style="text-decoration: underline;">Regsiter Users</span>)</td>
                <td>Set a group the owner permited to administration for own evnet.</td>
            </tr>
            <tr class="odd">
                <th align="left">Notify mail to Admin</th>
                <td>No/<span style="text-decoration: underline;">Yes</span>/Always</td>
                <td>Notification mail to admin when Event registerd or updated.
                    No = No notifcation.
                    Yes = Notify except operation user.
                    Always = Notify with operation user.
                </td>
            </tr>
            <tr class="even">
                <th align="left">Admin Group for Notification</th>
                <td><span style="text-decoration: underline;">Yes</span>/No</td>
                <td>The group is received admin notification mail</td>
            </tr>
            <tr class="odd">
                <th align="left">Do you need to approve New Event</th>
                <td>Yes/<span style="text-decoration: underline;">No</span></td>
                <td>Need to approve New Event by site administrator</td>
            </tr>
            <tr class="even">
                <th align="left">Display additional items in list</th>
                <td>field counts (<span style="text-decoration: underline;">3</span>)</td>
                <td>Display items entry order additional form</td>
            </tr>
            <tr class="odd">
                <th align="left">Display list items in a page</th>
                <td>line counts (<span style="text-decoration: underline;">50</span>)</td>
                <td>How many item lines in a page</td>
            </tr>
            <tr class="even">
                <th align="left">Display events in top page</th>
                <td>Event counts (<span style="text-decoration: underline;">10</span>)</td>
                <td>Number of listed events in top page</td>
            </tr>
            <tr class="odd">
                <th align="left">Show Multiple Entry</th>
                <td><span style="text-decoration: underline;">Yes</span>/No</td>
                <td>When event has multiple entry, show each entris. YES - display each entries. NO - Only show recenet
                    entry.
                </td>
            </tr>
            <tr class="even">
                <th align="left">User requested notification of new event</th>
                <td><span style="text-decoration: underline;">Yes</span>/No</td>
                <td>YES - Enable notification mail, NO - disable.</td>
            </tr>
            <tr class="odd">
                <th align="left">Event entry need to LOGIN</th>
                <td><span style="text-decoration: underline;">No</span>/Yes/Both</td>
                <td>Only login user can be reservation event (Not use email address).
                    When select "Both", email address input field exist only guest user.
                </td>
            </tr>
            <tr class="even">
                <th align="left">Has confirm page</th>
                <td><span style="text-decoration: underline;">Yes</span>/No</td>
                <td>Display confirm page when reservation submit</td>
            </tr>
            <tr class="odd">
                <th align="left">Additional item options</th>
                <td>(none)</td>
                <td>Additional item optional settings.
                    <ul>
                        <li>label_persons=label (label for multiple person reservation)</li>
                        <li>reply_tpl_suffix=suffix (suffix of reply mail template filename)</li>
                        <li>reply_extension=label (label for reply mail template extension)</li>
                        <li>export_charset=UTF-8 (using CSV output coding system)</li>
                        <li>size=60 (text input default size)</li>
                        <li>cols=40 (textarea default columns)</li>
                        <li>rows=5 (textarea default rows)</li>
                    </ul>
                    See "<a href="#form_options">Additional item options</a>" more details.

                </td>
            </tr>
            <tr class="even">
                <th align="left">Close Time Before (min)</th>
                <td style="text-decoration: underline;">60</td>
                <td>Event entry close time before setting minits.</td>
            </tr>
            <tr class="odd">
                <th align="left">Expire Time</th>
                <td><span style="text-decoration: underline;">1440</span> (was 1day, 24 hours)</td>
                <td>Event expired on top page when after event start time in minites.</td>
            </tr>
            <tr class="even">
                <th align="left">Persons default value</th>
                <td style="text-decoration: underline;">10</td>
                <td>Reservation persons in event post form</td>
            </tr>
            <tr class="odd">
                <th align="left">Date Foramt</th>
                <td style="text-decoration: underline;">D, d M Y</td>
                <td>Open Event Date(Time) display format. Using PHP date function format.</td>
            </tr>
            <tr class="even">
                <th align="left">Use Other moudle plugins</th>
                <td>Yes/<span style="text-decoration: underline;">No</span></td>
                <td>Internal Control accept entry form other modules</td>
            </tr>
            <tr class="odd">
                <th align="left">Current entry level mark</th>
                <td>0,[Close]<br>50,[Empty]<br>100,[Many]<br>101,[Full]<br></td>
                <td>The mark mean of how many entry in current. Show mark correspond parcentage. (xx,yy mean less than xx%
                    showup yy. And '0,yy' mean out of date mark)
                </td>
            </tr>
            <tr class="even">
                <th align="left">Time Table Labels</th>
                <td>(none)</td>
                <td>Set starting time in Settings collection page. e.g.: 08:00,14:00,16:00</td>
            </tr>
            <tr class="odd">
                <th align="left">Item List in export reservations</th>
                <td style="text-decoration: underline;">*</td>
                <td>Item `name' or `number' seperated comma(,). Astarisk(*) mean left items. e.g.: 3,4,0,2,*</td>
            </tr>
            <tr class="even">
                <th align="left">Allow Comments</th>
                <td><span style="text-decoration: underline;">Yes</span>/No</td>
                <td>Allow Comments for event<p>Following sets standard comment function in XOOPS</p></td>
            </tr>

        </table>
    </blockquote>
    </p>


    <p class="odd">
    <h4>Additional item options:</h4><br>
    </p>

    <p class="even">

    <h5><dt>label_persons (label for multiple person reservation)</dt></h5><br>
    <dd>This option setting for a reservation accept multiple persons.
        Set item name(label) to input how many.
        <p class="note">For example setting as
            "<span style="font-family:'Lucida Console', monospace">label_persons=Persons</span>". In additional item like as
            "Persons,select,1,2,3,4" that make to recommand numeric choice field.
        </p>
        Only value setting in this config, Assume set for this "label_persons=" option.
        This is compatible before eguide 2.5.
    </dd><br>

    </p>


    <dl>
        <p class="even">
        <h5><dt>label_persons (label for multiple person reservation)</dt></h5><br>
        <dd>This option setting for a reservation accept multiple persons.
            Set item name(label) to input how many.
            <p class="note">For example setting as
                "<span style="font-family:'Lucida Console', monospace">label_persons=Persons</span>". In additional item like as
                "Persons,select,1,2,3,4" that make to recommand numeric choice field.
            </p>
            Only value setting in this config, Assume set for this "label_persons=" option.
            This is compatible before eguide 2.5.
        </dd>

        </p>

        <p class="even">
        <h5><dt>reply_tpl_suffix (suffix of reply mail template filename)</dt></h5><br>
        <dd>
            Use for alternative reply mail template. Using filename added setting value for auto reply email. For example:
            <pre>reply_tpl_suffix=A</pre>
            mean using replay template
            <span style="font-family:'Lucida Console', monospace">language/&lt;lang&gt;/mail_template/{accept|order}<span style="font-weight: bold;">A</span>.tpl</span>
            file. "reply_extension" for indirect by addition items value. In this variable set suffix to direct.
        </dd>
        </p>

        <p class="even">
                <h5><dt>reply_extension (label for reply mail template extension)</dt></h5><br>
        <dd>
            This option using for switch reply mail tamplate.


            <div class="note">This option setting item name(label) for template file name extension.
                <p>For example, setting in preferences "<span style="font-family:'Lucida Console', monospace">reply_extension=OrderForm</span>", In Additional Items include like "OrderForm,hidden,A" field. This event reservation switch reply mail template file.</p>
                <p>In this case(the value as "A") using template file name assume <span style="font-family:'Lucida Console', monospace">language/&lt;lang&gt;/mail_template/{accept|order}<span style="font-weight: bold;">A</span>.tpl</span> and using it.</p>
                <p>Additional value for file name, that is store value in reservation. In international using, recommand the value make alpha-numeric only. Because sometime there is encoding problem.<br>If there is no mail template in additional name, using default mail template.</p>
            </div>
        </dd>
        </p>

        <p class="even">
        <h5><dt>export_charset (using CSV output coding system)</dt></h5><br>
        <dd>Default value as export_charset=UTF-8 (that is defined _MD_EXPORT_CHARSET resource)</dd>
        </p>

        <p class="even">
        <dt>size (text input default size)</dt>
        <dd>Default value as size=60</dd>
        </p>

        <p class="even">
        <dt>cols, rows (textarea default columns/rows)</dt><br>
        <dd>Default value as cols=40, rows=5</dd>
        </p>

        <p class="even">
         <dt><h5>eguide_plugins (set active reservation plugins)</h5></dt><br>
        <dd>
            Set reservation control plugins name list.
            List name with comma(",") when multiple plugin use.
            When this option set disable module name plugins activate.
            You want module name plugin activate, include the name in this list.
        </dd>
        </p>

        <p class="even">
           <h5><dt>use_fckeditor (use FCKeditor edit event description)</dt></h5><br>
        <dd>
            When this option setting, FCKeditor (HTML WYSIWYG editor) using in event edit page.
            You need install FCKeditor at XOOPS_URL/common/fckeditor/ folder.
            When setting value to "Basic", you can use simple toolbar.
        </dd>    </p>

        <p class="even">
         <h5><dt>bound_time (Bundary time as the last day)</dt></h5><br>
        <dd>
            The event start before stting this, display the day before date and more 24 hours (or 12 hours).
            Setting time format as "<span style="font-family:'Lucida Console', monospace">HH:MM</span>".
            <p>For example, setting "<span style="font-family:'Lucida Console', monospace">bound_time=03:00</span>", a event start "2009-12-15 01:00" to be display "2009-12-14 25:00". The bound time work correct from <span style="font-family:'Lucida Console', monospace">00:01</span> to <span style="font-family:'Lucida Console', monospace">11:59</span>.</p>
        </dd>    </p>

        <p class="even">
        <h5> <dt>reply_subject (Reply mail subject)</dt>
        <dt>from_name (Replay mail from name)</dt></h5><br>
        <dd>
            For reply mail by this module, override setting value.
            Default using define in language resource.
        </dd>
        </p>

        <p class="even">
        <dt><h5>enable_copy (Enable copy funciton event article)</h5></dt><br>
        <dd>
            <span style="font-family:'Lucida Console', monospace">enable_copy=yes</span>
            Add "New Event" checkbox when edit event article.
            When checked the checkbox to store that new event.
        </dd>
        </p>

        <p class="even">
        <dt><h5>email_repeat_check (Enable confirm input Email Address)</h5></dt><br>
        <dd>
            <span style="font-family:'Lucida Console', monospace">email_repeat_check=yes</span>
            When an application for the event,
            confirm email address to add a field to verify that it matches the address.
        </dd>
        </p>

        <p class="even">
        <dt><h5>need_bind_uid (Force user setting when register reservation)</h5></dt><br>
        <dd>
            <span style="font-family:'Lucida Console', monospace">need_bind_uid=1</span>
            Need setup user when resister reservation by moderator/admin.
        </dd>
        </p>

        <p class="even">
        <dt><h5>users_search_columns (Search columns when user selecting)</h5></dt><br>
        <dd>
            <span style="font-family:'Lucida Console', monospace">users_search_columns=uname,email</span>
            Setup user for register reservation by moderator/admin.
            Searching/Display columns name list for <span style="font-family:'Lucida Console', monospace">users</span> table joined by comma(,).
        </dd>
        </p>

        <p class="even">
        <dt><h5>users_search_labels (Searching/Display column display labels)</h5></dt><br>
        <dd>
            <span style="font-family:'Lucida Console', monospace">users_search_columns=Username,EMAIL</span>
        </dd>
        </p>

        <p class="even">
        <dt><h5>enable_past_register=yes</h5></dt><br>
        <dd>
            Allow register to event which past closed date by moderator/admin.
            Default to stop register with display "Finish reservation" message.
        </dd>
        </p>

        <p class="even">
        <dt><h5>display_username={X_UNAME}</h5></dt><br>
        <dd>
            Reservation user displayed format setting. Default as "<span style="font-family:'Lucida Console', monospace">{X_UNAME}</span>".
            for example "real name (user name)" style display, set follow:
            <pre>display_username={X_NAME} ({X_UNAME})</pre>
        </dd>
        </p>


    </dl>

    </p>
</div>
