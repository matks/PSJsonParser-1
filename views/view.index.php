<div class="navbar">
    <div class="navbar_container">
        <div class="links">
            {{LINKS}}
        </div>
        <div class="title">
            <h2>{{TITLE}}</h2>
        </div>
    </div>
</div>
<div class="container">
    <div class="details">
        <div class="options">
            <div class="blocks_container">
                <div class="block">
                    Filters :
                    {{FILTERS}}
                </div>
            </div>
        </div>
        <div class="table_container">
            <table class="table">
                <thead>
                <tr>
                    <th></th>
                    <th>Date</th>
                    <th>Version</th>
                    <th>Duration</th>
                    <th>Content</th>
                </tr>
                </thead>
                <tbody>
                {{TABLE_CONTENT}}
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    window.onload = function() {
        let labels;
        labels = document.querySelectorAll(".filter_version");
        for (const label of labels) {
            label.addEventListener('click', function() {
                let lbl = this;
                let version = lbl.dataset.for;
                let list_tr = document.querySelectorAll("table.table tbody tr."+version);
                list_tr.forEach(function (tr) {
                    if (hasClass(tr, version)) {
                        if (lbl.dataset.active === 'true') {
                            tr.style.display = "none";
                        } else {
                            tr.style.display = "";
                        }
                    }
                });
                if (lbl.dataset.active === 'true') {
                    lbl.dataset.active = 'false';
                } else {
                    lbl.dataset.active = 'true';
                }
            })
        }
    };

    function hasClass(ele,cls) {
        return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
    }
</script>