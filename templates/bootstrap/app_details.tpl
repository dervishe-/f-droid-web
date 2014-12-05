<article id="appsheet">
    <header class="page-header">
        <h2>
            <img src="[App:IconPath]" alt="icone [App:Name]" />
            <span>[App:Name]</span>
            <small><a class="back" href="index.php">[Text:Back]</a></small>
        </h2>
    </header>

    <div id="details" class="panel panel-default">
        <div class="panel-body">

            <div class="info" title="[Text:Download] [App:Name]">
                <a class="btn btn-primary" href="[App:Package:Name]" role="button">
                    [Text:Download]
                    <small>([App:Package:SizeReadable])</small>
                </a>
            </div>

            <aside id="download">
                <if placeholder="Config:UseQrCode">
                    <img src="[QrCode:ImagePath]" alt="QR-Code [App:Name]" title="QR-Code: [Text:Download] [App:Name]" />
                </if>
            </aside>

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

            <div class="info" title="[Text:Version]">
                <span>[Text:Version]: </span>
                <span>[App:Package:Version]</span>
            </div>

            <div class="info" title="[Text:Date]">
                <span>[Text:Date]</span>:
                <span>[App:Date]</span>
            </div>

            <div class="info" title="[Text:License]">
                <span>[Text:License]: </span>
                <span>[App:License]</span>
            </div>

            <if placeholder="App:Requirements">
                <div class="info" title="[Text:Requirements]">
                    <span>[Text:Requirements]: </span>
                    <span>[App:Requirements]</span>
                </div>
            </if>

            <if placeholder="App:Donate:HasDonationOptions">
                <aside id="donate_app" class="info">
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
                </aside>
            </if>

            <aside id="block_dev">
                <div class="info">
                    <span>[Text:SdkVersion]: </span>
                    <span>v[App:Package:SdkVersion]</span>
                </div>
                <div>
                    <if placeholder="App:Link:Website">
                        <a class='btn btn-default btn-xs' href="[App:Link:Website]">[Text:Website]</a>
                    </if>
                </div>
                <div>
                    <if placeholder="App:Link:IssueTracker">
                        <a class='btn btn-default btn-xs' href="[App:Link:IssueTracker]">[Text:IssueTracker]</a>
                    </if>
                </div>
                <div>
                    <if placeholder="App:Link:SourceCode">
                        <a class='btn btn-default btn-xs' href="[App:Link:SourceCode]">[Text:SourceCode]</a>
                    </if>
                </div>

                <div title="[Text:Hash]" id="hash" class="hash-container info">
                    <span class="hash-link">[Text:Hash] ([App:Package:Hash:Type])</span>
                    <span class="hash-value">[App:Package:Hash:Value]</span>
                </div>
            </aside>

        </div>
    </div>

    <div class="summary" title="[Text:Summary]">[App:Summary]</div>

    <div title="[Text:Description]" id="description">
        [App:Description]
    </div>

    <if placeholder="Subtemplate:Categories">
        <aside id="used_categories" title="[Text:Categories]" class="alert alert-info">
            <strong>[Text:Categories]</strong>
            <ul>
                [Subtemplate:Categories]
            </ul>
        </aside>
    </if>

    <if placeholder="Subtemplate:Antifeatures">
        <aside id="antifeatures" title="[Text:AntiFeatures]" class="alert alert-danger">
            <strong>[Text:AntiFeatures]</strong>
            <ul class="anti-features">
                [Subtemplate:Antifeatures]
            </ul>
        </aside>
    </if>

    <if placeholder="Subtemplate:Permissions">
        <aside id="perms_[App:Package:Version]" title="[Text:Permissions]" class="permission-container alert alert-warning">
            <strong>[Text:Permissions]</strong>
            <ul>
                [Subtemplate:Permissions]
            </ul>
        </aside>
    </if>

    <if placeholder="Subtemplate:Versions">
        <aside id="oldversions">
            <h3>[Text:PastVersions]</h3>
            [Subtemplate:Versions]
        </aside>
    </if>

    <script>
        window.onload = function() {

            // If javascript is disabled, then the hash will be displayed as per usual. If not,
            // we will hide it and make it show when a button is pressed.
            var hideHash = function() {
                var hash = $('#hash');
                var value = hash.find('.hash-value');

                // Add a bootstrap button that when clicked, toggles the visibility of the hash.
                var link = $('<a class="btn btn-default btn-xs"></a>');
                hash.find('.hash-link').remove().appendTo(link);
                value.before(link);

                // By default hide the hash, only showing when they click our newly added button.
                value.hide();
                link.click(function () {
                    value.toggle();
                });
            };

            hideHash();

        };
    </script>

</article>
