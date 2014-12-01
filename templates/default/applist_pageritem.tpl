<li>
    <if placeholder="Pager:IsButton">
        <a href="[Pager:Link]" title="[Text:GoToPage [Pager:Number]">
            [Pager:Number]
        </a>
    </if>
    <if placeholder="Pager:IsSpacer">
        ..
    </if>
    <if placeholder="Pager:IsSelected">
        <span>[Pager:Number]</span>
    </if>
</li>