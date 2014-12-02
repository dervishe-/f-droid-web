<section id="applist">

    <header class="page-header">
        <h2>
            [Text:AppList]:
            <span title="[Text:NumResults]: [AppList:NumResults]">([AppList:NumResults])</span>
        </h2>
    </header>

    <if placeholder="AppList:HasResults">
        [Subtemplate:AppItems]
        <footer>
            <div>
                <span class="hidden">[Text:Page]:</span>
                <ul class="pagination">
                    [Subtemplate:PagerItems]
                </ul>
            </div>
        </footer>
    </if>
    <if placeholder="AppList:HasNoResults">
        <p>[Text:NoResults]</p>
    </if>
</section>