<article id="categories">
    <header>
        <h2>[Text:Categories]</h2>
    </header>
    <form method="POST" action="[Search:Categories:Link]">
        <fieldset>
            <legend>[Text:CategoriesList]</legend>
            <ul>
                <li>
                    <input
                        type="checkbox"
                        id="all_cat" name="cat[]"
                        value="all"
                        title="[Text:AllCategoriesLink]: [Text:AllCategoriesLabel]"
                        <if placeholder="Search:Categories:AllCategoriesSelected">checked="checked"</if>
                    />
                    <label for="all_cat">
                        [Text:AllCategoriesLabel] ([Search:Categories:TotalAppCount])
                    </label>
                </li>

                [Subtemplate:CategoryItems]

                <li>
                    <input
                        type="submit"
                        value="[Text:FormValue]"
                        title="[Text:FormValue]: [Text:Categories]"
                        name="[Text:Categories]" />
                </li>
            </ul>
        </fieldset>
    </form>
</article>