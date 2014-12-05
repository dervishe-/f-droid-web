<!DOCTYPE html>
<html lang="[Lang:Current]">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>[Repo:Name]</title>

    <if placeholder="Page:Favicon">
        <link rel="icon" type="image/x-icon" href="[Page:Favicon]" />
    </if>

    <if placeholder="Config:UseFeeds">
        <link rel="alternate" type="application/atom+xml" title="[Page:Feed:Title]" href="[Page:Feed:Link]" />
    </if>

    <link type="text/css" rel="stylesheet" href="templates/bootstrap/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="templates/bootstrap/css/bootstrap.theme.css" />

</head>

<body>

<div class="container">
    <div class="row">
        <div class="jumbotron">
            [Subtemplate:Headers]
        </div>
    </div>
</div>

<main role="main">

    <if placeholder="Page:WarningMessage">
        <div id="warning" title="[Text:Warning]">
            [Page:WarningMessage]
        </div>
    </if>

    <nav id="menu" class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navigation-items">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand">[Repo:Name]</a>
            </div>
            <div id="navigation-items" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="[Page:Nav:AnchorMenu]" title="[Text:Nav:AccessMenu]">[Text:Nav:Menu]</a></li>
                    <li><a href="#search" title="[Text:Nav:AccessFormVal]">[Text:Nav:FormVal]</a></li>
                    <li><a href="#lastapplist" title="[Text:Nav:AccessLastAppList]">[Text:Nav:LastAppList]</a></li>
                    <li><a href="#categories" title="[Text:Nav:BrowseCategories]">[Text:Nav:Categories]</a></li>
                    <li><a href="#licenses" title="[Text:Nav:BrowseLicenses]">[Text:Nav:Licenses]</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">

            <div class="col-lg-6">
                [Subtemplate:MainContent]
            </div>

            <div class="col-lg-3">
                <div class="widget-panel panel panel-default">
                    <div class="panel-heading">
                        <a class="accordian"
                            data-toggle="collapse"
                            data-target="#filter-widgets"
                            aria-expanded="true"
                            aria-controls="filter-widgets">
                            <span class="glyphicon glyphicon-collapse-up up" style="float: right; cursor: pointer"></span>
                            <span class="glyphicon glyphicon-collapse-down down" style="float: right; cursor: pointer"></span>
                            <h2 class="panel-title">
                                [Text:Search]
                            </h2>
                        </a>
                    </div>
                    <div class="panel-body collapse in" id="filter-widgets">
                        <a name="search"></a>
                        [Subtemplate:Tools:KeywordSearch]
                        [Subtemplate:Tools:Reset]

                        <a name="categories"></a>
                        [Subtemplate:Tools:Categories]

                        <a name="licenses"></a>
                        [Subtemplate:Tools:Licenses]
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <a name="lastapplist"></a>
                [Subtemplate:LastApp]
            </div>

        </div>

    </div>
</main>

<if placeholder="Page:FooterText">
    <footer role="contentinfo"><span>[Page:FooterText]</span></footer>
</if>

<script src="templates/bootstrap/js/jquery-2.1.1.min.js"></script>
<script src="templates/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
