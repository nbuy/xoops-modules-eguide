<div id="help-template" class="outer">
    <{include file=$smarty.const.CO_EGUIDE_HELP_HEADER}>
    <h4 class="odd">ADDITIONA FIELDS FORMATS</h4><br>

    <p class="even">
        <a name="form"></a><h5>Additional fields Format</h5><br>

    <strong>In Reservation</strong><br>

    Additonal items set to FORM when used order to make reservation.
    These additional items are setup with the following formats:<br><br>
    <blockquote>
        line ::= ["!"]item-name["*"][,type[,argument...]]<p>
            type ::= {text|checkbox|radio|textarea|select|hidden|const}<br>
            argument ::= [value ["+"] "="] label
    </blockquote>
    <br>
    <ul>
        <li>"!" means the item is displayed to the public.</li>
        <div class="note">
            Additionally, form top of line marked "!" - reservation list is displayed.
            If there is no "!" marked items, the list is not displayed.
        </div>
        <li>"*" means you need input something.</li>
        <li>"#" is a message in the FORM (displayed comments).</li>
        <li>"," is a delimiter. Include part of strings, using escape in "\".</li>
        <li>value equivalent label when it suppressed.</li>
        <li>"+" Selected items (checkbox, radio, select) default value.</li>
        <li>Other arguments to be default value in text items(text, textarea).</li>
        <li>A part of argument makes attribute in inputs (text: "size=n", textarea: "cols=n" "rows=n").</li>
        <li>Using "prop=string" argument, makes any attributes in input.</li>
    </ul>

    </p>

    <p class="even">
        <br>

    <h5>Examples:</h5>

    <form>
        <table border="0" cellspacing="1" cellpadding="4" bgcolor="#808080">
            <tr bgcolor="#d0d0d0">
                <th>setup</th>
                <th>Displayed form</th>
            </tr>
            <tr class="even">
                <td>Name*</td>
                <td>Name* <input name='samp1'></td>
            </tr>
            <tr class="odd">
                <td>Name*,size=5</td>
                <td>Name* <input name='samp2' size=5></td>
            </tr>
            <tr class="even">
                <td>Name*,size=10,value,#comment text</td>
                <td>Name* <input name='samp3' size='10' value='value'> comment text</td>
            </tr>
            <tr class="odd">
                <td>Selection,radio,Item1+,Item2,Item3</td>
                <td>Selection <input type='radio' name='samp4' value='1' checked> Item1 &nbsp; <input type='radio' name='samp4' value='2'> Item2 &nbsp; <input type='radio' name='samp4' value='3'> Item3 &nbsp; </td>
            </tr>
            <tr class="even">
                <td>Selection,checkbox,Item1+,Item2,Item3</td>
                <td>Selection <input type='checkbox' name='samp5_1' checked> Item1 &nbsp; <input type='checkbox' name='samp5_2'> Item2 &nbsp; <input type='checkbox' name='samp5_3'> Item3 &nbsp; </td>
            </tr>
            <tr class="odd">
                <td>Selection,select,Item1,Item2,Item3</td>
                <td>Selection <select name='samp6'>
                        <option>Item1</option>
                        <option>Item2</option>
                        <option>Item3</option>
                    </select></td>
            </tr>
            <tr class="even">
                <td>Label,const,value</td>
                <td>Label value (The value display and handle to input value)</td>
            </tr>
            <tr class="odd">
                <td>Label,hidden,value</td>
                <td><em>(not display in form - present only input values)</em></td>
            </tr>
            <tr class="even">
                <td>#message text</td>
                <td>message text</td>
            </tr>
        </table>
    </form>
    </p>

    <p class="even">
    <br><h5>Related reservation forms</h5><br>
    <dl>
        <dt>Mulitiple persons reservation</dt>
        <dd>
            You want handle multiple persons reservation on a evnet,
            you need settings as following:
            <ul>
                <li>Number of persons input field in "Additional items" (example: "<span style="font-family:'Lucida Console', monospace">Persons,select,1,2,3,4,5</span>")</li>
                <li>Module Preferences "Label of persons" setting the fields label. (set in exmaple "<span style="font-family:'Lucida Console', monospace">Persons</span>")</li>
            </ul>
        </dd>
        <dt>Redirect After Reservation URL</dt>
        <dd>
            This is use when custom page display after reservation. That URL include following variables:
            <span style="font-family:'Lucida Console', monospace">{X_EID}</span> (event id), <span style="font-family:'Lucida Console', monospace">{X_SUB}</span> (event extra id), <span style="font-family:'Lucida Console', monospace">{X_RVID}</span> (reservation id).
            This variables use handle to get event information.
            You can use for post processing reservations.
        </dd>
    </dl>

    </p>
</div>
