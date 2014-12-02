<header role="banner">
    <div>
        <img src="[Repo:IconPath]" alt="logo: [Repo:Name]" />
        <h1>[Repo:Name]</h1>
        <if placeholder="Config:UseQrCodes">
            <img title="[Text:RepoQrCode]" src="[Repo:QrCodePath]" alt="qrcode: [Repo:Name]" />
        </if>
    </div>
    <div>[Repo:Description]</div>
    <div>
        <span>[Text:LastModified]: </span>
        <span>[Repo:LastModified]</span>
    </div>
    <div id="lang">
        <span>[Text:Language]: </span>
        <ul>
            [Subtemplate:LangSelector]
        </ul>
    </div>
</header>