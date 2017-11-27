<style>
    .note {
        color: #800000;
    }
</style>
<div id="help-template" class="outer">
    <{include file=$smarty.const.CO_EGUIDE_HELP_HEADER}>
    <h4 class="odd">RESERVATION LIMIT PLUGINS</h4><br>

    <p class="even">
        <a name="plugins"></a><h5>Reservation limit plugins</h5><br>

    <p>Event Guide module can use plugins for control reservations.<br>
        It provide more complex condition for reservations.<br>
        There is sample "dlimit" plugin that control same user can limited number of reservation in a day.<br>
        Using this plugin reservation to need login.<br>
    </p>

    <br>
    <p>This plugin activate set module preferences "Additional item options"
        and adds following:</p>
    <ul>
        <li>eguide_plugins=dlimit</li>
        <li>max_register_in_day=1</li>
        <li>max_register_in_future=2</li>
    </ul>

    <br>
    <dt><h5>eguide_plugins</h5></dt>
    <dd>Activate plugins..<br>
        Comma seperated if multiple.
    </dd>

    <br>
    <dt><h5>max_register_in_day</h5></dt>
    <dd>Number of reservation in a day. Default value is 1. No limitation as 0.</dd>

    <br>
    <dt><h5>max_register_in_future</h5></dt>
    <dd>Number of total reservation in active event.
        A event to starting time has come when next reservation to be accept.
        Default value is 0 that is no limitation.
    </dd>


    <br>
    <a name="multidate"></a><h5>Handling for multiple open event</h5><br>

    <p>Event Guide handle the content of same event, open two or more times.
        This settings to "Repeat Open" in event registration form.
    </p>

    <br>
    <p>You want more detail settings, you send "Preview" one time.
        There is expand and edit more detail items.
    </p>

    <br>
    <p>In addition, the event is registered once to do detailed
        specification and it sets it on "Edit Open Date" page.
    </p>

    <br>
    <h5>Note to multiple open event editing</h5><br>

    <ul>
        <li>When edit mutiple open event, careful to change the master event date.
            The open date keeps interval each other.
        </li>
        <li>Wen time is omitted by the date specification,
            specified same time of the starting date.
        </li>
    </ul>

    </p>
</div>
