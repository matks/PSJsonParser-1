<div class="navbar">
    <div class="navbar_container">
        <div class="links">
            {{LINKS}}
        </div>
        <div class="title">
            <h2>{{TITLE}}</h2>
        </div>
        <div class="recap">
            {{RECAP}}
        </div>
    </div>
</div>
<div class="container">

    <div class="details">
        <div class="options">
            <div class="blocks_container">
                <div class="block">
                    Start Date : {{START_DATE}}
                </div>
                <div class="block">
                    End Date : {{END_DATE}}
                </div>
            </div>
        </div>
        <div id="left_navigation">
            <div class="navigation_block">
                <h4>Options</h4>
                <div class="buttons">
                    <div class="button">
                        <button id="toggle_failed" data-state="shown">Hide Passed Tests</button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="navigation_block">
                <h4>Navigation</h4>
                <div class="navigation">
                    {{NAVIGATION}}
                </div>
            </div>
            <hr>
            <div class="navigation_block">
                <div class="additional_infos">
                    <h4>Additional Info</h4>
                    <div class="info">
                        <span><i class="material-icons">bug_report</i> Invalid Session ID bugs: </span> {{INVALID_SESSION_ID_COUNT}}
                    </div>
                </div>
            </div>
        </div>
        <div id="content">
            {{CONTENT}}
        </div>

    </div>
</div>
<script>
    window.onload = function() {
        let test_blocks;
        test_blocks = document.querySelectorAll(".test_title");
        for (const test_block of test_blocks) {
            test_block.addEventListener('click', function() {
                let id = this.id;
                let stt = document.getElementById('stack_'+id).style;
                if (stt.display !== "block") {
                    stt.display = "block";
                } else {
                    stt.display = "none";
                }
            })
        }

        let toggle_failed_button = document.getElementById('toggle_failed');
        toggle_failed_button.addEventListener('click', function() {
            let state = toggle_failed_button.dataset.state;
            if (state === 'shown') {
                //let's hide it
                toggle_failed_button.innerHTML = 'Show Passed Tests';
                let passed_suites = document.querySelectorAll("section.suite.hasPassed:not(.hasFailed)");
                passed_suites.forEach(function (suite) {
                    suite.style.display = "none";
                });
                //hide tests
                let passed_tests = document.querySelectorAll("section.test_component.passed");
                passed_tests.forEach(function (block) {
                    block.style.display = "none";
                });
                toggle_failed_button.dataset.state = 'hidden';
            } else {
                //let's show it
                toggle_failed_button.innerHTML = 'Hide Passed Tests';
                let passed_suites = document.querySelectorAll("section.suite.hasPassed:not(.hasFailed)");
                passed_suites.forEach(function (suite) {
                    suite.style.display = "block";
                });
                //hide tests
                let passed_tests = document.querySelectorAll("section.test_component.passed");
                passed_tests.forEach(function (block) {
                    block.style.display = "block";
                });
                toggle_failed_button.dataset.state = 'shown';
            }
        });
    }
</script>