<li <if placeholder="Lang:IsSelected">class='selected'</if>>
    <if placeholder="Lang:IsNotSelected">
        <a href="?lang=[Lang:Id]" title="[Lang:Name]">
    </if>

    <img alt="[Lang:Name]" src="[Lang:IconPath]" />

    <if placeholder="Lang:IsNotSelected">
        </a>
    </if>
</li>