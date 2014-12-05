<li >
    <if placeholder="Lang:IsNotSelected">
        <a href="?lang=[Lang:Id]" title="[Lang:Name]">
    </if>
	<if placeholder="Lang:IsSelected"><span></if>
    <img alt="[Lang:Name]" src="[Lang:IconPath]" />
	<if placeholder="Lang:IsSelected"></span></if>
    <if placeholder="Lang:IsNotSelected">
        </a>
    </if>
</li>
