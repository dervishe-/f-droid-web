<article id="appsheet">
    <header>
        <h2>
            <img src="[App:IconPath]" alt="icone [App:Name]" />
            <span>[App:Name]</span>
            <a href="index.php">[Text:Back]</a>
        </h2>
        <div title="[Text:Summary]">[App:Summary]</div>

        <if placeholder="Config:UseSocial">
            <aside id="social_links">
                <a title="[Text:Share] Diaspora" href="http://sharetodiaspora.github.io/?title=[Social:Message:UrlEncoded]&amp;url=[Social:Url]">
                    <img alt="[Text:Share] Diaspora" src="[Social:Icon:Diaspora]" />
                </a>
                <a title="[Text:Share] Facebook" href="https://www.facebook.com/sharer.php?u=[Social:Message:UrlEncoded]&amp;t=[Social:Url]">
                    <img alt="[Text:Share] Facebook" src="[Social:Icon:Facebook]" />
                </a>
                <a title="[Text:Share] Google+" href="https://plus.google.com/share?url=[Social:Url]">
                    <img alt="[Text:Share] Google+" src="[Social:Icon:GooglePlus]" />
                </a>
                <a title="[Text:Share] Twitter" href="https://twitter.com/intent/tweet?text=[Social:Message:UrlEncoded]&amp;url=[Social:Url]">
                    <img alt="[Text:Share] Twitter" src="[Social:Icon:Twitter]" />
                </a>
            </aside>
        </if>
    </header>

    <div id="details">

        <aside id="download">
            <if placeholder="Config:UseQrCode">
                <img src="[QrCode:ImagePath]" alt="QR-Code [App:Name]" title="QR-Code: [Text:Download] [App:Name]" />
            </if>
            <a href="[App:Package:Name]" title="[Text:Download] [App:Name]">[Text:Download]</a>
        </aside>

        <div title="[Text:Size]">
            <span>[Text:Size]: </span>
            <span>[App:Package:SizeReadable]</span>
        </div>

        <div title="[Text:Version]">
            <span>[Text:Version]: </span>
            <span>[App:Package:Version]</span> - <span>[Text:Date]</span>:
            <span>[App:Date]</span>
        </div>

        <div title="[Text:License]">
            <span>[Text:License]: </span>
            <span>[App:License]</span>
        </div>
        <if placeholder="App:Requirements">
            <div title="[Text:Requirements]">
                <span>[Text:Requirements]: </span>
                <span>[App:Requirements]</span>
            </div>
        </if>
    </div>

    <div title="[Text:Description]" id="description">
        [App:Description]
    </div>

    <div id="misc">

        <if placeholder="Subtemplate:Categories">
            <aside id="used_categories" title="[Text:Categories]">
                <span>[Text:Categories]: </span>
                <ul>
                    [Subtemplate:Categories]
                </ul>
            </aside>
        </if>

        <if placeholder="Subtemplate:Permissions">
            <aside id="perms_[App:Package:Version]" title="[Text:Permissions]">
                <span>[Text:Permissions]: </span>
                <ul>
                    [Subtemplate:Permissions]
                </ul>
            </aside>
        </if>

    </div>

    <if placeholder="Subtemplate:Antifeatures">
        <aside id="antifeatures" title="[Text:AntiFeatures]">
            <span>[Text:AntiFeatures]: </span>
            <ul>
                [Subtemplate:Antifeatures]
            </ul>
        </aside>
    </if>

    <div title="[Text:Hash]" id="hash">
        <span>[Text:Hash] [[App:Package:Hash:Type]]: </span>
        <span>[App:Package:Hash:Value]</span>
    </div>

    <div>

        <if placeholder="App:Donate:HasDonationOptions">
            <aside id="donate_app">
        </if>

                <if placeholder="App:Donate:Link">
                    <a title="[Text:Donate]" href="[App:Donate:Link]">[Text:Donate]</a>
                </if>
                <if placeholder="App:Donate:FlattrLink">
                    <a title="[Text:Donate]: [Text:Flattr]" href="[App:Donate:FlattrLink]">[Text:Donate]: [Text:Flattr]</a>
                </if>
                <if placeholder="App:Donate:BitcoinAddress">
                    <div title="[Text:Donate]: [Text:Bitcoin]">
                        <span>[Text:Bitcoin]</span>
                        <span>[App:Donate:BitcoinAddress]</span>
                    </div>
                </if>

        <if placeholder="App:Donate:HasDonationOptions">
            </aside>
        </if>

        <aside id="block_dev">
            <div>
                <span>[Text:SdkVersion]: </span>
                <span>v[App:Package:SdkVersion]</span>
            </div>
            <if placeholder="App:Link:Website">
                <a href="[App:Link:Website]">[Text:Website]</a>
            </if>
            <if placeholder="App:Link:IssueTracker">
                <a href="[App:Link:IssueTracker]">[Text:IssueTracker]</a>
            </if>
            <if placeholder="App:Link:SourceCode">
                <a href="[App:Link:SourceCode]">[Text:SourceCode]</a>
            </if>
        </aside>

        <if placeholder="Subtemplate:Versions">
            <aside id="oldversions">
                [Subtemplate:Versions]
            </aside>
        </if>

    </div>
</article>
