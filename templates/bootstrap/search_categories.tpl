<article id="categories">
    <form method="POST" action="[Search:Categories:Link]">
		<h2 style="position:absolute; top:-10000px">[Text:Categories]</h2>
        <fieldset>
            <legend>
                <a class="accordian"
                   data-toggle="collapse"
                   data-target="#category-items"
                   aria-controls="category-items">
                    <span class="glyphicon glyphicon-collapse-up up" style="float: right; cursor: pointer"></span>
                    <span class="glyphicon glyphicon-collapse-down down" style="float: right; cursor: pointer"></span>
                    <span style="cursor: pointer">[Text:Categories]</span>
                </a>
            </legend>
            <ul id="category-items" class="filter-items collapse in">
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
                        class="btn btn-default"
                        type="submit"
                        value="[Text:FormValue]"
                        title="[Text:FormValue]: [Text:Categories]"
                        name="[Text:Categories]" />
                </li>
            </ul>
        </fieldset>
    </form>
</article>
