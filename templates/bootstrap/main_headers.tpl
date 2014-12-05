<header role="banner">
    <div>
        <img id="repo-icon" src="[Repo:IconPath]" alt="logo: [Repo:Name]" />
        <if placeholder="Config:UseQrCodes">
            <a
                href="[Repo:Url]"
                title="[Text:RepoQrCode]">
                <img id="repo-qr" src="[Repo:QrCodePath]" alt="qrcode: [Repo:Name]" />
            </a>
        </if>
        <h1><a href="[Repo:Link:Home]">[Repo:Name]</a></h1>
    </div>
    <div>[Repo:Description]</div>
    <div>
        <span>[Text:LastModified]: </span>
        <span>[Repo:LastModified]</span>
    </div>
    <div id="lang">
        <div class="btn-group btn-group-sm">
            [Subtemplate:LangSelector]
        </div>
    </div>
</header>