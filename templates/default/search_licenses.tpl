<article id="licenses">
    <header>
        <h2>[Text:License]</h2>
    </header>
    <form method="POST" action="[Search:Licenses:Link]">
        <fieldset>
            <legend>[Text:LicenseList]</legend>
            <ul>
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
                        type="submit"
                        value="[Text:FormValue]"
                        title="[Text:FormValue]: [Text:License]"
                        name="[Text:License]" />
                </li>
            </ul>
        </fieldset>
    </form>
</article>