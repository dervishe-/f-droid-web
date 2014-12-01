<aside id="lastapplist" role="complementary">
    <header>
        <h2>[Text:LatestAppList]</h2>
    </header>
    <if placeholder="AppList:HasResults">
        [Subtemplate:AppItems]
    </if>
    <if placeholder="AppList:HasNoResults">
        <p>[Text:NoApps]</p>
    </if>
</aside>