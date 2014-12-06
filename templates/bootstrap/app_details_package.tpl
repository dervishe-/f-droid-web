<div id="[Package:Version]" class="package">
    <div title="[Text:Version]">
        <h4>[Text:Version] [Package:Version] <small>[Package:DateAdded]</small></h4>
    </div>
    <a class="btn btn-default" title="[Text:Download] [Package:Name]" href="[Package:Name]" aria-describedby="[Package:Version]">
        [Text:Download]
    </a>
    <div class="info">
        <span class="info-label">[Text:Size]: </span>
        <span>[Package:SizeReadable]</span>
    </div>
    <div class="info">
        <span class="info-label">[Text:SdkVersion]: </span>
        <span>v[Package:SdkVersion]</span>
    </div>
    <div title="[Text:Hash] [Package:Name]" class="hash-container info">
        <span class="info-label hash-label">[Text:Hash] ([Package:Hash:Type]): </span>
        <span class="hash-value">[Package:Hash:Value]</span>
    </div>
    <if placeholder="Subtemplate:Permissions">
        <aside id="perms_[Package:Version]" title="[Text:Permissions]" class="permission-container alert alert-warning">
            <strong>[Text:Permissions]</strong>
            <ul>
                [Subtemplate:Permissions]
            </ul>
        </aside>
    </if>
</div>