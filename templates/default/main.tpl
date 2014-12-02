<!DOCTYPE html>
<html lang="[Lang:Current]">

<head>

    <meta charset="UTF-8">

    <title>[Repo:Name]</title>

    <if placeholder="Page:Favicon">
        <link rel="icon" type="image/x-icon" href="[Page:Favicon]" />
    </if>

    <if placeholder="Config:UseFeeds">
        <link rel="alternate" type="application/atom+xml" title="[Page:Feed:Name]" href="[Page:Feed:Link]" />
    </if>

    <link type="text/css" rel="stylesheet" href="Media/css/default.css" />

</head>

<body>

[Subtemplate:Headers]

<main role="main">

    <if placeholder="Page:WarningMessage">
        <div id="warning" title="[Text:Warning]">
            [Page:WarningMessage]
        </div>
    </if>

    <nav id="menu" role="navigation">
        <h2>[Text:Menu]:</h2>
        <ul>
            <li><a href="#search" title="[Text:Nav:AccessFormVal]">[Text:Nav:FormVal]</a></li>
            <li><a href="#categories" title="[Text:Nav:BrowseCategories]">[Text:Nav:Categories]</a></li>
            <li><a href="#licenses" title="[Text:Nav:BrowseLicenses]">[Text:Nav:Licenses]</a></li>
            <li><a href="[Page:Nav:AnchorMenu]" title="[Text:Nav:AccessMenu]">[Text:Nav:Menu]</a></li>
            <li><a href="#lastapplist" title="[Text:Nav:AccessLastAppList]">[Text:Nav:LastAppList]</a></li>
        </ul>
    </nav>

    [Subtemplate:MainContent]

    <aside id="tools" role="search">
        [Subtemplate:Tools:Reset]
        [Subtemplate:Tools:KeywordSearch]
        [Subtemplate:Tools:Categories]
        [Subtemplate:Tools:Licenses]
    </aside>

    [Subtemplate:LastApp]

</main>

<if placeholder="Page:FooterText">
    <footer role="contentinfo"><span>[Page:FooterText]</span></footer>
</if>

</body>
</html>
