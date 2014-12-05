<if placeholder="Pager:IsButton">
    <li><a href="[Pager:Link]" title="[Text:GoToPage] [Pager:Number]">[Pager:Number]</a></li>
</if>
<if placeholder="Pager:IsSpacer">
    <li class="disabled"><a href="#">..</a></li></if>
<if placeholder="Pager:IsSelected">
    <li class="active"><a href="[Pager:Link]" title="[Text:GoToPage] [Pager:Number]">[Pager:Number]</a></li>
</if>