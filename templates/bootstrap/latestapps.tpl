<div class="widget-panel panel panel-default">
    <div class="panel-heading">
        <a class="accordian"
            data-toggle="collapse"
            data-target="#latest-apps"
            aria-expanded="true"
            aria-controls="latest-apps">
            <span class="glyphicon glyphicon-collapse-up up" style="float: right"></span>
            <span class="glyphicon glyphicon-collapse-down down" style="float: right"></span>
            <h2 class="panel-title">[Text:LatestAppList]</h2>
        </a>
    </div>
    <div class="panel-body collapse in" id="latest-apps">
        <if placeholder="AppList:HasResults">
            [Subtemplate:AppItems]
        </if>
        <if placeholder="AppList:HasNoResults">
            <p>[Text:NoApps]</p>
        </if>
    </div>
</div>