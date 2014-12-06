<!DOCTYPE html>
<html lang="[Lang:Current]">

<head>

    <meta charset="UTF-8">

    <title>[Page:Title]</title>

    <if placeholder="Page:Favicon">
        <link rel="icon" type="image/x-icon" href="[Page:Favicon]" />
    </if>

    <link type="text/css" rel="stylesheet" href="templates/default/css/default.css" />

</head>

<body>
	<header>
		<div role="banner">
			<div>
				<h1>[Page:Title]</h1>
			</div>
			<div id="lang">
				<span>[Text:Language]: </span>
				<ul>
				[Subtemplate:LangSelector]
				</ul>
			</div>
		</div>
	</header>

<main role="main">

	<nav id="menu" role="navigation">
		<h2>[Text:Menu]:</h2>
		<ul>
			<li><a href="#warning" title="[Text:AccessErrorMessage]">[Page:Title]</a></li>
		</ul>
	</nav>
	<article>
		<h2>[Page:Title]</h2>
		<div id="warning">[Text:ErrorMessage]</div>
	</article>

</main>
<footer role="contentinfo"><span>[Text:Footer]</span></footer>

</body>
</html>
