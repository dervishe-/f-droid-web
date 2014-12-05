<article id="licenses">
    <form method="POST" action="[Search:Licenses:Link]">
		<h2 style="position:absolute; top:-10000px">[Text:License]</h2>
        <fieldset>
            <legend>
                <a class="accordian collapsed"
                   data-toggle="collapse"
                   data-target="#license-items"
                   aria-controls="license-items">
                    <span class="glyphicon glyphicon-collapse-up up" style="float: right; cursor: pointer"></span>
                    <span class="glyphicon glyphicon-collapse-down down" style="float: right; cursor: pointer"></span>
                    [Text:License]
                </a>
            </legend>
            <ul id="license-items" class="filter-items collapse up">
                <li>
                    <input
                        type="checkbox"
                        id="all_lic"
                        name="lic[]"
                        value="all"
                        <if placeholder="Search:Licenses:AllLicensesSelected">checked="checked"</if>
                        title="[Text:AltLicenseLink]: [Text:AllLicenses]"
                    />
                    <label for="all_lic">
                        [Text:AllLicenses] ([Search:Licenses:TotalAppCount])
                    </label>
                </li>

                [Subtemplate:LicenseItems]

                <li>
                    <input
                        class="btn btn-default"
                        type="submit"
                        value="[Text:FormValue]"
                        title="[Text:FormValue]: [Text:License]"
                        name="[Text:License]" />
                </li>
            </ul>
        </fieldset>
    </form>
</article>
