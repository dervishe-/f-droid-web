<div id="[Package:Version]">
    <a title="[Text:Download] [Package:Name]" href="[Package:Name]" aria-describedby="[Package:Version]">
        [Text:Download]
    </a>
    <div title="[Text:Version]">
        <span>[Text:Version]: </span>
        <span>[Package:Version]</span> - <span>[Text:DateAdded]</span>:
        <span>[Package:DateAdded]</span>
    </div>
    <div>
        <span>[Text:SdkVersion]: </span>
        <span>v[Package:SdkVersion]</span>
    </div>
    <div title="[Text:Size]">
        <span>[Text:Size]: </span>
        <span>[Package:SizeReadable]</span>
    </div>
    <div title="[Text:Hash] [Package:Name]">
        <span>[Text:Hash] [[Package:Hash:Type]]: </span>
        <span>[Package:Hash:Value]</span>
    </div>
    <if placeholder="Subtemplate:Permissions">
        <div id="perms_[Package:Version]" title="[Text:Permissions]">
            <span>[Text:Permissions]: </span>
            <ul>
                [Subtemplate:Permissions]
            </ul>
        </div>
    </if>
</div>