<style>
    .note {
        color: #800000;
    }
</style>
<div id="help-template" class="outer">
    <{include file=$smarty.const.CO_EGUIDE_HELP_HEADER}>
    <h4 class="odd">FUNCTIONS</h4><br>
    <p class="even">

    <h4>Event Registration and Display</h4><br>

    <ol>
        <li>Show Summary Listed</li>
        <li>Blocks (Date and Title list)</li>
        <li>Show all (with reservation form)</li><br>
        <div class='note'>
            When user with Admin permission or event poster - will display administration links.
        </div><br>
    </ol>

    <h4>Event Edit</h4><br>

    <ol>
        <li>New Event Registration - allow admin or setup group</li>
        <li>Reservations</li>
        <ul>
            <li>Approve reservation request</li>
            <li>Summary display</li>
            <ul>
                <li>counting items in radio, check, select field</li>
                <li>Display reservation list (for Display, for Print)</li><br>
                <div class="note">
                    Reservations need approval (auto approve disabled), Here to approve.
                    <br>
                    All Reservation information to display and Edit, Delete operation
                    to be chosen from item "more" link.
                    NOTE: Be careful to be match form field when you edit information.
                </div><br>
                <li>get data in CSV format</li>
            </ul>
            <li>Sending Mail to reservation persons</li>
        </ul>
    </ol><br>

    <h4>Admin functions</h4><br>

    <ol>
        <li>Event Configuration</li>
        <li>Event operations</li>
        <li>Notify new event registered users</li>
    </ol>

    </p>
</div>
