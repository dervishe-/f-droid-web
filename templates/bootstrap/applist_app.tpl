<div class="panel panel-default app-abstract app" id="[App:Id:Safe]">
    <div class="panel-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <header>
                        <h3>
                            <img src="[App:Icon]" alt="icone [App:Name]" />
                            <span class="text">[App:Name]</span>
                        </h3>
                        <span id="desc_[App:Id:Safe]" title="[Text:Summary]">[App:Summary]</span>
                    </header>
                </div>
                <div class="col-lg-6 app-actions">

                    <a class="btn btn-default"
                        href="[App:Package:Name]"
                        title="[Text:Download]: [App:Name]"
                        aria-describedby="desc_[App:Id:Safe]">
                        [Text:Download]
                    </a>

                    <a class="btn btn-default"
                        href="[App:Link]"
                        title="[Text:Sheet]: [App:Name]"
                        aria-describedby="desc_[App:Id:Safe]">
                        [Text:Sheet]
                    </a>

                    <ul class="app-details">
                        <li class="size">
                            <span class="details-label">[Text:Size]:</span>
                            <span class="details-value">[App:Package:SizeReadable]</span>
                        </li>
                        <li class="version">
                            <span class="details-label">[Text:Version]:</span>
                            <span class="details-value">[App:Package:Version]</span>
                        </li>
                        <li class="date">
                            <span class="details-label">[Text:Date]:</span>
                            <span class="details-value">[App:Date]</span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>